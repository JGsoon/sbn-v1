<?php
/**
 * SBN v1.0 - Synology Backup Notifier
 * Point d'entrée principal de l'application
 *
 * @package SBN
 * @version 1.0.0
 * @author Johnny Girault
 * @license MIT
 */

// Charger la configuration (AVANT session_start)
require_once __DIR__ . '/config/config.php';

// Démarrer la session (APRÈS configuration)
session_start();

// Importer le routeur
use App\Core\Router;

try {
    // Obtenir l'URL demandée
    $url = $_GET['url'] ?? '';

    // Créer et dispatcher le routeur
    $router = new Router();
    $router->dispatch($url);

} catch (Exception $e) {
    // Gestion des erreurs
    if (APP_DEBUG) {
        echo '<h1>Erreur</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        // En production, logger l'erreur et afficher une page d'erreur générique
        error_log('[SBN Error] ' . $e->getMessage());
        http_response_code(500);
        echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur - SBN v1.0</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: #f5f6fa;
            text-align: center;
            padding: 20px;
        }
        .error-container {
            max-width: 600px;
        }
        h1 {
            font-size: 72px;
            margin: 0;
            color: #e74c3c;
        }
        h2 {
            font-size: 24px;
            margin: 20px 0;
        }
        p {
            color: #666;
            line-height: 1.6;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Oups!</h1>
        <h2>Une erreur s\'est produite</h2>
        <p>Nous sommes désolés, mais quelque chose s\'est mal passé. Notre équipe technique a été notifiée et travaille à résoudre le problème.</p>
        <a href="' . APP_URL . '">Retour à l\'accueil</a>
    </div>
</body>
</html>';
    }
    exit;
}
?>