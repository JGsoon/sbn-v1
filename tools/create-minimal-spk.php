<?php
/**
 * Créer un SPK minimal avec PharData (format GNU tar standard)
 */

$outputFile = __DIR__ . '/MINIMAL-SBN-' . date('YmdHis') . '.spk';

echo "========================================\n";
echo "Création d'un SPK minimal avec PharData\n";
echo "========================================\n\n";

try {
    // Créer un répertoire temporaire
    $tempDir = sys_get_temp_dir() . '/minimal-spk-' . uniqid();
    mkdir($tempDir, 0777, true);
    mkdir($tempDir . '/scripts', 0777, true);
    mkdir($tempDir . '/package', 0777, true);

    // 1. Fichier INFO minimal
    $infoContent = "package=\"SBNBackupNotifier\"\n";
    $infoContent .= "version=\"1.0.0\"\n";
    $infoContent .= "displayname=\"SBN Backup Notifier\"\n";
    $infoContent .= "description=\"Test package\"\n";
    $infoContent .= "maintainer=\"Soon22\"\n";
    $infoContent .= "arch=\"noarch\"\n";
    $infoContent .= "os_min_ver=\"6.0-0000\"\n";
    file_put_contents($tempDir . '/INFO', $infoContent);
    echo "✅ INFO créé\n";

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
    chmod($tempDir . '/scripts/installer', 0755);
    echo "✅ scripts/installer créé\n";

    $startStopContent = "#!/bin/bash\n";
    $startStopContent .= "case \$1 in\n";
    $startStopContent .= "  start) exit 0 ;;\n";
    $startStopContent .= "  stop) exit 0 ;;\n";
    $startStopContent .= "  status) exit 0 ;;\n";
    $startStopContent .= "esac\n";
    $startStopContent .= "exit 0\n";
    file_put_contents($tempDir . '/scripts/start-stop-status', $startStopContent);
    chmod($tempDir . '/scripts/start-stop-status', 0755);
    echo "✅ scripts/start-stop-status créé\n";

    // 3. Package minimal (juste un README)
    $readmeContent = "# SBN Backup Notifier\n\nTest package\n";
    file_put_contents($tempDir . '/package/README.md', $readmeContent);
    echo "✅ package/README.md créé\n\n";

    // 4. Créer package.tgz avec tar système
    echo "Création de package.tgz...\n";
    $packageTgzFile = $tempDir . '/package.tgz';
    chdir($tempDir . '/package');
    $cmd = 'tar -czf "' . $packageTgzFile . '" README.md';
    exec($cmd, $output, $returnCode);
    if ($returnCode !== 0) {
        throw new \Exception("Erreur création package.tgz");
    }
    echo "✅ package.tgz créé (" . filesize($packageTgzFile) . " bytes)\n\n";

    // 5. Créer le SPK avec tar système (IMPORTANT: ordre correct)
    echo "Création du fichier SPK...\n";
    chdir($tempDir);

    // Utiliser tar avec l'ordre spécifique
    $cmd = 'tar -cf "' . $outputFile . '" INFO scripts/installer scripts/start-stop-status package.tgz';
    exec($cmd, $output, $returnCode);
    if ($returnCode !== 0) {
        throw new \Exception("Erreur création SPK: " . implode("\n", $output));
    }

    echo "✅ SPK créé: $outputFile\n";
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

    // Inspecter le contenu
    echo "Contenu du SPK:\n";
    echo "----------------------------------------\n";
    $cmd = 'tar -tf "' . $outputFile . '"';
    passthru($cmd);
    echo "----------------------------------------\n\n";

    echo "✅ Package SPK minimal créé!\n";
    echo "   Testez ce fichier sur votre NAS.\n";
    echo "   Si celui-ci fonctionne, le problème vient de notre TarCreator.\n";

} catch (\Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
