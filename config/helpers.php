<?php
/**
 * SBN v1.0 - Fonctions Helper
 *
 * @package SBN
 * @version 1.0.0
 */

if (!function_exists('url')) {
    /**
     * Génère une URL complète à partir d'un chemin relatif
     *
     * @param string $path Chemin relatif (ex: 'dashboard', 'users/edit', '/api/backup')
     * @return string URL complète
     */
    function url($path = '') {
        $path = ltrim($path, '/');
        return APP_URL . ($path ? '/' . $path : '');
    }
}

if (!function_exists('asset')) {
    /**
     * Génère une URL vers un asset (CSS, JS, images)
     *
     * @param string $path Chemin vers l'asset (ex: 'css/main.css', 'js/app.js')
     * @return string URL complète vers l'asset
     */
    function asset($path) {
        $path = ltrim($path, '/');
        return APP_URL . '/public/' . $path;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirige vers une URL
     *
     * @param string $path Chemin relatif ou URL complète
     * @param int $code Code de statut HTTP (301, 302, etc.)
     */
    function redirect($path, $code = 302) {
        if (strpos($path, 'http') === 0) {
            header("Location: $path", true, $code);
        } else {
            header("Location: " . url($path), true, $code);
        }
        exit;
    }
}

if (!function_exists('base_path')) {
    /**
     * Retourne le chemin de base de l'application
     *
     * @param string $path Chemin relatif (optionnel)
     * @return string Chemin complet
     */
    function base_path($path = '') {
        return ROOT_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

if (!function_exists('public_path')) {
    /**
     * Retourne le chemin vers le dossier public
     *
     * @param string $path Chemin relatif (optionnel)
     * @return string Chemin complet
     */
    function public_path($path = '') {
        return PUBLIC_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

if (!function_exists('storage_path')) {
    /**
     * Retourne le chemin vers le dossier storage
     *
     * @param string $path Chemin relatif (optionnel)
     * @return string Chemin complet
     */
    function storage_path($path = '') {
        return STORAGE_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

if (!function_exists('old')) {
    /**
     * Récupère une ancienne valeur de formulaire
     *
     * @param string $key Clé du champ
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    function old($key, $default = '') {
        return $_SESSION['old'][$key] ?? $default;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Génère un token CSRF
     *
     * @return string Token CSRF
     */
    function csrf_token() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Génère un champ hidden avec le token CSRF
     *
     * @return string HTML du champ
     */
    function csrf_field() {
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . csrf_token() . '">';
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and Die - Affiche une variable et arrête l'exécution
     *
     * @param mixed ...$vars Variables à afficher
     */
    function dd(...$vars) {
        echo '<pre style="background: #1e1e1e; color: #dcdcdc; padding: 20px; margin: 10px; border-radius: 5px; font-family: monospace;">';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        die(1);
    }
}

if (!function_exists('env')) {
    /**
     * Récupère une variable d'environnement
     *
     * @param string $key Clé de la variable
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }

        // Conversion des booléens
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }

        return $value;
    }
}
