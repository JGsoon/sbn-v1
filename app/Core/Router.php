<?php
/**
 * SBN v1.0 - Routeur
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Core;

class Router {
    private $routes = [];
    private $currentRoute = null;

    /**
     * Constructeur - Charge les routes
     */
    public function __construct() {
        $this->routes = require CONFIG_PATH . '/routes.php';
    }

    /**
     * Dispatcher - Router la requête vers le bon contrôleur
     */
    public function dispatch($url) {
        // Nettoyer l'URL
        $url = $this->parseUrl($url);

        // Chercher la route correspondante
        $route = $this->matchRoute($url);

        if (!$route) {
            $this->handleNotFound();
            return;
        }

        $this->currentRoute = $route;

        // Vérifier l'authentification si nécessaire
        if (isset($route['auth']) && $route['auth'] === true) {
            if (!$this->isAuthenticated()) {
                $this->redirectToLogin();
                return;
            }

            // Vérifier le rôle si spécifié
            if (isset($route['role'])) {
                if (!$this->hasRole($route['role'])) {
                    $this->handleForbidden();
                    return;
                }
            }
        }

        // Charger le contrôleur
        $controllerName = 'App\\Controllers\\' . $route['controller'];
        $action = $route['action'];

        if (!class_exists($controllerName)) {
            $this->handleNotFound();
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $action)) {
            $this->handleNotFound();
            return;
        }

        // Exécuter l'action du contrôleur
        call_user_func_array([$controller, $action], []);
    }

    /**
     * Parser l'URL
     */
    private function parseUrl($url) {
        if (empty($url)) {
            return '';
        }

        // Supprimer les paramètres GET
        $url = parse_url($url, PHP_URL_PATH);

        // Supprimer les slashes
        $url = trim($url, '/');

        return $url;
    }

    /**
     * Trouver la route correspondante
     */
    private function matchRoute($url) {
        // Route exacte
        if (isset($this->routes[$url])) {
            return $this->routes[$url];
        }

        // Route avec paramètres (ex: backups/view?id=123)
        foreach ($this->routes as $pattern => $route) {
            if (strpos($url, $pattern) === 0) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Vérifier si l'utilisateur est authentifié
     */
    private function isAuthenticated() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Vérifier si l'utilisateur a le rôle requis
     */
    private function hasRole($role) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    /**
     * Rediriger vers la page de login
     */
    private function redirectToLogin() {
        $returnUrl = $_SERVER['REQUEST_URI'];
        header('Location: ' . APP_URL . '/login?return=' . urlencode($returnUrl));
        exit;
    }

    /**
     * Gérer les erreurs 404
     */
    private function handleNotFound() {
        http_response_code(404);
        require VIEWS_PATH . '/errors/404.php';
        exit;
    }

    /**
     * Gérer les erreurs 403
     */
    private function handleForbidden() {
        http_response_code(403);
        require VIEWS_PATH . '/errors/403.php';
        exit;
    }
}
