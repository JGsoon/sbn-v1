<?php
/**
 * SBN v1.0 - Contrôleur de Partage
 *
 * Gère le partage sélectif de données entre utilisateurs
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Core\Controller;
use PDO;

class ShareController extends Controller {

    /**
     * Liste des partages créés par l'utilisateur
     */
    public function index() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        // Récupérer les partages créés par cette société
        $stmt = $this->db->prepare("
            SELECT s.*, u.name as owner_name
            FROM shared_access s
            LEFT JOIN users u ON s.owner_user_id = u.id
            WHERE s.owner_company_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$user['company_id']]);
        $shares = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Décoder le JSON pour affichage
        foreach ($shares as &$share) {
            $share['scope_decoded'] = json_decode($share['scope'], true);
            $share['permissions_decoded'] = json_decode($share['permissions'], true);
        }

        $this->view('share/index', [
            'title' => 'Partages - SBN v1.0',
            'user' => $user,
            'shares' => $shares
        ]);
    }

    /**
     * Formulaire de création de partage
     */
    public function create() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        // Récupérer la liste des appareils de la société
        $stmt = $this->db->prepare("
            SELECT id, name, ip_address, last_seen
            FROM backup_devices
            WHERE company_id = ? AND is_active = 1
            ORDER BY name ASC
        ");
        $stmt->execute([$user['company_id']]);
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Valider le CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token CSRF invalide');
                $this->redirect('share/create');
            }

            // Récupérer les données
            $email = trim($this->post('email'));
            $device_ids = $this->post('device_ids', []);
            $permissions = $this->post('permissions', []);
            $expires_in = $this->post('expires_in');

            // Validation
            $errors = [];

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email invalide';
            }

            // Ne pas permettre de partager avec soi-même
            if ($email === $user['email']) {
                $errors['email'] = 'Vous ne pouvez pas partager avec vous-même';
            }

            if (empty($device_ids)) {
                $errors['device_ids'] = 'Sélectionnez au moins un appareil';
            }

            if (empty($permissions)) {
                $errors['permissions'] = 'Sélectionnez au moins une permission';
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $_POST;
                $this->redirect('share/create');
            }

            try {
                // Vérifier que TOUS les device_ids appartiennent bien à la société
                $placeholders = implode(',', array_fill(0, count($device_ids), '?'));
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as count
                    FROM backup_devices
                    WHERE id IN ($placeholders) AND company_id = ?
                ");
                $stmt->execute(array_merge($device_ids, [$user['company_id']]));
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result['count'] != count($device_ids)) {
                    $this->setFlash('error', 'Certains appareils sélectionnés sont invalides');
                    $this->redirect('share/create');
                }

                // Calculer la date d'expiration
                $expiresAt = null;
                if ($expires_in && $expires_in > 0) {
                    $expiresAt = date('Y-m-d H:i:s', strtotime("+$expires_in days"));
                }

                // Construire le scope JSON
                $scope = json_encode([
                    'device_ids' => array_map('intval', $device_ids)
                ]);

                // Construire les permissions JSON
                $permissionsJson = json_encode($permissions);

                // Vérifier si un partage existe déjà pour cet email
                $stmt = $this->db->prepare("
                    SELECT id FROM shared_access
                    WHERE owner_company_id = ? AND shared_with_email = ? AND is_active = 1
                ");
                $stmt->execute([$user['company_id'], $email]);

                if ($stmt->fetch()) {
                    $this->setFlash('warning', 'Un partage actif existe déjà pour cet email. Révoquez-le d\'abord si vous souhaitez le modifier.');
                    $this->redirect('share');
                }

                // Créer le partage
                $stmt = $this->db->prepare("
                    INSERT INTO shared_access (
                        owner_company_id,
                        owner_user_id,
                        shared_with_email,
                        scope,
                        permissions,
                        is_active,
                        expires_at,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, 1, ?, NOW())
                ");
                $stmt->execute([
                    $user['company_id'],
                    $user['id'],
                    $email,
                    $scope,
                    $permissionsJson,
                    $expiresAt
                ]);

                // Logger l'action
                $this->logAudit(
                    $user['id'],
                    'share_created',
                    "Partage créé pour: $email (" . count($device_ids) . " appareils)",
                    $_SERVER['REMOTE_ADDR']
                );

                // TODO: Envoyer un email d'invitation
                $this->setFlash('success', 'Partage créé avec succès. Un email d\'invitation sera envoyé prochainement.');
                $this->redirect('share');

            } catch (\Exception $e) {
                error_log('Error creating share: ' . $e->getMessage());
                $this->setFlash('error', 'Erreur lors de la création du partage');
                $this->redirect('share/create');
            }
        }

        // Afficher le formulaire
        $this->view('share/create', [
            'title' => 'Nouveau partage - SBN v1.0',
            'user' => $user,
            'devices' => $devices
        ]);
    }

    /**
     * Révoquer un partage
     */
    public function revoke() {
        $user = $this->getUser();
        $shareId = $this->get('id');

        if (!$user || !$shareId) {
            $this->redirect('share');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Valider le CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token CSRF invalide');
                $this->redirect('share');
            }

            try {
                // SÉCURITÉ: Vérifier que le partage appartient à la société
                $stmt = $this->db->prepare("
                    SELECT shared_with_email FROM shared_access
                    WHERE id = ? AND owner_company_id = ?
                ");
                $stmt->execute([$shareId, $user['company_id']]);
                $share = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$share) {
                    $this->setFlash('error', 'Partage non trouvé');
                    $this->redirect('share');
                }

                // Révoquer le partage
                $stmt = $this->db->prepare("
                    UPDATE shared_access
                    SET is_active = 0, revoked_at = NOW()
                    WHERE id = ? AND owner_company_id = ?
                ");
                $stmt->execute([$shareId, $user['company_id']]);

                // Logger l'action
                $this->logAudit(
                    $user['id'],
                    'share_revoked',
                    "Partage révoqué pour: {$share['shared_with_email']}",
                    $_SERVER['REMOTE_ADDR']
                );

                $this->setFlash('success', 'Partage révoqué avec succès');

            } catch (\Exception $e) {
                error_log('Error revoking share: ' . $e->getMessage());
                $this->setFlash('error', 'Erreur lors de la révocation du partage');
            }
        }

        $this->redirect('share');
    }

    /**
     * Supprimer définitivement un partage
     */
    public function delete() {
        $user = $this->getUser();
        $shareId = $this->get('id');

        if (!$user || !$shareId) {
            $this->redirect('share');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Valider le CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token CSRF invalide');
                $this->redirect('share');
            }

            try {
                // SÉCURITÉ: Vérifier que le partage appartient à la société
                $stmt = $this->db->prepare("
                    SELECT shared_with_email FROM shared_access
                    WHERE id = ? AND owner_company_id = ?
                ");
                $stmt->execute([$shareId, $user['company_id']]);
                $share = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$share) {
                    $this->setFlash('error', 'Partage non trouvé');
                    $this->redirect('share');
                }

                // Supprimer le partage
                $stmt = $this->db->prepare("
                    DELETE FROM shared_access
                    WHERE id = ? AND owner_company_id = ?
                ");
                $stmt->execute([$shareId, $user['company_id']]);

                // Logger l'action
                $this->logAudit(
                    $user['id'],
                    'share_deleted',
                    "Partage supprimé pour: {$share['shared_with_email']}",
                    $_SERVER['REMOTE_ADDR']
                );

                $this->setFlash('success', 'Partage supprimé avec succès');

            } catch (\Exception $e) {
                error_log('Error deleting share: ' . $e->getMessage());
                $this->setFlash('error', 'Erreur lors de la suppression du partage');
            }
        }

        $this->redirect('share');
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
}
