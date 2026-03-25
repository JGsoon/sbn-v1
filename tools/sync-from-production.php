<?php
/**
 * Synchronisation depuis la production
 * Copie intelligemment les fichiers de production vers local
 * en préservant tools/, tests/ et certains fichiers
 */

$productionPath = 'C:/xampp/htdocs/sbn-v1-production';
$localPath = __DIR__ . '/..';

echo "╔════════════════════════════════════════════════╗\n";
echo "║   Synchronisation Production → Local          ║\n";
echo "╚════════════════════════════════════════════════╝\n\n";

// Fichiers à NE PAS écraser
$preserve = [
    '.git',
    '.gitignore',
    'tools',
    'tests',
    'sbn-v1.code-workspace',
    '.vscode',
    'sbn-v1.rar',
    'sbn-v1.zip',
    '.env.exemple',
    '.env.production'
];

// Fonction de copie récursive
function copyDirectory($src, $dst, $preserve = [], $basePath = '') {
    $copied = 0;
    $skipped = 0;

    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }

    $items = scandir($src);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $srcPath = $src . '/' . $item;
        $dstPath = $dst . '/' . $item;
        $relativePath = $basePath ? $basePath . '/' . $item : $item;

        // Vérifier si on doit préserver
        $shouldPreserve = false;
        foreach ($preserve as $preserveItem) {
            if ($relativePath === $preserveItem || strpos($relativePath, $preserveItem . '/') === 0) {
                $shouldPreserve = true;
                break;
            }
        }

        if ($shouldPreserve) {
            echo "  ⏭️  Préservé: $relativePath\n";
            $skipped++;
            continue;
        }

        if (is_dir($srcPath)) {
            $result = copyDirectory($srcPath, $dstPath, $preserve, $relativePath);
            $copied += $result['copied'];
            $skipped += $result['skipped'];
        } else {
            // Copier le fichier
            if (copy($srcPath, $dstPath)) {
                echo "  ✅ Copié: $relativePath\n";
                $copied++;
            } else {
                echo "  ❌ Erreur: $relativePath\n";
            }
        }
    }

    return ['copied' => $copied, 'skipped' => $skipped];
}

// Confirmation
echo "⚠️  Cette opération va :\n";
echo "  1. Copier TOUS les fichiers depuis production\n";
echo "  2. Écraser les fichiers existants (sauf tools/ et tests/)\n";
echo "  3. Préserver .env.exemple et .gitignore\n\n";

echo "📂 Source:      $productionPath\n";
echo "📂 Destination: $localPath\n\n";

echo "Fichiers préservés:\n";
foreach ($preserve as $item) {
    echo "  🔒 $item\n";
}

echo "\n";
echo "Appuyez sur ENTRÉE pour continuer ou CTRL+C pour annuler...\n";
if (php_sapi_name() === 'cli') {
    fgets(STDIN);
}

echo "\n🔄 Synchronisation en cours...\n\n";

$result = copyDirectory($productionPath, $localPath, $preserve);

echo "\n" . str_repeat('═', 50) . "\n";
echo "✅ Synchronisation terminée!\n";
echo str_repeat('═', 50) . "\n";
echo "Fichiers copiés:    " . $result['copied'] . "\n";
echo "Fichiers préservés: " . $result['skipped'] . "\n";

echo "\n📝 Prochaines étapes:\n";
echo "  1. Vérifiez les fichiers avec: git status\n";
echo "  2. Comparez les changements avec: git diff\n";
echo "  3. Testez l'application en local\n";
echo "  4. Commitez les changements\n";
