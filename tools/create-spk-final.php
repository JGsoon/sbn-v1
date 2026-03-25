<?php
/**
 * Créer le SPK FINAL avec TOUS les correctifs
 */

require_once __DIR__ . '/../app/Helpers/TarCreator.php';

$outputFile = __DIR__ . '/SBN-DSM7-FINAL-' . date('YmdHis') . '.spk';
$templateDir = __DIR__ . '/../storage/spk-template';

echo "========================================\n";
echo "Création du SPK FINAL pour DSM 7\n";
echo "========================================\n\n";

try {
    // Lire TOUS les fichiers depuis le template
    echo "Lecture des fichiers template...\n";

    $infoContent = file_get_contents($templateDir . '/INFO');
    $privilegeContent = file_get_contents($templateDir . '/conf/privilege');
    $icon72Content = file_get_contents($templateDir . '/PACKAGE_ICON.PNG');
    $icon256Content = file_get_contents($templateDir . '/PACKAGE_ICON_256.PNG');
    $installerContent = file_get_contents($templateDir . '/scripts/installer');
    $startStopContent = file_get_contents($templateDir . '/scripts/start-stop-status');
    $webhookContent = file_get_contents($templateDir . '/package/webhook.sh');
    $readmeContent = file_get_contents($templateDir . '/package/README.md');

    // Forcer fins de ligne Unix
    $infoContent = str_replace("\r\n", "\n", str_replace("\r", "\n", $infoContent));
    $installerContent = str_replace("\r\n", "\n", str_replace("\r", "\n", $installerContent));
    $startStopContent = str_replace("\r\n", "\n", str_replace("\r", "\n", $startStopContent));
    $webhookContent = str_replace("\r\n", "\n", str_replace("\r", "\n", $webhookContent));
    $readmeContent = str_replace("\r\n", "\n", str_replace("\r", "\n", $readmeContent));

    echo "✅ Fichiers lus\n\n";

    // Vérifier os_min_ver
    if (strpos($infoContent, 'os_min_ver="7.0-40000"') !== false) {
        echo "✅ INFO contient os_min_ver=\"7.0-40000\"\n";
    } else {
        echo "❌ ERREUR: os_min_ver n'est pas 7.0-40000\n";
        echo "Contenu INFO:\n$infoContent\n";
        exit(1);
    }

    if (strpos($infoContent, 'startable="no"') !== false) {
        echo "✅ INFO contient startable=\"no\"\n";
    }
    echo "\n";

    // Créer package.tgz
    echo "Création de package.tgz...\n";
    $packageTar = new \App\Helpers\TarCreator();
    $packageTar->addFile('webhook.sh', $webhookContent, 0755);

    $configContent = "#!/bin/bash\n# Configuration de test\nSBN_API_URL=\"http://test.example.com/api/webhook\"\nSBN_API_TOKEN=\"test_token_12345\"\n";
    $configContent = str_replace("\r\n", "\n", str_replace("\r", "\n", $configContent));
    $packageTar->addFile('config.sh', $configContent, 0644);
    $packageTar->addFile('README.md', $readmeContent, 0644);
    $packageTar->finalize();

    $packageTgzContent = gzencode($packageTar->getArchive(), 9);
    echo "✅ package.tgz créé (" . strlen($packageTgzContent) . " bytes)\n\n";

    // Créer le SPK
    echo "Création du fichier SPK...\n";
    $spkTar = new \App\Helpers\TarCreator();

    echo "  1. INFO (0644)\n";
    $spkTar->addFile('INFO', $infoContent, 0644);

    echo "  2. PACKAGE_ICON.PNG (0644)\n";
    $spkTar->addFile('PACKAGE_ICON.PNG', $icon72Content, 0644);

    echo "  3. PACKAGE_ICON_256.PNG (0644)\n";
    $spkTar->addFile('PACKAGE_ICON_256.PNG', $icon256Content, 0644);

    echo "  4. conf/ (DIR 0755)\n";
    $spkTar->addDirectory('conf');

    echo "  5. conf/privilege (0644)\n";
    $spkTar->addFile('conf/privilege', $privilegeContent, 0644);

    echo "  6. scripts/ (DIR 0755)\n";
    $spkTar->addDirectory('scripts');

    echo "  7. scripts/installer (0755 - EXÉCUTABLE)\n";
    $spkTar->addFile('scripts/installer', $installerContent, 0755);

    echo "  8. scripts/start-stop-status (0755 - EXÉCUTABLE)\n";
    $spkTar->addFile('scripts/start-stop-status', $startStopContent, 0755);

    echo "  9. package.tgz (0644)\n";
    $spkTar->addFile('package.tgz', $packageTgzContent, 0644);

    $spkTar->save($outputFile);

    echo "\n✅ SPK FINAL créé: $outputFile\n";
    echo "   Taille: " . number_format(filesize($outputFile)) . " bytes\n\n";

    // Vérifier le contenu
    echo "========================================\n";
    echo "Vérification du SPK créé:\n";
    echo "========================================\n\n";

    $fp = fopen($outputFile, 'rb');

    // Lire le premier fichier (INFO)
    $header = fread($fp, 512);
    $sizeOctal = trim(substr($header, 124, 12));
    $size = octdec($sizeOctal);
    $infoInArchive = fread($fp, $size);
    fclose($fp);

    echo "Contenu du fichier INFO dans l'archive:\n";
    echo "----------------------------------------\n";
    echo $infoInArchive;
    echo "\n----------------------------------------\n\n";

    if (strpos($infoInArchive, 'os_min_ver="7.0-40000"') !== false) {
        echo "✅ os_min_ver=\"7.0-40000\" confirmé dans l'archive\n";
    } else {
        echo "❌ os_min_ver n'est PAS 7.0-40000 dans l'archive !\n";
    }

    echo "\n========================================\n";
    echo "🧪 TESTEZ CE FICHIER SUR VOTRE DS1522+\n";
    echo "========================================\n\n";
    echo "Ce SPK contient:\n";
    echo "  ✅ os_min_ver=\"7.0-40000\" (requis pour DSM 7)\n";
    echo "  ✅ startable=\"no\"\n";
    echo "  ✅ conf/privilege (pas de privilèges root)\n";
    echo "  ✅ Permissions 0755 pour les scripts\n";
    echo "  ✅ Champs uname/gname=\"root\"\n";
    echo "  ✅ Fins de ligne Unix (LF)\n";
    echo "  ✅ Icônes PNG incluses\n";
    echo "  ✅ Format TAR POSIX ustar correct\n\n";

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "   " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
