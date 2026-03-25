<?php
/**
 * SBN v1.0 - Contrôleur API
 *
 * Gère les webhooks et API externes
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Core\Controller;
use PDO;

class ApiController extends Controller {

    /**
     * Webhook pour réception des données de sauvegarde depuis NAS Synology
     *
     * POST /api/webhook
     * Headers: X-API-Token: <token>
     * Body: JSON avec les données de sauvegarde
     */
    public function webhook() {
        // Définir le header JSON
        header('Content-Type: application/json');

        try {
            // 1. Vérifier la méthode HTTP
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed. Use POST.']);
                exit;
            }

            // 2. Récupérer et valider le token API
            $apiToken = $_SERVER['HTTP_X_API_TOKEN'] ?? '';

            if (empty($apiToken)) {
                http_response_code(401);
                echo json_encode(['error' => 'Missing API token']);
                $this->logAudit(null, 'api_webhook_unauthorized', 'Missing API token', $_SERVER['REMOTE_ADDR']);
                exit;
            }

            // 3. Vérifier le token et récupérer le company_id
            $stmt = $this->db->prepare("
                SELECT id, company_id, name, is_active, last_used_at
                FROM api_tokens
                WHERE token = ? AND is_active = 1
            ");
            $stmt->execute([$apiToken]);
            $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tokenData) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid or inactive API token']);
                $this->logAudit(null, 'api_webhook_invalid_token', 'Invalid token: ' . substr($apiToken, 0, 10) . '...', $_SERVER['REMOTE_ADDR']);
                exit;
            }

            // 4. Mettre à jour la dernière utilisation du token
            $stmt = $this->db->prepare("UPDATE api_tokens SET last_used_at = NOW() WHERE id = ?");
            $stmt->execute([$tokenData['id']]);

            // 5. Récupérer et parser les données JSON
            $rawBody = file_get_contents('php://input');
            $data = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON: ' . json_last_error_msg()]);
                exit;
            }

            // 6. Valider les données requises
            $requiredFields = ['device_name', 'status'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    http_response_code(400);
                    echo json_encode(['error' => "Missing required field: $field"]);
                    exit;
                }
            }

            // 7. Valider le statut
            $validStatuses = ['success', 'failed', 'warning', 'running'];
            if (!in_array($data['status'], $validStatuses)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid status. Must be: ' . implode(', ', $validStatuses)]);
                exit;
            }

            // 8. Trouver ou créer le device
            $deviceId = $this->findOrCreateDevice(
                $tokenData['company_id'],
                $data['device_name'],
                $data['device_ip'] ?? null,
                $data['device_os'] ?? null
            );

            // 9. Créer l'enregistrement de sauvegarde
            $backupId = $this->createBackupRecord(
                $tokenData['company_id'],
                $deviceId,
                $data
            );

            // 10. Logger l'audit
            $this->logAudit(
                null,
                'api_webhook_success',
                "Backup received for device: {$data['device_name']}, status: {$data['status']}",
                $_SERVER['REMOTE_ADDR']
            );

            // 11. Créer une notification si erreur ou avertissement
            if (in_array($data['status'], ['failed', 'warning'])) {
                $this->createNotification(
                    $tokenData['company_id'],
                    $backupId,
                    $data
                );
            }

            // 12. Réponse success
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Backup data received successfully',
                'backup_id' => $backupId,
                'device_id' => $deviceId,
                'company_id' => $tokenData['company_id']
            ]);

        } catch (\Exception $e) {
            error_log('API Webhook Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => 'Internal server error',
                'message' => 'An error occurred while processing the webhook'
            ]);
        }
    }

    /**
     * Trouver ou créer un appareil (device)
     */
    private function findOrCreateDevice($companyId, $deviceName, $deviceIp = null, $deviceOs = null) {
        // Chercher un device existant
        $stmt = $this->db->prepare("
            SELECT id FROM backup_devices
            WHERE company_id = ? AND name = ?
        ");
        $stmt->execute([$companyId, $deviceName]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($device) {
            // Mettre à jour les infos si fournies
            if ($deviceIp || $deviceOs) {
                $stmt = $this->db->prepare("
                    UPDATE backup_devices
                    SET ip_address = COALESCE(?, ip_address),
                        os_version = COALESCE(?, os_version),
                        last_seen = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$deviceIp, $deviceOs, $device['id']]);
            }
            return $device['id'];
        }

        // Créer un nouveau device
        $stmt = $this->db->prepare("
            INSERT INTO backup_devices (company_id, name, ip_address, os_version, is_active, last_seen, created_at)
            VALUES (?, ?, ?, ?, 1, NOW(), NOW())
        ");
        $stmt->execute([$companyId, $deviceName, $deviceIp, $deviceOs]);

        return $this->db->lastInsertId();
    }

    /**
     * Créer un enregistrement de sauvegarde
     */
    private function createBackupRecord($companyId, $deviceId, $data) {
        $stmt = $this->db->prepare("
            INSERT INTO backups (
                company_id,
                device_id,
                status,
                start_time,
                end_time,
                size_bytes,
                error_message,
                backup_type,
                destination_path,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $companyId,
            $deviceId,
            $data['status'],
            $data['start_time'] ?? null,
            $data['end_time'] ?? null,
            $data['size_bytes'] ?? null,
            $data['error_message'] ?? null,
            $data['backup_type'] ?? 'full',
            $data['destination_path'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Créer une notification en cas d'erreur ou avertissement
     */
    private function createNotification($companyId, $backupId, $data) {
        $type = $data['status'] === 'failed' ? 'error' : 'warning';
        $title = $data['status'] === 'failed'
            ? "Échec de sauvegarde - {$data['device_name']}"
            : "Avertissement sauvegarde - {$data['device_name']}";

        $message = $data['error_message'] ?? "Statut: {$data['status']}";

        $stmt = $this->db->prepare("
            INSERT INTO notifications (company_id, backup_id, type, title, message, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");

        $stmt->execute([$companyId, $backupId, $type, $title, $message]);
    }

    /**
     * Logger un événement d'audit
     */
    private function logAudit($userId, $action, $details, $ipAddress) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO audit_logs (user_id, action, details, ip_address, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $action, $details, $ipAddress]);
        } catch (\Exception $e) {
            error_log('Audit log error: ' . $e->getMessage());
        }
    }

    /**
     * Vérifier le statut d'une sauvegarde (pour l'interface web)
     */
    public function backupStatus() {
        header('Content-Type: application/json');

        $user = $this->getUser();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $deviceId = $this->get('device_id');

        if (!$deviceId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing device_id parameter']);
            exit;
        }

        // Récupérer les dernières sauvegardes pour ce device
        $stmt = $this->db->prepare("
            SELECT b.*, d.name as device_name
            FROM backups b
            LEFT JOIN backup_devices d ON b.device_id = d.id
            WHERE b.company_id = ? AND b.device_id = ?
            ORDER BY b.start_time DESC
            LIMIT 10
        ");
        $stmt->execute([$user['company_id'], $deviceId]);
        $backups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'backups' => $backups
        ]);
    }
}
