<?php
/**
 * Script de vérification avant déploiement en production
 * Exécutez ce script en local avant d'uploader sur O2switch
 *
 * Usage: php check-production-ready.php
 */

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║   SBN v1.0 - Vérification avant déploiement en production    ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$errors = [];
$warnings = [];
$success = [];

// Vérification 1 : Fichier .env.production existe
echo "🔍 Vérification du fichier .env.production...\n";
if (file_exists(__DIR__ . '/.env.production')) {
    $success[] = "✅ Fichier .env.production présent";
} else {
    $errors[] = "❌ Fichier .env.production manquant";
}

// Vérification 2 : Fichier .htaccess configuré
echo "🔍 Vérification du fichier .htaccess...\n";
if (file_exists(__DIR__ . '/.htaccess')) {
    $htaccess = file_get_contents(__DIR__ . '/.htaccess');

    if (strpos($htaccess, 'RewriteEngine On') !== false) {
        $success[] = "✅ .htaccess configuré avec mod_rewrite";
    } else {
        $errors[] = "❌ .htaccess : mod_rewrite non activé";
    }

    // Vérifier qu'il n'y a pas de RewriteBase hardcodé
    if (preg_match('/^\s*RewriteBase\s+\/sbn-v1\//m', $htaccess)) {
        $warnings[] = "⚠️  .htaccess : RewriteBase hardcodé détecté, peut causer des problèmes sur O2switch";
    }
} else {
    $errors[] = "❌ Fichier .htaccess manquant";
}

// Vérification 3 : Structure des dossiers
echo "🔍 Vérification de la structure des dossiers...\n";
$requiredDirs = [
    'app/Controllers',
    'app/Models',
    'app/Views',
    'app/Core',
    'config',
    'database',
    'public/css',
    'public/js',
    'storage/logs',
    'storage/cache'
];

foreach ($requiredDirs as $dir) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        $success[] = "✅ Dossier $dir présent";
    } else {
        $errors[] = "❌ Dossier $dir manquant";
    }
}

// Vérification 4 : Fichiers critiques
echo "🔍 Vérification des fichiers critiques...\n";
$requiredFiles = [
    'index.php',
    'config/config.php',
    'config/database.php',
    'config/routes.php',
    'config/helpers.php',
    'config/autoload.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $success[] = "✅ Fichier $file présent";
    } else {
        $errors[] = "❌ Fichier $file manquant";
    }
}

// Vérification 5 : Fichiers SQL de migration
echo "🔍 Vérification des fichiers SQL...\n";
$sqlFiles = [
    'database/schema.sql',
    'database/add_phone_column.sql',
    'database/add_smtp_config.sql',
    'database/add_shared_access.sql',
    'database/fix_api_tokens.sql',
    'database/add_roles_subscription_sharing.sql'
];

foreach ($sqlFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $success[] = "✅ SQL: $file présent";
    } else {
        $warnings[] = "⚠️  SQL: $file manquant (peut causer des problèmes)";
    }
}

// Vérification 6 : Configuration sécurité
echo "🔍 Vérification de la sécurité...\n";
$configFile = __DIR__ . '/config/config.php';
if (file_exists($configFile)) {
    $configContent = file_get_contents($configFile);

    if (strpos($configContent, "define('APP_DEBUG'") !== false) {
        $success[] = "✅ APP_DEBUG configuré";
    }

    if (strpos($configContent, "define('CSRF_TOKEN_NAME'") !== false) {
        $success[] = "✅ Protection CSRF configurée";
    }
}

// Vérification 7 : Fichiers sensibles protégés
echo "🔍 Vérification de la protection des fichiers sensibles...\n";
$protectedDirs = ['config', 'storage', 'database', 'app'];
foreach ($protectedDirs as $dir) {
    $htaccessPath = __DIR__ . '/' . $dir . '/.htaccess';
    if (file_exists($htaccessPath)) {
        $success[] = "✅ Protection $dir/.htaccess présente";
    } else {
        $warnings[] = "⚠️  Protection $dir/.htaccess manquante";
    }
}

// Vérification 8 : Fichier .env n'est PAS dans le dossier (ne doit pas être uploadé)
echo "🔍 Vérification de l'absence de .env (fichier local uniquement)...\n";
if (file_exists(__DIR__ . '/.env')) {
    $warnings[] = "⚠️  Fichier .env présent - NE PAS uploader sur O2switch (utiliser .env.production)";
} else {
    $success[] = "✅ Pas de fichier .env local (correct)";
}

// Vérification 9 : Helpers et fonctions
echo "🔍 Vérification des helpers...\n";
if (file_exists(__DIR__ . '/config/helpers.php')) {
    $helpers = file_get_contents(__DIR__ . '/config/helpers.php');
    $requiredHelpers = ['url', 'asset', 'redirect', 'csrf_token'];

    foreach ($requiredHelpers as $helper) {
        if (strpos($helpers, "function $helper(") !== false) {
            $success[] = "✅ Helper $helper() présent";
        } else {
            $errors[] = "❌ Helper $helper() manquant";
        }
    }
}

// Vérification 10 : Guide de déploiement
echo "🔍 Vérification de la documentation...\n";
if (file_exists(__DIR__ . '/DEPLOIEMENT_O2SWITCH.md')) {
    $success[] = "✅ Guide de déploiement présent";
} else {
    $warnings[] = "⚠️  Guide de déploiement manquant";
}

// Affichage des résultats
echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                      RÉSULTATS                                ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

if (!empty($success)) {
    echo "✅ SUCCÈS (" . count($success) . "):\n";
    foreach ($success as $msg) {
        echo "   $msg\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  AVERTISSEMENTS (" . count($warnings) . "):\n";
    foreach ($warnings as $msg) {
        echo "   $msg\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "❌ ERREURS (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "   $msg\n";
    }
    echo "\n";
}

// Verdict final
echo "╔═══════════════════════════════════════════════════════════════╗\n";
if (empty($errors)) {
    if (empty($warnings)) {
        echo "║  ✅ PRÊT POUR LE DÉPLOIEMENT                                  ║\n";
        echo "║                                                               ║\n";
        echo "║  Vous pouvez uploader l'application sur O2switch.            ║\n";
        echo "║  Consultez DEPLOIEMENT_O2SWITCH.md pour les instructions.    ║\n";
    } else {
        echo "║  ⚠️  PRÊT AVEC AVERTISSEMENTS                                 ║\n";
        echo "║                                                               ║\n";
        echo "║  Vous pouvez déployer, mais corrigez les avertissements.     ║\n";
    }
} else {
    echo "║  ❌ PAS PRÊT POUR LE DÉPLOIEMENT                              ║\n";
    echo "║                                                               ║\n";
    echo "║  Corrigez les erreurs avant d'uploader sur O2switch.          ║\n";
}
echo "╚═══════════════════════════════════════════════════════════════╝\n";

// Code de sortie
exit(empty($errors) ? 0 : 1);
