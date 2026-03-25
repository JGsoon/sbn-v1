<?php
/**
 * SBN v1.0 - Configuration générale de l'application
 *
 * @package SBN
 * @version 1.0.0
 * @author Johnny Girault
 * @license MIT
 */

// Chargement des variables d'environnement
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value, '"\''));
        }
    }
}

// Configuration de l'application
define('APP_NAME', getenv('APP_NAME') ?: 'SBN v1.0');
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');

// Détection automatique de l'URL de base
if (getenv('APP_URL')) {
    define('APP_URL', rtrim(getenv('APP_URL'), '/'));
} else {
    // Auto-détection pour compatibilité multi-environnements
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $baseDir = str_replace('\\', '/', dirname($scriptName));
    $baseDir = ($baseDir === '/') ? '' : $baseDir;
    define('APP_URL', $protocol . '://' . $host . $baseDir);
}

// Chemins de l'application
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEWS_PATH', APP_PATH . '/Views');

// Configuration de la base de données
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'sbn_dev');
define('DB_USER', getenv('DB_USER') ?: 'sbn_dev');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Configuration de sécurité
define('SESSION_LIFETIME', (int)(getenv('SESSION_LIFETIME') ?: 120)); // minutes
define('CSRF_TOKEN_NAME', getenv('CSRF_TOKEN_NAME') ?: 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 15); // minutes

// Configuration Email
define('MAIL_HOST', getenv('MAIL_HOST') ?: 'smtp.example.com');
define('MAIL_PORT', getenv('MAIL_PORT') ?: 587);
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: '');
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: '');
define('MAIL_FROM', getenv('MAIL_FROM') ?: 'noreply@sbn.soon22.fr');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'SBN Notifications');

// Configuration API
define('API_RATE_LIMIT', (int)(getenv('API_RATE_LIMIT') ?: 100));

// Configuration des logs
define('LOG_PATH', STORAGE_PATH . '/logs');
define('LOG_LEVEL', APP_DEBUG ? 'debug' : 'info');

// Timezone
date_default_timezone_set('Europe/Paris');

// Configuration PHP
ini_set('display_errors', APP_DEBUG ? 1 : 0);
ini_set('log_errors', 1);
ini_set('error_log', LOG_PATH . '/php_errors.log');

// Configuration de session sécurisée
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME * 60);

// En production, activer le cookie secure (HTTPS)
if (APP_ENV === 'production') {
    ini_set('session.cookie_secure', 1);
}

// Charger les helpers
require_once ROOT_PATH . '/config/helpers.php';

// Charger l'autoloader
require_once ROOT_PATH . '/config/autoload.php';
