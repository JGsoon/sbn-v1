<?php
/**
 * SBN v1.0 - Générateur de packages SPK (Version corrigée)
 *
 * @package SBN
 * @version 1.0.1
 */

namespace App\Controllers;

use App\Core\Controller;
use PDO;

class SpkGeneratorController extends Controller {

    /**
     * Télécharger le package Synology (.spk) - Version corrigée
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
            $filename = 'SBN-Backup-Notifier-' . preg_replace('/[^a-zA-Z0-9-_]/', '-', $tokenData['name']) . '.spk';

            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
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
            $this->setFlash('error', 'Erreur lors de la génération du package: ' . $e->getMessage());
            $this->redirect('settings/api');
        }
    }

    /**
     * Générer le package .spk (version native tar - compatible Synology)
     */
    private function generateSpkPackage($tokenData, $user) {
        $templateDir = __DIR__ . '/../../storage/spk-template';
        $tempDir = sys_get_temp_dir() . '/sbn-spk-' . uniqid();
        $outputFile = sys_get_temp_dir() . '/SBN-' . uniqid() . '.spk';

        // Créer le répertoire temporaire
        if (!mkdir($tempDir, 0777, true)) {
            throw new \Exception("Impossible de créer le dossier temporaire");
        }

        mkdir($tempDir . '/scripts', 0777, true);
        mkdir($tempDir . '/package', 0777, true);

        // 1. Fichier INFO
        if (!copy($templateDir . '/INFO', $tempDir . '/INFO')) {
            throw new \Exception("Impossible de copier le fichier INFO");
        }

        // 2. Scripts
        copy($templateDir . '/scripts/installer', $tempDir . '/scripts/installer');
        copy($templateDir . '/scripts/start-stop-status', $tempDir . '/scripts/start-stop-status');
        chmod($tempDir . '/scripts/installer', 0755);
        chmod($tempDir . '/scripts/start-stop-status', 0755);

        // 3. Webhook script
        copy($templateDir . '/package/webhook.sh', $tempDir . '/package/webhook.sh');
        chmod($tempDir . '/package/webhook.sh', 0755);

        // 4. README
        copy($templateDir . '/package/README.md', $tempDir . '/package/README.md');

        // 5. Configuration personnalisée
        $configTemplate = file_get_contents($templateDir . '/package/config.sh.template');
        $config = str_replace(
            ['{{API_URL}}', '{{API_TOKEN}}', '{{COMPANY_NAME}}', '{{USER_EMAIL}}'],
            [
                APP_URL . '/api/webhook',
                $tokenData['token'],
                $tokenData['company_name'] ?? 'N/A',
                $user['email']
            ],
            $configTemplate
        );
        file_put_contents($tempDir . '/package/config.sh', $config);
        chmod($tempDir . '/package/config.sh', 0644);

        // Créer package.tgz (archive tar du contenu de /package)
        $packageTgz = $tempDir . '/package.tgz';

        // Déterminer la commande tar disponible
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows - utiliser tar.exe (disponible depuis Windows 10)
            $tarCommand = 'tar';
        } else {
            // Linux/Unix
            $tarCommand = 'tar';
        }

        // Créer l'archive package.tgz
        $currentDir = getcwd();
        chdir($tempDir . '/package');

        $packageFiles = scandir('.');
        $packageFiles = array_filter($packageFiles, function($file) {
            return $file !== '.' && $file !== '..';
        });

        $cmd = $tarCommand . ' -czf "' . $packageTgz . '" ' . implode(' ', array_map('escapeshellarg', $packageFiles));
        exec($cmd . ' 2>&1', $output, $returnCode);

        chdir($currentDir);

        if ($returnCode !== 0 || !file_exists($packageTgz)) {
            // Si tar échoue, utiliser une méthode PHP alternative
            $packageTgz = $this->createTarGzPhp($tempDir . '/package', $packageTgz);
        }

        // Supprimer le dossier package
        $this->deleteDirectory($tempDir . '/package');

        // Créer le fichier .spk final (archive tar non compressée)
        chdir($tempDir);

        $spkFiles = ['INFO', 'scripts', 'package.tgz'];
        $cmd = $tarCommand . ' -cf "' . $outputFile . '" ' . implode(' ', array_map('escapeshellarg', $spkFiles));
        exec($cmd . ' 2>&1', $output, $returnCode);

        chdir($currentDir);

        if ($returnCode !== 0 || !file_exists($outputFile)) {
            // Méthode alternative PHP
            $outputFile = $this->createSpkPhp($tempDir, $outputFile);
        }

        // Nettoyer le répertoire temporaire
        $this->deleteDirectory($tempDir);

        if (!file_exists($outputFile)) {
            throw new \Exception("Échec de la création du package SPK");
        }

        return $outputFile;
    }

    /**
     * Créer un tar.gz avec PHP pur (fallback)
     */
    private function createTarGzPhp($sourceDir, $outputFile) {
        try {
            $phar = new \PharData($outputFile);
            $phar->buildFromDirectory($sourceDir);
            $phar->compress(\Phar::GZ);
            unset($phar);

            if (file_exists($outputFile . '.gz')) {
                rename($outputFile . '.gz', $outputFile);
            }

            return $outputFile;
        } catch (\Exception $e) {
            throw new \Exception("Impossible de créer package.tgz: " . $e->getMessage());
        }
    }

    /**
     * Créer le SPK avec PHP pur (fallback)
     */
    private function createSpkPhp($tempDir, $outputFile) {
        try {
            $phar = new \PharData($outputFile);
            $phar->buildFromDirectory($tempDir);
            unset($phar);

            return $outputFile;
        } catch (\Exception $e) {
            throw new \Exception("Impossible de créer le SPK: " . $e->getMessage());
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
