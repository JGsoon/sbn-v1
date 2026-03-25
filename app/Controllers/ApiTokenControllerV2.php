<?php
/**
 * Version corrigée pour génération SPK compatible Synology
 * Utilise PharData sans compression pour le .spk final
 */

namespace App\Controllers;

use App\Core\Controller;
use PDO;

class ApiTokenControllerV2 extends Controller {

    /**
     * Générer le package .spk (version 100% compatible Synology)
     */
    private function generateSpkPackage($tokenData, $user) {
        $templateDir = __DIR__ . '/../../storage/spk-template';
        $tempDir = sys_get_temp_dir() . '/sbn-spk-' . uniqid();
        $outputFile = sys_get_temp_dir() . '/SBN-' . uniqid() . '.tar';

        try {
            // Créer le répertoire temporaire
            if (!mkdir($tempDir, 0777, true)) {
                throw new \Exception("Impossible de créer le dossier temporaire");
            }

            mkdir($tempDir . '/scripts', 0777, true);
            mkdir($tempDir . '/package', 0777, true);

            // 1. Fichier INFO
            copy($templateDir . '/INFO', $tempDir . '/INFO');

            // 2. Scripts
            copy($templateDir . '/scripts/installer', $tempDir . '/scripts/installer');
            copy($templateDir . '/scripts/start-stop-status', $tempDir . '/scripts/start-stop-status');

            // 3. Webhook script
            copy($templateDir . '/package/webhook.sh', $tempDir . '/package/webhook.sh');

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

            // Créer package.tgz avec PharData
            $packageTgz = $tempDir . '/package.tgz';

            // Utiliser PharData pour créer le .tar.gz
            $pharPackage = new \PharData($tempDir . '/package.tar');
            $pharPackage->buildFromDirectory($tempDir . '/package');
            $pharPackage->compress(\Phar::GZ);

            // Renommer package.tar.gz en package.tgz
            if (file_exists($tempDir . '/package.tar.gz')) {
                rename($tempDir . '/package.tar.gz', $packageTgz);
            }

            // Supprimer package.tar intermédiaire
            if (file_exists($tempDir . '/package.tar')) {
                unlink($tempDir . '/package.tar');
            }

            // Supprimer le dossier package source
            $this->deleteDirectory($tempDir . '/package');

            // Créer le fichier .spk final (TAR non compressé)
            // IMPORTANT: Le .spk doit être un TAR non compressé
            $pharSpk = new \PharData($outputFile);
            $pharSpk->addFile($tempDir . '/INFO', 'INFO');
            $pharSpk->addFile($packageTgz, 'package.tgz');

            // Ajouter le dossier scripts
            $pharSpk->addFile($tempDir . '/scripts/installer', 'scripts/installer');
            $pharSpk->addFile($tempDir . '/scripts/start-stop-status', 'scripts/start-stop-status');

            unset($pharSpk);

            // Nettoyer le répertoire temporaire
            $this->deleteDirectory($tempDir);

            if (!file_exists($outputFile)) {
                throw new \Exception("Le fichier SPK n'a pas été créé");
            }

            return $outputFile;

        } catch (\Exception $e) {
            // Nettoyer en cas d'erreur
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
}
