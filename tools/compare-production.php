<?php
/**
 * Outil de comparaison entre version locale et production
 * Analyse les différences et génère un rapport
 */

$localPath = __DIR__ . '/..';
$productionPath = 'C:/xampp/htdocs/sbn-v1-production';

echo "╔════════════════════════════════════════════════╗\n";
echo "║   Analyse Production vs Local                  ║\n";
echo "╚════════════════════════════════════════════════╝\n\n";

// Fonction pour scanner récursivement
function scanDirectory($dir, $basePath = '') {
    $files = [];
    $items = scandir($dir);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $fullPath = $dir . '/' . $item;
        $relativePath = $basePath ? $basePath . '/' . $item : $item;

        // Ignorer certains dossiers
        if (in_array($item, ['storage', '.git', 'vendor', 'node_modules', 'tools', 'tests'])) {
            continue;
        }

        if (is_dir($fullPath)) {
            $files = array_merge($files, scanDirectory($fullPath, $relativePath));
        } else {
            $files[] = $relativePath;
        }
    }

    return $files;
}

// Scanner les deux dossiers
echo "📂 Scan des fichiers...\n";
$localFiles = scanDirectory($localPath);
$productionFiles = scanDirectory($productionPath);

// Comparer
$onlyLocal = array_diff($localFiles, $productionFiles);
$onlyProduction = array_diff($productionFiles, $localFiles);
$common = array_intersect($localFiles, $productionFiles);

echo "\n" . str_repeat('═', 50) . "\n";
echo "📊 STATISTIQUES\n";
echo str_repeat('═', 50) . "\n";
echo "Total fichiers local:       " . count($localFiles) . "\n";
echo "Total fichiers production:  " . count($productionFiles) . "\n";
echo "Fichiers communs:           " . count($common) . "\n";
echo "Uniquement local:           " . count($onlyLocal) . "\n";
echo "Uniquement production:      " . count($onlyProduction) . "\n";

// Fichiers uniquement en local
if (count($onlyLocal) > 0) {
    echo "\n" . str_repeat('═', 50) . "\n";
    echo "📁 FICHIERS UNIQUEMENT EN LOCAL\n";
    echo str_repeat('═', 50) . "\n";
    foreach ($onlyLocal as $file) {
        echo "  - $file\n";
    }
}

// Fichiers uniquement en production
if (count($onlyProduction) > 0) {
    echo "\n" . str_repeat('═', 50) . "\n";
    echo "📁 FICHIERS UNIQUEMENT EN PRODUCTION\n";
    echo str_repeat('═', 50) . "\n";
    foreach ($onlyProduction as $file) {
        echo "  - $file\n";
    }
}

// Comparer le contenu des fichiers communs
echo "\n" . str_repeat('═', 50) . "\n";
echo "🔍 ANALYSE DES DIFFÉRENCES DE CONTENU\n";
echo str_repeat('═', 50) . "\n";

$different = [];
foreach ($common as $file) {
    $localFile = $localPath . '/' . $file;
    $prodFile = $productionPath . '/' . $file;

    if (file_exists($localFile) && file_exists($prodFile)) {
        $localHash = md5_file($localFile);
        $prodHash = md5_file($prodFile);

        if ($localHash !== $prodHash) {
            $different[] = $file;

            // Analyser la taille
            $localSize = filesize($localFile);
            $prodSize = filesize($prodFile);
            $sizeDiff = $prodSize - $localSize;
            $sizeDiffStr = $sizeDiff > 0 ? "+$sizeDiff" : "$sizeDiff";

            echo "  ⚠️  $file ($sizeDiffStr bytes)\n";
        }
    }
}

echo "\n" . str_repeat('═', 50) . "\n";
echo "📈 RÉSUMÉ\n";
echo str_repeat('═', 50) . "\n";
echo "Fichiers modifiés: " . count($different) . "\n";

if (count($different) > 0) {
    echo "\n✅ Fichiers critiques modifiés:\n";

    $critical = ['.env', '.htaccess', 'index.php', 'config/config.php', 'config/database.php'];

    foreach ($critical as $criticalFile) {
        if (in_array($criticalFile, $different)) {
            echo "  🔴 $criticalFile (CRITIQUE)\n";
        }
    }
}

echo "\n" . str_repeat('═', 50) . "\n";
echo "✅ Analyse terminée!\n";
echo str_repeat('═', 50) . "\n";
