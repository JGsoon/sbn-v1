<?php
/**
 * SBN v1.0 - Vérification de l'installation
 * Ce fichier vérifie que tous les prérequis sont en place
 *
 * À SUPPRIMER EN PRODUCTION !
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Installation - SBN v1.0</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 32px;
            text-align: center;
        }
        .header h1 { font-size: 32px; margin-bottom: 8px; }
        .header p { opacity: 0.9; }
        .content { padding: 32px; }
        .check-section {
            margin-bottom: 32px;
            padding-bottom: 32px;
            border-bottom: 1px solid #eee;
        }
        .check-section:last-child { border-bottom: none; }
        .check-section h2 {
            font-size: 20px;
            margin-bottom: 16px;
            color: #333;
        }
        .check-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .check-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 16px;
            font-weight: bold;
        }
        .check-ok { background: #d4edda; color: #155724; }
        .check-error { background: #f8d7da; color: #721c24; }
        .check-warning { background: #fff3cd; color: #856404; }
        .check-ok .check-icon { background: #28a745; color: white; }
        .check-error .check-icon { background: #dc3545; color: white; }
        .check-warning .check-icon { background: #ffc107; color: white; }
        .check-label { flex: 1; font-weight: 500; }
        .check-value { color: #666; font-size: 14px; }
        .summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-top: 32px;
        }
        .summary.success { background: #d4edda; color: #155724; }
        .summary.error { background: #f8d7da; color: #721c24; }
        .btn {
            display: inline-block;
            margin-top: 16px;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Vérification de l'installation</h1>
            <p>SBN v1.0 - Synology Backup Notifier</p>
        </div>

        <div class="content">
            <?php
            $errors = 0;
            $warnings = 0;
            $success = 0;

            // Vérification PHP
            echo '<div class="check-section">';
            echo '<h2>Configuration PHP</h2>';

            // Version PHP
            $phpVersion = phpversion();
            $phpOk = version_compare($phpVersion, '8.0.0', '>=');
            echo '<div class="check-item ' . ($phpOk ? 'check-ok' : 'check-error') . '">';
            echo '<div class="check-icon">' . ($phpOk ? '✓' : '✗') . '</div>';
            echo '<div class="check-label">Version PHP</div>';
            echo '<div class="check-value">' . $phpVersion . ' ' . ($phpOk ? '(OK)' : '(Minimum 8.0 requis)') . '</div>';
            echo '</div>';
            $phpOk ? $success++ : $errors++;

            // Extensions
            $extensions = ['pdo', 'pdo_mysql', 'openssl', 'mbstring', 'json'];
            foreach ($extensions as $ext) {
                $loaded = extension_loaded($ext);
                echo '<div class="check-item ' . ($loaded ? 'check-ok' : 'check-error') . '">';
                echo '<div class="check-icon">' . ($loaded ? '✓' : '✗') . '</div>';
                echo '<div class="check-label">Extension ' . $ext . '</div>';
                echo '<div class="check-value">' . ($loaded ? 'Chargée' : 'Non disponible') . '</div>';
                echo '</div>';
                $loaded ? $success++ : $errors++;
            }
            echo '</div>';

            // Vérification fichiers
            echo '<div class="check-section">';
            echo '<h2>Fichiers et dossiers</h2>';

            $files = [
                '.env' => 'Configuration environnement',
                'config/config.php' => 'Configuration principale',
                'app/Core/Router.php' => 'Routeur',
                'app/Controllers/AuthController.php' => 'Contrôleur authentification'
            ];

            foreach ($files as $file => $label) {
                $exists = file_exists(__DIR__ . '/' . $file);
                echo '<div class="check-item ' . ($exists ? 'check-ok' : 'check-error') . '">';
                echo '<div class="check-icon">' . ($exists ? '✓' : '✗') . '</div>';
                echo '<div class="check-label">' . $label . '</div>';
                echo '<div class="check-value">' . ($exists ? 'Trouvé' : 'Manquant') . '</div>';
                echo '</div>';
                $exists ? $success++ : $errors++;
            }

            // Permissions
            $dirs = ['storage/logs', 'storage/cache'];
            foreach ($dirs as $dir) {
                $path = __DIR__ . '/' . $dir;
                $writable = is_dir($path) && is_writable($path);
                echo '<div class="check-item ' . ($writable ? 'check-ok' : 'check-warning') . '">';
                echo '<div class="check-icon">' . ($writable ? '✓' : '⚠') . '</div>';
                echo '<div class="check-label">Écriture ' . $dir . '</div>';
                echo '<div class="check-value">' . ($writable ? 'Autorisée' : 'Refusée') . '</div>';
                echo '</div>';
                $writable ? $success++ : $warnings++;
            }
            echo '</div>';

            // Base de données
            echo '<div class="check-section">';
            echo '<h2>Base de données</h2>';

            if (file_exists(__DIR__ . '/.env')) {
                $envContent = file_get_contents(__DIR__ . '/.env');
                preg_match('/DB_HOST=(.+)/', $envContent, $host);
                preg_match('/DB_NAME=(.+)/', $envContent, $dbname);
                preg_match('/DB_USER=(.+)/', $envContent, $user);
                preg_match('/DB_PASS=(.+)/', $envContent, $pass);

                try {
                    $pdo = new PDO(
                        'mysql:host=' . trim($host[1] ?? 'localhost') . ';dbname=' . trim($dbname[1] ?? 'sbn_dev') . ';charset=utf8mb4',
                        trim($user[1] ?? 'root'),
                        trim($pass[1] ?? ''),
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );

                    echo '<div class="check-item check-ok">';
                    echo '<div class="check-icon">✓</div>';
                    echo '<div class="check-label">Connexion MySQL</div>';
                    echo '<div class="check-value">Réussie</div>';
                    echo '</div>';
                    $success++;

                    // Vérifier les tables
                    $tables = ['users', 'companies', 'backups', 'backup_devices'];
                    $stmt = $pdo->query("SHOW TABLES");
                    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    foreach ($tables as $table) {
                        $exists = in_array($table, $existingTables);
                        echo '<div class="check-item ' . ($exists ? 'check-ok' : 'check-error') . '">';
                        echo '<div class="check-icon">' . ($exists ? '✓' : '✗') . '</div>';
                        echo '<div class="check-label">Table ' . $table . '</div>';
                        echo '<div class="check-value">' . ($exists ? 'Existe' : 'Manquante') . '</div>';
                        echo '</div>';
                        $exists ? $success++ : $errors++;
                    }

                } catch (PDOException $e) {
                    echo '<div class="check-item check-error">';
                    echo '<div class="check-icon">✗</div>';
                    echo '<div class="check-label">Connexion MySQL</div>';
                    echo '<div class="check-value">Échec: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    echo '</div>';
                    $errors++;
                }
            } else {
                echo '<div class="check-item check-error">';
                echo '<div class="check-icon">✗</div>';
                echo '<div class="check-label">Fichier .env</div>';
                echo '<div class="check-value">Non trouvé</div>';
                echo '</div>';
                $errors++;
            }
            echo '</div>';

            // Résumé
            $total = $success + $errors + $warnings;
            $allOk = $errors === 0;

            echo '<div class="summary ' . ($allOk ? 'success' : 'error') . '">';
            if ($allOk) {
                echo '<h2>✅ Installation réussie !</h2>';
                echo '<p>Tous les tests sont passés avec succès.</p>';
                echo '<p><strong>' . $success . '</strong> vérifications réussies';
                if ($warnings > 0) echo ', <strong>' . $warnings . '</strong> avertissements';
                echo '</p>';
                echo '<a href="index.php" class="btn">Accéder à l\'application</a>';
            } else {
                echo '<h2>⚠️ Problèmes détectés</h2>';
                echo '<p>Veuillez corriger les erreurs ci-dessus avant de continuer.</p>';
                echo '<p><strong>' . $errors . '</strong> erreurs, <strong>' . $success . '</strong> réussites';
                if ($warnings > 0) echo ', <strong>' . $warnings . '</strong> avertissements';
                echo '</p>';
            }
            echo '</div>';
            ?>

            <div style="margin-top: 32px; padding-top: 32px; border-top: 1px solid #eee; text-align: center; color: #666;">
                <p><strong>⚠️ IMPORTANT :</strong> Supprimez ce fichier (check-install.php) avant la mise en production !</p>
            </div>
        </div>
    </div>
</body>
</html>
