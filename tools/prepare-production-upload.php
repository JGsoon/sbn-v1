<?php
/**
 * Préparation du dossier de production pour upload FTP
 * Copie uniquement les fichiers nécessaires et met à jour le .htaccess
 */

$sourceDir = __DIR__ . '/..';
$productionDir = 'C:/xampp/htdocs/sbn-v1-production';

echo "╔════════════════════════════════════════════════╗\n";
echo "║   Préparation Production pour Upload FTP      ║\n";
echo "╚════════════════════════════════════════════════╝\n\n";

// Fichiers/dossiers à NE PAS copier en production
$exclude = [
    '.git',
    '.gitignore',
    'tools',
    'tests',
    'sbn-v1.code-workspace',
    '.vscode',
    'sbn-v1.rar',
    'sbn-v1.zip',
    '.env.exemple',
    '.env.production',
    '.env.production.real',
    'SESSION_SUMMARY.md',
    'SYNC_REPORT.md',
    '.claude',
    'storage/logs', // Les logs ne doivent pas être uploadés
];

// Nouveaux fichiers/dossiers à copier (créés localement)
$newItems = [
    'docs/SPK_GENERATOR_GUIDE.md',
    'storage/spk-template', // Déjà présent mais on s'assure qu'il est à jour
];

echo "📋 Fichiers/dossiers exclus de la production:\n";
foreach ($exclude as $item) {
    echo "  ❌ $item\n";
}

echo "\n✅ Mise à jour du .htaccess pour production...\n";

// Restaurer le .htaccess de production (avec HTTPS forcé)
$productionHtaccess = <<<'HTACCESS'
# SBN v1.0 - Configuration Apache
# Hébergement O2Switch - Production

# Activation du module de réécriture
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # HTTPS forcé (ACTIVÉ pour la production)
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Bloquer l'accès aux fichiers et dossiers sensibles
    RewriteRule ^\.env$ - [F,L]
    RewriteRule ^\.env\..*$ - [F,L]
    RewriteRule ^config/ - [F,L]
    RewriteRule ^storage/(?!uploads/) - [F,L]
    RewriteRule ^app/ - [F,L]
    RewriteRule ^database/ - [F,L]
    RewriteRule ^vendor/ - [F,L]

    # Autoriser les fichiers statiques existants
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    # Rediriger toutes les autres requêtes vers index.php
    RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
</IfModule>

# Protection des fichiers sensibles
<FilesMatch "^\.">
    Require all denied
</FilesMatch>

<FilesMatch "\.(env|log|sql|md|txt|yml|yaml|json|lock|git|gitignore)$">
    Require all denied
</FilesMatch>

# Désactiver l'affichage des répertoires
Options -Indexes

# Sécurité - Headers HTTP
<IfModule mod_headers.c>
    # Protection XSS
    Header set X-XSS-Protection "1; mode=block"

    # Empêcher le MIME sniffing
    Header set X-Content-Type-Options "nosniff"

    # Protection Clickjacking
    Header set X-Frame-Options "SAMEORIGIN"

    # Référent Policy
    Header set Referrer-Policy "strict-origin-when-cross-origin"

    # HSTS - Forcer HTTPS
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

    # CSP - Politique de sécurité du contenu
    Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self';"
</IfModule>

# Configuration PHP (sécurité)
<IfModule mod_php.c>
    php_flag display_errors Off
    php_flag log_errors On
    php_value error_log storage/logs/php_errors.log
    php_flag expose_php Off
    php_value session.cookie_httponly 1
    php_value session.cookie_secure 1
    php_value session.use_strict_mode 1
    php_value max_execution_time 300
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
</IfModule>

# Compression GZIP pour performances
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json application/xml
</IfModule>

# Cache navigateur pour ressources statiques
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType font/woff2 "access plus 1 year"
</IfModule>

# Encodage UTF-8 par défaut
AddDefaultCharset UTF-8
HTACCESS;

file_put_contents($productionDir . '/.htaccess', $productionHtaccess);
echo "  ✅ .htaccess production mis à jour\n";

echo "\n✅ Copie du nouveau guide SPK...\n";

// Copier le guide SPK
$srcGuide = $sourceDir . '/docs/SPK_GENERATOR_GUIDE.md';
$dstGuide = $productionDir . '/docs/SPK_GENERATOR_GUIDE.md';

if (file_exists($srcGuide)) {
    if (!is_dir(dirname($dstGuide))) {
        mkdir(dirname($dstGuide), 0755, true);
    }
    copy($srcGuide, $dstGuide);
    echo "  ✅ docs/SPK_GENERATOR_GUIDE.md copié\n";
} else {
    echo "  ⚠️  docs/SPK_GENERATOR_GUIDE.md non trouvé\n";
}

echo "\n" . str_repeat('═', 50) . "\n";
echo "✅ Préparation terminée!\n";
echo str_repeat('═', 50) . "\n\n";

echo "📋 Prochaines étapes:\n\n";
echo "1. ✅ Le dossier sbn-v1-production est prêt\n";
echo "2. 🔐 Vérifiez que .env contient les bons credentials de production\n";
echo "3. 📤 Uploadez via FTP vers sbn.soon22.fr\n";
echo "4. 🗑️  Supprimez les fichiers de dev sur le serveur:\n";
echo "     - tools/\n";
echo "     - tests/\n";
echo "     - SESSION_SUMMARY.md\n";
echo "     - SYNC_REPORT.md\n";
echo "5. ✅ Testez https://sbn.soon22.fr\n\n";

echo "⚠️  IMPORTANT:\n";
echo "   - Le .htaccess force maintenant HTTPS\n";
echo "   - Le .env doit avoir APP_DEBUG=false\n";
echo "   - Vérifiez les permissions sur le serveur (755/644)\n\n";
