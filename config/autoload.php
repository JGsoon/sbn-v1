<?php
/**
 * SBN v1.0 - Autoloader PSR-4 (Corrigé)
 *
 * @package SBN
 * @version 1.0.0
 */

spl_autoload_register(function ($class) {
    // Debug en développement
    $debug = defined('APP_DEBUG') && APP_DEBUG;
    
    if ($debug) {
        error_log("=== AUTOLOADER SBN ===");
        error_log("Tentative de charger: $class");
    }
    
    // Gérer le namespace App\
    if (strpos($class, 'App\\') === 0) {
        // App\Core\Router → Core\Router
        $relativeClass = substr($class, 4);
        
        // Core\Router → Core/Router
        $path = str_replace('\\', '/', $relativeClass);
        
        // Core/Router → /path/to/app/Core/Router.php
        $file = APP_PATH . '/' . $path . '.php';
        
        if ($debug) {
            error_log("Namespace App détecté");
            error_log("Classe relative: $relativeClass");
            error_log("Chemin construit: $file");
            error_log("Fichier existe: " . (file_exists($file) ? 'OUI' : 'NON'));
        }
        
        if (file_exists($file)) {
            require_once $file;
            if ($debug) {
                error_log("✅ Classe chargée avec succès");
            }
            return;
        }
        
        if ($debug) {
            error_log("❌ Fichier non trouvé: $file");
        }
    }
    
    // Gérer le namespace Config\
    elseif (strpos($class, 'Config\\') === 0) {
        // Config\Database → Database
        $relativeClass = substr($class, 7);
        
        // Database → database (MINUSCULES pour convention config/)
        $relativeClass = strtolower($relativeClass);
        
        // database → /path/to/config/database.php
        $file = CONFIG_PATH . '/' . str_replace('\\', '/', $relativeClass) . '.php';
        
        if ($debug) {
            error_log("Namespace Config détecté");
            error_log("Classe relative: $relativeClass");
            error_log("Chemin construit: $file");
            error_log("Fichier existe: " . (file_exists($file) ? 'OUI' : 'NON'));
        }
        
        if (file_exists($file)) {
            require_once $file;
            if ($debug) {
                error_log("✅ Classe Config chargée avec succès");
            }
            return;
        }
        
        if ($debug) {
            error_log("❌ Fichier Config non trouvé: $file");
        }
    }
    
    if ($debug) {
        error_log("======================");
    }
});
