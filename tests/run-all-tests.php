<?php
/**
 * Lance tous les tests
 * Usage: php tests/run-all-tests.php
 */

echo "╔════════════════════════════════════════════════╗" . PHP_EOL;
echo "║        SBN - Test Suite Runner                 ║" . PHP_EOL;
echo "╚════════════════════════════════════════════════╝" . PHP_EOL;

$startTime = microtime(true);

// Fonction pour exécuter un fichier de test
function runTestFile($file) {
    echo PHP_EOL . "📁 Running: " . basename($file) . PHP_EOL;
    echo str_repeat('═', 50) . PHP_EOL;

    $output = [];
    $returnCode = 0;

    exec("php " . escapeshellarg($file) . " 2>&1", $output, $returnCode);

    foreach ($output as $line) {
        echo $line . PHP_EOL;
    }

    return $returnCode === 0;
}

// Collecter tous les fichiers de test
$testDirs = [
    __DIR__ . '/Unit',
    __DIR__ . '/Integration',
    __DIR__ . '/E2E'
];

$allPassed = true;
$totalTests = 0;

foreach ($testDirs as $dir) {
    if (!is_dir($dir)) {
        echo "⚠️  Directory not found: {$dir}" . PHP_EOL;
        continue;
    }

    $files = glob($dir . '/*Test.php');

    foreach ($files as $file) {
        $totalTests++;
        if (!runTestFile($file)) {
            $allPassed = false;
        }
    }
}

$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);

echo PHP_EOL . str_repeat('═', 50) . PHP_EOL;
echo "🏁 ALL TESTS COMPLETED in {$duration}s" . PHP_EOL;
echo str_repeat('═', 50) . PHP_EOL;

if ($allPassed) {
    echo "✅ ALL TEST SUITES PASSED!" . PHP_EOL;
    exit(0);
} else {
    echo "❌ SOME TESTS FAILED!" . PHP_EOL;
    exit(1);
}
