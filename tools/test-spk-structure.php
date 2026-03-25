<?php
/**
 * Tester la structure du SPK généré
 */

require_once __DIR__ . '/../app/Helpers/TarCreator.php';

$templateDir = __DIR__ . '/../storage/spk-template';

echo "========================================\n";
echo "Test de la structure du SPK\n";
echo "========================================\n\n";

// Vérifier que tous les fichiers nécessaires existent
$requiredFiles = [
    'INFO',
    'PACKAGE_ICON.PNG',
    'PACKAGE_ICON_256.PNG',
    'scripts/installer',
    'scripts/start-stop-status',
    'package/webhook.sh',
    'package/config.sh.template',
    'package/README.md'
];

echo "1. Vérification des fichiers template:\n";
$allFilesExist = true;
foreach ($requiredFiles as $file) {
    $path = $templateDir . '/' . $file;
    $exists = file_exists($path);
    $status = $exists ? '✅' : '❌';
    echo "$status $file\n";
    if (!$exists) {
        $allFilesExist = false;
    }
}
echo "\n";

if (!$allFilesExist) {
    echo "❌ ERREUR: Des fichiers template sont manquants!\n";
    exit(1);
}

// Lire le contenu du fichier INFO
echo "2. Contenu du fichier INFO:\n";
echo "----------------------------------------\n";
$infoContent = file_get_contents($templateDir . '/INFO');
echo $infoContent;
echo "----------------------------------------\n\n";

// Vérifier que INFO ne contient pas les champs UI problématiques
if (strpos($infoContent, 'dsmuidir') !== false) {
    echo "⚠️ ATTENTION: Le fichier INFO contient 'dsmuidir'\n";
}
if (strpos($infoContent, 'dsmappname') !== false) {
    echo "⚠️ ATTENTION: Le fichier INFO contient 'dsmappname'\n";
}

// Vérifier les options silent
if (strpos($infoContent, 'silent_install="yes"') !== false) {
    echo "✅ Option silent_install présente\n";
} else {
    echo "⚠️ Option silent_install manquante\n";
}

echo "\n";

// Créer un SPK de test
echo "3. Création d'un SPK de test:\n";
$outputFile = sys_get_temp_dir() . '/TEST-SBN-' . uniqid() . '.spk';

try {
    $spkTar = new \App\Helpers\TarCreator();

    // Ordre de création
    echo "   - Ajout de INFO\n";
    $spkTar->addFile('INFO', $infoContent, 0644);

    echo "   - Ajout de PACKAGE_ICON.PNG\n";
    $spkTar->addFile('PACKAGE_ICON.PNG', file_get_contents($templateDir . '/PACKAGE_ICON.PNG'), 0644);

    echo "   - Ajout de PACKAGE_ICON_256.PNG\n";
    $spkTar->addFile('PACKAGE_ICON_256.PNG', file_get_contents($templateDir . '/PACKAGE_ICON_256.PNG'), 0644);

    echo "   - Ajout du dossier scripts/\n";
    $spkTar->addDirectory('scripts');

    echo "   - Ajout de scripts/installer\n";
    $spkTar->addFile('scripts/installer', file_get_contents($templateDir . '/scripts/installer'), 0755);

    echo "   - Ajout de scripts/start-stop-status\n";
    $spkTar->addFile('scripts/start-stop-status', file_get_contents($templateDir . '/scripts/start-stop-status'), 0755);

    echo "   - Création de package.tgz\n";
    $packageTar = new \App\Helpers\TarCreator();
    $packageTar->addFile('webhook.sh', file_get_contents($templateDir . '/package/webhook.sh'), 0755);
    $packageTar->addFile('config.sh', "#!/bin/bash\n# Test config\n", 0644);
    $packageTar->addFile('README.md', file_get_contents($templateDir . '/package/README.md'), 0644);
    $packageTar->finalize();

    $packageTgzContent = gzencode($packageTar->getArchive(), 9);

    echo "   - Ajout de package.tgz\n";
    $spkTar->addFile('package.tgz', $packageTgzContent, 0644);

    // Sauvegarder
    $spkTar->save($outputFile);

    echo "\n✅ SPK de test créé: $outputFile\n";
    echo "   Taille: " . number_format(filesize($outputFile)) . " bytes\n\n";

    // Lister le contenu avec tar
    echo "4. Structure du SPK (ordre des fichiers):\n";
    echo "----------------------------------------\n";
    $cmd = 'tar -tf "' . $outputFile . '"';
    passthru($cmd);
    echo "----------------------------------------\n\n";

    echo "✅ Test terminé avec succès!\n";
    echo "\nVous pouvez maintenant tester l'installation de: $outputFile\n";
    echo "Ou supprimer le fichier si vous n'en avez plus besoin.\n";

} catch (\Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
