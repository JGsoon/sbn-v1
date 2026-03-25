<?php
/**
 * SBN v1.0 - Contrôleur Tokens API
 *
 * Gère les tokens API pour l'authentification des webhooks NAS
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Core\Controller;
use PDO;

class ApiTokenController extends Controller {

    /**
     * Liste des tokens API
     */
    public function index() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        // Récupérer les tokens de la société
        $stmt = $this->db->prepare("
            SELECT id, name, token, is_active, last_used_at, created_at
            FROM api_tokens
            WHERE company_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user['company_id']]);
        $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('settings/api', [
            'title' => 'Tokens API - SBN v1.0',
            'user' => $user,
            'tokens' => $tokens
        ]);
    }

    /**
     * Créer un nouveau token API
     */
    public function create() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Valider le CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token CSRF invalide');
                $this->redirect('settings/api');
            }

            // Récupérer le nom du token
            $tokenName = trim($this->post('token_name'));

            // Valider
            $errors = [];
            if (empty($tokenName)) {
                $errors['token_name'] = 'Le nom du token est requis';
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $_POST;
                $this->redirect('settings/api');
            }

            // Générer un token sécurisé
            $token = $this->generateSecureToken();

            try {
                // Insérer dans la base de données
                $stmt = $this->db->prepare("
                    INSERT INTO api_tokens (company_id, user_id, name, token, is_active, created_at)
                    VALUES (?, ?, ?, ?, 1, NOW())
                ");
                $stmt->execute([$user['company_id'], $user['id'], $tokenName, $token]);

                // Logger l'action
                $this->logAudit(
                    $user['id'],
                    'api_token_created',
                    "Token API créé: $tokenName",
                    $_SERVER['REMOTE_ADDR']
                );

                // Stocker le token en session pour l'afficher UNE FOIS
                $_SESSION['new_token'] = [
                    'name' => $tokenName,
                    'token' => $token
                ];

                $this->setFlash('success', 'Token API créé avec succès. Copiez-le maintenant, il ne sera plus affiché!');
                $this->redirect('settings/api');

            } catch (\Exception $e) {
                error_log('Error creating API token: ' . $e->getMessage());

                // En mode debug, afficher l'erreur exacte
                if (APP_DEBUG) {
                    $this->setFlash('error', 'Erreur SQL: ' . $e->getMessage());
                } else {
                    $this->setFlash('error', 'Erreur lors de la création du token');
                }
                $this->redirect('settings/api');
            }
        }

        $this->redirect('settings/api');
    }

    /**
     * Révoquer (désactiver) un token
     */
    public function revoke() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Valider le CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token CSRF invalide');
                $this->redirect('settings/api');
            }

            $tokenId = $this->post('token_id');

            if (!$tokenId) {
                $this->setFlash('error', 'ID du token manquant');
                $this->redirect('settings/api');
            }

            try {
                // Vérifier que le token appartient bien à la société de l'utilisateur
                $stmt = $this->db->prepare("
                    SELECT id, name FROM api_tokens
                    WHERE id = ? AND company_id = ?
                ");
                $stmt->execute([$tokenId, $user['company_id']]);
                $token = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$token) {
                    $this->setFlash('error', 'Token non trouvé');
                    $this->redirect('settings/api');
                }

                // Désactiver le token
                $stmt = $this->db->prepare("
                    UPDATE api_tokens
                    SET is_active = 0
                    WHERE id = ? AND company_id = ?
                ");
                $stmt->execute([$tokenId, $user['company_id']]);

                // Logger l'action
                $this->logAudit(
                    $user['id'],
                    'api_token_revoked',
                    "Token API révoqué: {$token['name']}",
                    $_SERVER['REMOTE_ADDR']
                );

                $this->setFlash('success', 'Token révoqué avec succès');

            } catch (\Exception $e) {
                error_log('Error revoking API token: ' . $e->getMessage());
                $this->setFlash('error', 'Erreur lors de la révocation du token');
            }
        }

        $this->redirect('settings/api');
    }

    /**
     * Supprimer définitivement un token
     */
    public function delete() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Valider le CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token CSRF invalide');
                $this->redirect('settings/api');
            }

            $tokenId = $this->post('token_id');

            if (!$tokenId) {
                $this->setFlash('error', 'ID du token manquant');
                $this->redirect('settings/api');
            }

            try {
                // Vérifier que le token appartient bien à la société de l'utilisateur
                $stmt = $this->db->prepare("
                    SELECT id, name FROM api_tokens
                    WHERE id = ? AND company_id = ?
                ");
                $stmt->execute([$tokenId, $user['company_id']]);
                $token = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$token) {
                    $this->setFlash('error', 'Token non trouvé');
                    $this->redirect('settings/api');
                }

                // Supprimer le token
                $stmt = $this->db->prepare("
                    DELETE FROM api_tokens
                    WHERE id = ? AND company_id = ?
                ");
                $stmt->execute([$tokenId, $user['company_id']]);

                // Logger l'action
                $this->logAudit(
                    $user['id'],
                    'api_token_deleted',
                    "Token API supprimé: {$token['name']}",
                    $_SERVER['REMOTE_ADDR']
                );

                $this->setFlash('success', 'Token supprimé avec succès');

            } catch (\Exception $e) {
                error_log('Error deleting API token: ' . $e->getMessage());
                $this->setFlash('error', 'Erreur lors de la suppression du token');
            }
        }

        $this->redirect('settings/api');
    }

    /**
     * Générer un token API sécurisé
     */
    private function generateSecureToken($length = 64) {
        // Générer des bytes aléatoires cryptographiquement sécurisés
        $randomBytes = random_bytes($length);

        // Convertir en hexadécimal
        $token = bin2hex($randomBytes);

        // Préfixer pour identification
        return 'sbn_' . $token;
    }

    /**
     * Télécharger le fichier config.sh personnalisé
     */
    public function download() {
        $user = $this->getUser();
        $tokenId = $this->get('id');

        if (!$user || !$tokenId) {
            $this->redirect('settings/api');
        }

        // Récupérer le token
        $stmt = $this->db->prepare("
            SELECT token, name FROM api_tokens
            WHERE id = ? AND company_id = ?
        ");
        $stmt->execute([$tokenId, $user['company_id']]);
        $tokenData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$tokenData) {
            $this->setFlash('error', 'Token non trouvé');
            $this->redirect('settings/api');
        }

        // Générer le fichier config.sh
        $appUrl = rtrim(APP_URL, '/');
        $content = <<<EOF
#!/bin/bash
################################################################################
# SBN v1.0 - Configuration du script webhook
# Généré automatiquement pour: {$tokenData['name']}
# Date: {date('Y-m-d H:i:s')}
################################################################################

# URL de votre instance SBN (webhook endpoint)
SBN_API_URL="{$appUrl}/api/webhook"

# Token API de votre société
# ⚠️ NE PARTAGEZ JAMAIS CE TOKEN
SBN_API_TOKEN="{$tokenData['token']}"

# Mode debug (true/false)
# Active les logs détaillés dans la console et le fichier webhook.log
SBN_DEBUG="false"

# Timeout pour les requêtes HTTP (en secondes)
SBN_TIMEOUT="30"
EOF;

        // Headers pour téléchargement
        header('Content-Type: application/x-sh');
        header('Content-Disposition: attachment; filename="config.sh"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-cache, must-revalidate');

        echo $content;
        exit;
    }

    /**
     * Nettoyer la session du nouveau token (appelé après fermeture de la modal)
     */
    public function clearToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_SESSION['new_token'])) {
                unset($_SESSION['new_token']);
            }

            // Retourner une réponse JSON simple
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
    }

    /**
     * Télécharger le package Synology (.spk)
     */
    public function downloadSpk() {
        $user = $this->getUser();
        $tokenId = $this->get('token_id');

        if (!$user || !$tokenId) {
            $this->redirect('settings/api');
        }

        // Récupérer le token
        $stmt = $this->db->prepare("
            SELECT t.*, c.name as company_name
            FROM api_tokens t
            LEFT JOIN companies c ON t.company_id = c.id
            WHERE t.id = ? AND t.company_id = ? AND t.user_id = ?
        ");
        $stmt->execute([$tokenId, $user['company_id'], $user['id']]);
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tokenData) {
            $this->setFlash('error', 'Token non trouvé');
            $this->redirect('settings/api');
        }

        try {
            // Générer le package
            $spkPath = $this->generateSpkPackage($tokenData, $user);

            // Télécharger le fichier
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="SBN-Backup-Notifier-' . $tokenData['name'] . '.spk"');
            header('Content-Length: ' . filesize($spkPath));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: public');

            readfile($spkPath);

            // Nettoyer le fichier temporaire
            @unlink($spkPath);

            // Logger l'action
            $this->logAudit(
                $user['id'],
                'spk_downloaded',
                "Package SPK téléchargé pour le token: {$tokenData['name']}",
                $_SERVER['REMOTE_ADDR']
            );

            exit;

        } catch (\Exception $e) {
            error_log('Erreur génération SPK: ' . $e->getMessage());
            $this->setFlash('error', 'Erreur lors de la génération du package');
            $this->redirect('settings/api');
        }
    }

    /**
     * Générer le package .spk
     */
                private function generateSpkPackage($tokenData, $user) {
        require_once __DIR__ . '/../Helpers/TarCreator.php';

        $templateDir = __DIR__ . '/../../storage/spk-template';
        $tempDir = sys_get_temp_dir() . '/sbn-spk-' . uniqid();
        $outputFile = sys_get_temp_dir() . '/SBN-' . uniqid() . '.spk';

        try {
            // Créer le répertoire temporaire
            if (!mkdir($tempDir, 0777, true)) {
                throw new \Exception("Impossible de créer le dossier temporaire");
            }

            mkdir($tempDir . '/package', 0777, true);

            // 1. Préparer les fichiers
            $infoContent = file_get_contents($templateDir . '/INFO');
            $privilegeContent = file_get_contents($templateDir . '/conf/privilege');
            $icon72Content = file_get_contents($templateDir . '/PACKAGE_ICON.PNG');
            $icon256Content = file_get_contents($templateDir . '/PACKAGE_ICON_256.PNG');
            $installerContent = file_get_contents($templateDir . '/scripts/installer');
            $startStopContent = file_get_contents($templateDir . '/scripts/start-stop-status');
            $webhookContent = file_get_contents($templateDir . '/package/webhook.sh');
            $readmeContent = file_get_contents($templateDir . '/package/README.md');

            // IMPORTANT: Forcer les fins de ligne Unix (LF) pour compatibilité Synology
            $infoContent = str_replace("\r\n", "\n", str_replace("\r", "\n", $infoContent));
            $installerContent = str_replace("\r\n", "\n", str_replace("\r", "\n", $installerContent));
            $startStopContent = str_replace("\r\n", "\n", str_replace("\r", "\n", $startStopContent));
            $webhookContent = str_replace("\r\n", "\n", str_replace("\r", "\n", $webhookContent));
            $readmeContent = str_replace("\r\n", "\n", str_replace("\r", "\n", $readmeContent));

            // 2. Configuration personnalisée
            $configTemplate = file_get_contents($templateDir . '/package/config.sh.template');
            $configContent = str_replace(
                ['{{API_URL}}', '{{API_TOKEN}}', '{{COMPANY_NAME}}', '{{USER_EMAIL}}'],
                [
                    APP_URL . '/api/webhook',
                    $tokenData['token'],
                    $tokenData['company_name'] ?? 'N/A',
                    $user['email']
                ],
                $configTemplate
            );
            $configContent = str_replace("\r\n", "\n", str_replace("\r", "\n", $configContent));

            // 3. Créer package.tgz (avec TarCreator)
            $packageTar = new \App\Helpers\TarCreator();
            $packageTar->addFile('webhook.sh', $webhookContent, 0755);
            $packageTar->addFile('config.sh', $configContent, 0644);
            $packageTar->addFile('README.md', $readmeContent, 0644);
            $packageTar->finalize();

            // Compresser avec gzip
            $packageTarContent = $packageTar->getArchive();
            $packageTgzContent = gzencode($packageTarContent, 9);

            // 4. Créer le fichier .spk dans le BON ORDRE
            // ORDRE IMPORTANT: INFO, icônes, conf/, scripts/, package.tgz
            $spkTar = new \App\Helpers\TarCreator();

            // 1. INFO (DOIT ÊTRE EN PREMIER)
            $spkTar->addFile('INFO', $infoContent, 0644);

            // 2. Icônes du package
            $spkTar->addFile('PACKAGE_ICON.PNG', $icon72Content, 0644);
            $spkTar->addFile('PACKAGE_ICON_256.PNG', $icon256Content, 0644);

            // 3. Dossier conf/ et fichier privilege (requis pour DSM 7)
            $spkTar->addDirectory('conf');
            $spkTar->addFile('conf/privilege', $privilegeContent, 0644);

            // 4. Le dossier scripts/ et son contenu
            $spkTar->addDirectory('scripts');
            $spkTar->addFile('scripts/installer', $installerContent, 0755);
            $spkTar->addFile('scripts/start-stop-status', $startStopContent, 0755);

            // 5. package.tgz (EN DERNIER)
            $spkTar->addFile('package.tgz', $packageTgzContent, 0644);

            // Sauvegarder le SPK
            $spkTar->save($outputFile);

            // Nettoyer
            $this->deleteDirectory($tempDir);

            if (!file_exists($outputFile)) {
                throw new \Exception("Le fichier SPK n'a pas été créé");
            }

            return $outputFile;

        } catch (\Exception $e) {
            if (isset($tempDir) && is_dir($tempDir)) {
                $this->deleteDirectory($tempDir);
            }
            throw $e;
        }
    }

    /**
     * Supprimer un répertoire récursivement
     */
    private function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : @unlink($path);
        }
        @rmdir($dir);
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
