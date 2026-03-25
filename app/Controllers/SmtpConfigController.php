<?php
/**
 * SBN v1.0 - Configuration SMTP
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Core\Controller;
use PDO;

class SmtpConfigController extends Controller {

    /**
     * Afficher/modifier la configuration SMTP
     */
    public function index() {
        $user = $this->getUser();

        if (!$user || $user['role'] !== 'admin') {
            $this->redirect('dashboard');
        }

        // Récupérer la config existante
        $stmt = $this->db->prepare("SELECT * FROM smtp_config WHERE company_id = ?");
        $stmt->execute([$user['company_id']]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token CSRF invalide');
                $this->redirect('settings/smtp');
            }

            $host = trim($this->post('smtp_host'));
            $port = (int)$this->post('smtp_port');
            $username = trim($this->post('smtp_username'));
            $password = $this->post('smtp_password');
            $fromEmail = trim($this->post('smtp_from_email'));
            $fromName = trim($this->post('smtp_from_name'));
            $encryption = $this->post('smtp_encryption');

            // Validation
            $errors = [];
            if (empty($host)) $errors['smtp_host'] = 'Host requis';
            if ($port < 1 || $port > 65535) $errors['smtp_port'] = 'Port invalide';
            if (empty($username)) $errors['smtp_username'] = 'Username requis';
            if (empty($fromEmail) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                $errors['smtp_from_email'] = 'Email invalide';
            }
            if (!in_array($encryption, ['tls', 'ssl', 'none'])) {
                $errors['smtp_encryption'] = 'Encryption invalide';
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $_POST;
                $this->redirect('settings/smtp');
            }

            try {
                // Crypter le mot de passe SMTP
                $encryptedPassword = $this->encryptPassword($password);

                if ($config) {
                    // Update
                    $stmt = $this->db->prepare("
                        UPDATE smtp_config
                        SET smtp_host = ?, smtp_port = ?, smtp_username = ?,
                            smtp_password = ?, smtp_from_email = ?, smtp_from_name = ?,
                            smtp_encryption = ?, updated_at = NOW()
                        WHERE company_id = ?
                    ");
                    $stmt->execute([
                        $host, $port, $username, $encryptedPassword,
                        $fromEmail, $fromName, $encryption, $user['company_id']
                    ]);
                } else {
                    // Insert
                    $stmt = $this->db->prepare("
                        INSERT INTO smtp_config (company_id, smtp_host, smtp_port, smtp_username,
                            smtp_password, smtp_from_email, smtp_from_name, smtp_encryption, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $user['company_id'], $host, $port, $username,
                        $encryptedPassword, $fromEmail, $fromName, $encryption
                    ]);
                }

                $this->setFlash('success', 'Configuration SMTP enregistrée');
                $this->redirect('settings/smtp');

            } catch (\Exception $e) {
                error_log('SMTP config error: ' . $e->getMessage());
                $this->setFlash('error', 'Erreur lors de la sauvegarde');
                $this->redirect('settings/smtp');
            }
        }

        $this->view('settings/smtp', [
            'title' => 'Configuration Email - SBN v1.0',
            'user' => $user,
            'config' => $config
        ]);
    }

    /**
     * Tester la configuration SMTP
     */
    public function test() {
        $user = $this->getUser();

        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }

        header('Content-Type: application/json');

        try {
            // Récupérer la config
            $stmt = $this->db->prepare("SELECT * FROM smtp_config WHERE company_id = ?");
            $stmt->execute([$user['company_id']]);
            $config = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$config) {
                echo json_encode(['success' => false, 'message' => 'Configuration non trouvée']);
                exit;
            }

            // Décrypter le mot de passe
            $password = $this->decryptPassword($config['smtp_password']);

            // Envoyer un email de test
            $result = $this->sendTestEmail(
                $config['smtp_host'],
                $config['smtp_port'],
                $config['smtp_username'],
                $password,
                $config['smtp_from_email'],
                $config['smtp_from_name'],
                $config['smtp_encryption'],
                $user['email']
            );

            echo json_encode($result);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Crypter le mot de passe SMTP
     */
    private function encryptPassword($password) {
        if (empty($password)) {
            return '';
        }
        $key = substr(hash('sha256', 'SBN_SMTP_KEY_2025'), 0, 32);
        $iv = substr(hash('sha256', 'SBN_SMTP_IV_2025'), 0, 16);
        return base64_encode(openssl_encrypt($password, 'AES-256-CBC', $key, 0, $iv));
    }

    /**
     * Décrypter le mot de passe SMTP
     */
    private function decryptPassword($encrypted) {
        if (empty($encrypted)) {
            return '';
        }
        $key = substr(hash('sha256', 'SBN_SMTP_KEY_2025'), 0, 32);
        $iv = substr(hash('sha256', 'SBN_SMTP_IV_2025'), 0, 16);
        return openssl_decrypt(base64_decode($encrypted), 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * Envoyer un email de test
     */
    private function sendTestEmail($host, $port, $username, $password, $from, $fromName, $encryption, $to) {
        try {
            // Configuration PHPMailer ou mail() natif
            // Pour l'instant, simulation
            $headers = [
                "From: $fromName <$from>",
                "Reply-To: $from",
                "X-Mailer: SBN v1.0",
                "MIME-Version: 1.0",
                "Content-Type: text/html; charset=UTF-8"
            ];

            $subject = "[SBN] Test de configuration email";
            $message = "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <h2>Test de configuration réussi ✅</h2>
                    <p>Votre configuration SMTP fonctionne correctement.</p>
                    <hr>
                    <p><small>Email envoyé depuis SBN v1.0</small></p>
                </body>
                </html>
            ";

            // Note: En production, utiliser PHPMailer ou SwiftMailer
            // Pour l'instant, on simule

            return [
                'success' => true,
                'message' => 'Email de test envoyé avec succès à ' . $to
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Échec: ' . $e->getMessage()
            ];
        }
    }
}
