<?php
/**
 * Bootstrap pour les tests
 * Charge l'environnement de test et configure l'application
 */

// Définir l'environnement de test
define('ENVIRONMENT', 'test');

// Charger l'autoloader
require_once __DIR__ . '/../config/autoload.php';

// Charger les helpers
if (file_exists(__DIR__ . '/../config/helpers.php')) {
    require_once __DIR__ . '/../config/helpers.php';
}

// Charger la configuration de test
$testEnvFile = __DIR__ . '/.env.test';
if (file_exists($testEnvFile)) {
    $lines = file($testEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
        putenv(trim($line));
    }
}

// Fonction helper pour les tests
function assertValue($expected, $actual, $message = '') {
    if ($expected !== $actual) {
        $msg = $message ?: "Expected: " . var_export($expected, true) . ", Got: " . var_export($actual, true);
        throw new Exception("❌ Assertion failed: " . $msg);
    }
    echo "✅ " . ($message ?: "Test passed") . PHP_EOL;
    return true;
}

function assertTrue($condition, $message = '') {
    return assertValue(true, $condition, $message);
}

function assertFalse($condition, $message = '') {
    return assertValue(false, $condition, $message);
}

function assertEqual($expected, $actual, $message = '') {
    return assertValue($expected, $actual, $message);
}

function assertNotNull($value, $message = '') {
    if ($value === null) {
        throw new Exception("❌ " . ($message ?: "Value should not be null"));
    }
    echo "✅ " . ($message ?: "Value is not null") . PHP_EOL;
    return true;
}

// Compteur de tests
global $testsPassed, $testsFailed;
$testsPassed = 0;
$testsFailed = 0;

function runTest($name, callable $test) {
    global $testsPassed, $testsFailed;

    echo PHP_EOL . "🧪 Running: " . $name . PHP_EOL;
    echo str_repeat('-', 50) . PHP_EOL;

    try {
        $test();
        $testsPassed++;
        echo "✅ " . $name . " PASSED" . PHP_EOL;
    } catch (Exception $e) {
        $testsFailed++;
        echo "❌ " . $name . " FAILED: " . $e->getMessage() . PHP_EOL;
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    }
}

function printTestSummary() {
    global $testsPassed, $testsFailed;

    echo PHP_EOL . str_repeat('=', 50) . PHP_EOL;
    echo "📊 TEST SUMMARY" . PHP_EOL;
    echo str_repeat('=', 50) . PHP_EOL;
    echo "✅ Passed: " . $testsPassed . PHP_EOL;
    echo "❌ Failed: " . $testsFailed . PHP_EOL;
    echo "📝 Total:  " . ($testsPassed + $testsFailed) . PHP_EOL;
    echo str_repeat('=', 50) . PHP_EOL;

    if ($testsFailed > 0) {
        exit(1);
    }
}

// Register shutdown function pour afficher le résumé
register_shutdown_function('printTestSummary');
