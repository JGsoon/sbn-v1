<?php
/**
 * Script pour télécharger et inspecter un SPK directement
 */

// Simuler la génération comme le ferait ApiTokenController
require_once __DIR__ . '/../app/Helpers/TarCreator.php';
require_once __DIR__ . '/../config/config.php';

$templateDir = __DIR__ . '/../storage/spk-template';
$outputFile = __DIR__ . '/DEBUG-SBN-' . date('YmdHis') . '.spk';

echo "========================================\n";
echo "Génération d'un SPK de debug\n";
echo "========================================\n\n";

try {
    // 1. Préparer les fichiers
    echo "1. Lecture des fichiers template...\n";
    $infoContent = file_get_contents($templateDir . '/INFO');

    // Vérifier les fins de ligne du fichier INFO
    $hasWindows = strpos($infoContent, "\r\n") !== false;
    $hasMac = strpos($infoContent, "\r") !== false && !$hasWindows;
    $hasUnix = strpos($infoContent, "\n") !== false;

    echo "   INFO: " . strlen($infoContent) . " bytes\n";
    echo "   Fins de ligne: " .
         ($hasWindows ? "Windows (CRLF) ❌" : "") .
         ($hasMac ? "Mac (CR)" : "") .
         ($hasUnix && !$hasWindows ? "Unix (LF) ✅" : "") . "\n";

    // Forcer les fins de ligne Unix
    $infoContent = str_replace("\r\n", "\n", $infoContent);
    $infoContent = str_replace("\r", "\n", $infoContent);
    echo "   → Converti en Unix (LF)\n\n";

    $icon72Content = file_get_contents($templateDir . '/PACKAGE_ICON.PNG');
    $icon256Content = file_get_contents($templateDir . '/PACKAGE_ICON_256.PNG');
    $installerContent = file_get_contents($templateDir . '/scripts/installer');
    $startStopContent = file_get_contents($templateDir . '/scripts/start-stop-status');
    $webhookContent = file_get_contents($templateDir . '/package/webhook.sh');
    $readmeContent = file_get_contents($templateDir . '/package/README.md');

    // Forcer fins de ligne Unix pour les scripts bash
    $installerContent = str_replace("\r\n", "\n", $installerContent);
    $installerContent = str_replace("\r", "\n", $installerContent);
    $startStopContent = str_replace("\r\n", "\n", $startStopContent);
    $startStopContent = str_replace("\r", "\n", $startStopContent);
    $webhookContent = str_replace("\r\n", "\n", $webhookContent);
    $webhookContent = str_replace("\r", "\n", $webhookContent);

    echo "2. Vérification des fichiers:\n";
    echo "   ✅ INFO: " . strlen($infoContent) . " bytes\n";
    echo "   ✅ PACKAGE_ICON.PNG: " . strlen($icon72Content) . " bytes\n";
    echo "   ✅ PACKAGE_ICON_256.PNG: " . strlen($icon256Content) . " bytes\n";
    echo "   ✅ installer: " . strlen($installerContent) . " bytes\n";
    echo "   ✅ start-stop-status: " . strlen($startStopContent) . " bytes\n";
    echo "   ✅ webhook.sh: " . strlen($webhookContent) . " bytes\n";
    echo "   ✅ README.md: " . strlen($readmeContent) . " bytes\n\n";

    // 2. Créer package.tgz
    echo "3. Création de package.tgz...\n";
    $packageTar = new \App\Helpers\TarCreator();
    $packageTar->addFile('webhook.sh', $webhookContent, 0755);

    $configContent = "#!/bin/bash\n# Configuration de test\nSBN_API_URL=\"http://test.example.com/api/webhook\"\nSBN_API_TOKEN=\"test_token_12345\"\n";
    $packageTar->addFile('config.sh', $configContent, 0644);
    $packageTar->addFile('README.md', $readmeContent, 0644);
    $packageTar->finalize();

    $packageTarContent = $packageTar->getArchive();
    $packageTgzContent = gzencode($packageTarContent, 9);
    echo "   ✅ package.tgz: " . strlen($packageTgzContent) . " bytes (compressé)\n\n";

    // 3. Créer le SPK
    echo "4. Création du fichier SPK...\n";
    $spkTar = new \App\Helpers\TarCreator();

    echo "   - Ajout de INFO\n";
    $spkTar->addFile('INFO', $infoContent, 0644);

    echo "   - Ajout de PACKAGE_ICON.PNG\n";
    $spkTar->addFile('PACKAGE_ICON.PNG', $icon72Content, 0644);

    echo "   - Ajout de PACKAGE_ICON_256.PNG\n";
    $spkTar->addFile('PACKAGE_ICON_256.PNG', $icon256Content, 0644);

    echo "   - Ajout du dossier scripts/\n";
    $spkTar->addDirectory('scripts');

    echo "   - Ajout de scripts/installer\n";
    $spkTar->addFile('scripts/installer', $installerContent, 0755);

    echo "   - Ajout de scripts/start-stop-status\n";
    $spkTar->addFile('scripts/start-stop-status', $startStopContent, 0755);

    echo "   - Ajout de package.tgz\n";
    $spkTar->addFile('package.tgz', $packageTgzContent, 0644);

    $spkTar->save($outputFile);
    echo "\n";

    if (!file_exists($outputFile)) {
        throw new \Exception("Le fichier SPK n'a pas été créé");
    }

    echo "✅ SPK créé avec succès!\n";
    echo "   Fichier: $outputFile\n";
    echo "   Taille: " . number_format(filesize($outputFile)) . " bytes\n\n";

    // 4. Inspecter le contenu
    echo "========================================\n";
    echo "5. Inspection du contenu:\n";
    echo "========================================\n";

    require_once __DIR__ . '/read-tar-content.php';

    $fp = fopen($outputFile, 'rb');
    $fileNum = 0;

    while (!feof($fp)) {
        $header = fread($fp, 512);
        if (strlen($header) < 512) {
            break;
        }

        if (trim($header) === '') {
            break;
        }

        $filename = trim(substr($header, 0, 100));
        if (empty($filename)) {
            break;
        }

        $sizeOctal = trim(substr($header, 124, 12));
        $size = octdec($sizeOctal);
        $type = substr($header, 156, 1);
        $typeStr = ($type === '5') ? 'DIR' : 'FILE';

        $fileNum++;
        echo sprintf("%d. %-40s %8s %10s bytes\n", $fileNum, $filename, $typeStr, number_format($size));

        if ($size > 0) {
            $blocks = ceil($size / 512);
            fseek($fp, $blocks * 512, SEEK_CUR);
        }
    }

    fclose($fp);

    echo "\n✅ Fichier SPK prêt pour le test!\n";
    echo "   Copiez ce fichier sur votre NAS et installez-le.\n\n";

} catch (\Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "   " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
