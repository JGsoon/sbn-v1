<?php
/**
 * Script pour remplacer la génération SPK par une version manuelle
 */

$file = __DIR__ . '/../app/Controllers/ApiTokenController.php';
$content = file_get_contents($file);

// Nouvelle méthode qui utilise TarCreator
$newMethod = <<<'PHP'
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
            $installerContent = file_get_contents($templateDir . '/scripts/installer');
            $startStopContent = file_get_contents($templateDir . '/scripts/start-stop-status');
            $webhookContent = file_get_contents($templateDir . '/package/webhook.sh');
            $readmeContent = file_get_contents($templateDir . '/package/README.md');

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

            // 3. Créer package.tgz (avec TarCreator)
            $packageTar = new \App\Helpers\TarCreator();
            $packageTar->addFile('webhook.sh', $webhookContent, 0755);
            $packageTar->addFile('config.sh', $configContent, 0644);
            $packageTar->addFile('README.md', $readmeContent, 0644);

            $packageTgzPath = $tempDir . '/package.tgz';
            $packageTar->finalize();

            // Compresser avec gzip
            $packageTarContent = $packageTar->getArchive();
            file_put_contents($packageTgzPath, gzencode($packageTarContent, 9));

            // 4. Créer le fichier .spk (avec TarCreator)
            $spkTar = new \App\Helpers\TarCreator();

            // Ajouter INFO
            $spkTar->addFile('INFO', $infoContent, 0644);

            // Ajouter le dossier scripts/
            $spkTar->addDirectory('scripts');
            $spkTar->addFile('scripts/installer', $installerContent, 0755);
            $spkTar->addFile('scripts/start-stop-status', $startStopContent, 0755);

            // Ajouter package.tgz
            $spkTar->addFile('package.tgz', file_get_contents($packageTgzPath), 0644);

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
PHP;

// Remplacer la méthode
$pattern = '/(private function generateSpkPackage\([^)]+\) \{.*?)(\n\s+\/\*\*\s*\n\s+\* Supprimer un répertoire récursivement)/s';

if (preg_match($pattern, $content)) {
    $content = preg_replace($pattern, $newMethod . '$2', $content);
    file_put_contents($file, $content);
    echo "✅ Méthode generateSpkPackage remplacée avec TarCreator!\n";
    echo "Le fichier .spk sera maintenant créé manuellement avec le format tar POSIX.\n";
} else {
    echo "❌ Impossible de trouver la méthode à remplacer.\n";
}
