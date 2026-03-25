<?php
/**
 * Créer un SPK minimal avec PharData (méthode PHP native)
 */

$outputFile = __DIR__ . '/MINIMAL-PHAR-' . date('YmdHis') . '.spk';

echo "========================================\n";
echo "Création d'un SPK minimal avec PharData\n";
echo "========================================\n\n";

try {
    // Créer un répertoire temporaire
    $tempDir = sys_get_temp_dir() . '/minimal-spk-phar-' . uniqid();
    mkdir($tempDir, 0777, true);
    mkdir($tempDir . '/scripts', 0777, true);

    // 1. Fichier INFO minimal
    $infoContent = "package=\"SBNBackupNotifier\"\n";
    $infoContent .= "version=\"1.0.0\"\n";
    $infoContent .= "displayname=\"SBN Backup Notifier\"\n";
    $infoContent .= "description=\"Test package\"\n";
    $infoContent .= "maintainer=\"Soon22\"\n";
    $infoContent .= "arch=\"noarch\"\n";
    $infoContent .= "os_min_ver=\"6.0-0000\"\n";
    file_put_contents($tempDir . '/INFO', $infoContent);
    echo "✅ INFO créé (" . strlen($infoContent) . " bytes)\n";

    // 2. Scripts minimaux
    $installerContent = "#!/bin/bash\n";
    $installerContent .= "case \$1 in\n";
    $installerContent .= "  preinst) exit 0 ;;\n";
    $installerContent .= "  postinst) exit 0 ;;\n";
    $installerContent .= "  preuninst) exit 0 ;;\n";
    $installerContent .= "  postuninst) exit 0 ;;\n";
    $installerContent .= "esac\n";
    $installerContent .= "exit 0\n";
    file_put_contents($tempDir . '/scripts/installer', $installerContent);
    echo "✅ scripts/installer créé (" . strlen($installerContent) . " bytes)\n";

    $startStopContent = "#!/bin/bash\n";
    $startStopContent .= "case \$1 in\n";
    $startStopContent .= "  start) exit 0 ;;\n";
    $startStopContent .= "  stop) exit 0 ;;\n";
    $startStopContent .= "  status) exit 0 ;;\n";
    $startStopContent .= "esac\n";
    $startStopContent .= "exit 0\n";
    file_put_contents($tempDir . '/scripts/start-stop-status', $startStopContent);
    echo "✅ scripts/start-stop-status créé (" . strlen($startStopContent) . " bytes)\n\n";

    // 3. Créer package.tgz avec PharData
    echo "Création de package.tgz...\n";
    $packageDir = $tempDir . '/package_content';
    mkdir($packageDir, 0777, true);

    $readmeContent = "# SBN Backup Notifier\n\nTest package\n";
    file_put_contents($packageDir . '/README.md', $readmeContent);

    // Créer package.tar avec PharData
    $packageTarPath = $tempDir . '/package.tar';
    $packageTar = new PharData($packageTarPath);
    $packageTar->buildFromDirectory($packageDir);

    // Compresser en .tgz
    $packageTar->compress(Phar::GZ);
    $packageTgzPath = $tempDir . '/package.tgz';
    rename($packageTarPath . '.gz', $packageTgzPath);
    unlink($packageTarPath); // Supprimer le .tar non compressé

    echo "✅ package.tgz créé (" . filesize($packageTgzPath) . " bytes)\n\n";

    // 4. Créer le SPK avec PharData dans le BON ORDRE
    echo "Création du fichier SPK...\n";

    // Copier package.tgz dans le répertoire principal
    copy($packageTgzPath, $tempDir . '/package.tgz');

    // Créer le SPK - IMPORTANT: ajouter les fichiers dans l'ordre
    $spk = new PharData($outputFile);

    // L'ordre est crucial pour Synology
    echo "   - Ajout de INFO\n";
    $spk->addFile($tempDir . '/INFO', 'INFO');

    echo "   - Ajout de scripts/installer\n";
    $spk->addFile($tempDir . '/scripts/installer', 'scripts/installer');

    echo "   - Ajout de scripts/start-stop-status\n";
    $spk->addFile($tempDir . '/scripts/start-stop-status', 'scripts/start-stop-status');

    echo "   - Ajout de package.tgz\n";
    $spk->addFile($tempDir . '/package.tgz', 'package.tgz');

    unset($spk); // Fermer l'archive

    echo "\n✅ SPK créé: $outputFile\n";
    echo "   Taille: " . number_format(filesize($outputFile)) . " bytes\n\n";

    // Nettoyer
    function deleteDirectory($dir) {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? deleteDirectory($path) : @unlink($path);
        }
        @rmdir($dir);
    }
    deleteDirectory($tempDir);

    // Inspecter le contenu avec notre outil
    echo "Contenu du SPK:\n";
    echo "----------------------------------------\n";
    require_once __DIR__ . '/read-tar-content.php';

    $fp = fopen($outputFile, 'rb');
    $fileNum = 0;

    while (!feof($fp)) {
        $header = fread($fp, 512);
        if (strlen($header) < 512) break;
        if (trim($header) === '') break;

        $filename = trim(substr($header, 0, 100));
        if (empty($filename)) break;

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
    echo "----------------------------------------\n\n";

    echo "✅ Package SPK minimal créé avec PharData!\n";
    echo "   Testez ce fichier sur votre DS1522+.\n";
    echo "   Si celui-ci fonctionne, le problème vient de notre TarCreator.\n";
    echo "   Sinon, il y a un autre problème structurel.\n";

} catch (\Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "   " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
