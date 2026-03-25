<?php
/**
 * SBN v1.0 - Configuration Base de données
 *
 * @package SBN
 * @version 1.0.0
 * @author Johnny Girault
 * @license MIT
 */

namespace Config;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;

    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;
    private $charset = 'utf8mb4';

    /**
     * Constructeur privé (Singleton)
     */
    private function __construct() {
        // Charger les variables d'environnement
        $this->loadEnv();

        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->port = getenv('DB_PORT') ?: '3306';
        $this->dbname = getenv('DB_NAME') ?: 'sbn_dev';
        $this->username = getenv('DB_USER') ?: 'sbn_dev';
        $this->password = getenv('DB_PASS') ?: '';

        $this->connect();
    }

    /**
     * Charger les variables d'environnement depuis .env
     */
    private function loadEnv() {
        $envFile = dirname(__DIR__) . '/.env';

        if (!file_exists($envFile)) {
            throw new \Exception("Le fichier .env est introuvable. Veuillez copier .env.exemple vers .env");
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Ignorer les commentaires
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parser la ligne KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Supprimer les guillemets
                $value = trim($value, '"\'');

                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }

    /**
     * Établir la connexion PDO
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset} COLLATE utf8mb4_unicode_ci"
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);

            // Logger la connexion réussie
            $this->logInfo("Connexion à la base de données établie");

        } catch (PDOException $e) {
            // Ne pas exposer les détails de connexion en production
            $this->logError("Erreur de connexion à la base de données: " . $e->getMessage());

            if (getenv('APP_DEBUG') === 'true') {
                throw new \Exception("Erreur de connexion à la base de données: " . $e->getMessage());
            } else {
                throw new \Exception("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
            }
        }
    }

    /**
     * Obtenir l'instance unique (Singleton)
     *
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtenir la connexion PDO
     *
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Empêcher le clonage (Singleton)
     */
    private function __clone() {}

    /**
     * Empêcher la désérialisation (Singleton)
     */
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Logger une information
     */
    private function logInfo($message) {
        $this->log('INFO', $message);
    }

    /**
     * Logger une erreur
     */
    private function logError($message) {
        $this->log('ERROR', $message);
    }

    /**
     * Écrire dans le fichier de log
     */
    private function log($level, $message) {
        $logDir = dirname(__DIR__) . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/database_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
