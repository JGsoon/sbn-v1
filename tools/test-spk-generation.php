<?php
/**
 * Script de test de génération SPK
 * Simule la création d'un package SPK et vérifie sa structure
 */

echo "╔════════════════════════════════════════════════╗\n";
echo "║   Test de génération de package SPK           ║\n";
echo "╚════════════════════════════════════════════════╝\n\n";

$templateDir = __DIR__ . '/../storage/spk-template';
$tempDir = sys_get_temp_dir() . '/sbn-spk-test-' . uniqid();
$outputFile = sys_get_temp_dir() . '/SBN-TEST.spk';

echo "1. Vérification du template...\n";

// Vérifier que le template existe
$requiredFiles = [
    $templateDir . '/INFO',
    $templateDir . '/scripts/installer',
    $templateDir . '/scripts/start-stop-status',
    $templateDir . '/package/webhook.sh',
    $templateDir . '/package/config.sh.template',
    $templateDir . '/package/README.md',
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "  ✅ " . basename(dirname($file)) . "/" . basename($file) . "\n";
    } else {
        echo "  ❌ MANQUANT: $file\n";
        exit(1);
    }
}

echo "\n2. Création du dossier temporaire...\n";
mkdir($tempDir, 0777, true);
mkdir($tempDir . '/scripts', 0777, true);
mkdir($tempDir . '/package', 0777, true);
echo "  ✅ $tempDir\n";

echo "\n3. Copie des fichiers...\n";

// Copier les fichiers
copy($templateDir . '/INFO', $tempDir . '/INFO');
copy($templateDir . '/scripts/installer', $tempDir . '/scripts/installer');
copy($templateDir . '/scripts/start-stop-status', $tempDir . '/scripts/start-stop-status');
copy($templateDir . '/package/webhook.sh', $tempDir . '/package/webhook.sh');
copy($templateDir . '/package/README.md', $tempDir . '/package/README.md');

// Créer config.sh de test
$config = <<<'CONFIG'
#!/bin/bash
API_URL="https://sbn.soon22.fr/api/webhook"
API_TOKEN="sbn_test_token_123456789"
COMPANY_NAME="Test Company"
USER_EMAIL="test@example.com"
LOG_RETENTION_DAYS=30
PACKAGE_VERSION="1.0.0"
CONFIG;

file_put_contents($tempDir . '/package/config.sh', $config);
chmod($tempDir . '/scripts/installer', 0755);
chmod($tempDir . '/scripts/start-stop-status', 0755);
chmod($tempDir . '/package/webhook.sh', 0755);

echo "  ✅ Fichiers copiés\n";

echo "\n4. Création de package.tgz...\n";

$packageTgz = $tempDir . '/package.tgz';
$currentDir = getcwd();
chdir($tempDir . '/package');

$files = scandir('.');
$files = array_filter($files, fn($f) => $f !== '.' && $f !== '..');
$filesStr = implode(' ', $files); // Ne pas utiliser escapeshellarg pour les fichiers

// Utiliser un chemin relatif pour le fichier de sortie
exec("tar -czf ../package.tgz * 2>&1", $output, $returnCode);

// Déplacer package.tgz au bon endroit
if (file_exists($tempDir . '/package.tgz')) {
    $packageTgz = $tempDir . '/package.tgz';
}

chdir($currentDir);

if ($returnCode === 0 && file_exists($packageTgz)) {
    echo "  ✅ package.tgz créé (" . filesize($packageTgz) . " bytes)\n";
} else {
    echo "  ❌ Échec de création de package.tgz\n";
    print_r($output);
    exit(1);
}

// Supprimer le dossier package
function deleteDirectory($dir) {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    rmdir($dir);
}

deleteDirectory($tempDir . '/package');

echo "\n5. Création du fichier .spk...\n";

chdir($tempDir);
// Créer le .spk dans le dossier courant puis le déplacer
exec('tar -cf output.spk INFO scripts package.tgz 2>&1', $output, $returnCode);

if (file_exists('output.spk')) {
    rename('output.spk', $outputFile);
}

chdir($currentDir);

if ($returnCode === 0 && file_exists($outputFile)) {
    echo "  ✅ $outputFile créé (" . filesize($outputFile) . " bytes)\n";
} else {
    echo "  ❌ Échec de création du .spk\n";
    print_r($output);
    exit(1);
}

echo "\n6. Vérification de la structure du .spk...\n";

exec('tar -tf ' . escapeshellarg($outputFile) . ' 2>&1', $tarContent, $returnCode);

if ($returnCode === 0) {
    echo "  ✅ Contenu du .spk:\n";
    foreach ($tarContent as $line) {
        echo "     - $line\n";
    }

    // Vérifier les fichiers requis
    $required = ['INFO', 'scripts/installer', 'scripts/start-stop-status', 'package.tgz'];
    $missing = [];

    foreach ($required as $req) {
        if (!in_array($req, $tarContent)) {
            $missing[] = $req;
        }
    }

    if (empty($missing)) {
        echo "\n  ✅ Tous les fichiers requis sont présents\n";
    } else {
        echo "\n  ❌ Fichiers manquants:\n";
        foreach ($missing as $m) {
            echo "     - $m\n";
        }
    }
} else {
    echo "  ❌ Impossible de lire le contenu du .spk\n";
}

echo "\n7. Vérification du fichier INFO...\n";

exec('tar -xOf ' . escapeshellarg($outputFile) . ' INFO 2>&1', $infoContent, $returnCode);

if ($returnCode === 0 && !empty($infoContent)) {
    echo "  ✅ Fichier INFO lisible:\n";
    foreach (array_slice($infoContent, 0, 5) as $line) {
        echo "     $line\n";
    }
} else {
    echo "  ❌ Impossible de lire le fichier INFO\n";
}

echo "\n" . str_repeat('═', 50) . "\n";
echo "✅ TEST TERMINÉ\n";
echo str_repeat('═', 50) . "\n\n";

echo "Fichier de test généré: $outputFile\n";
echo "Taille: " . filesize($outputFile) . " bytes\n\n";

echo "📋 Vous pouvez maintenant:\n";
echo "1. Essayer d'installer ce fichier sur votre NAS\n";
echo "2. Si ça fonctionne, le générateur est OK !\n\n";

// Nettoyer
deleteDirectory($tempDir);
echo "Dossier temporaire nettoyé.\n";
