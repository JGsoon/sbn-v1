<?php
/**
 * Fichier de debug pour diagnostiquer les problèmes de connexion
 * À SUPPRIMER après diagnostic !
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Charger la config
require_once __DIR__ . '/config/config.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Debug Login - SBN v1.0</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        h2 { margin-top: 0; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Diagnostic de connexion SBN v1.0</h1>

    <?php
    echo '<div class="section">';
    echo '<h2>1. Configuration</h2>';
    echo '<pre>';
    echo 'DB_HOST: ' . DB_HOST . "\n";
    echo 'DB_NAME: ' . DB_NAME . "\n";
    echo 'DB_USER: ' . DB_USER . "\n";
    echo 'APP_DEBUG: ' . (APP_DEBUG ? 'true' : 'false') . "\n";
    echo '</pre>';
    echo '</div>';

    // Test connexion BDD
    echo '<div class="section">';
    echo '<h2>2. Connexion à la base de données</h2>';
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo '<p class="success">✅ Connexion réussie</p>';
    } catch (PDOException $e) {
        echo '<p class="error">❌ Erreur: ' . htmlspecialchars($e->getMessage()) . '</p>';
        die();
    }
    echo '</div>';

    // Vérifier les tables
    echo '<div class="section">';
    echo '<h2>3. Tables existantes</h2>';
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) > 0) {
        echo '<p class="success">✅ ' . count($tables) . ' tables trouvées</p>';
        echo '<pre>' . implode("\n", $tables) . '</pre>';
    } else {
        echo '<p class="error">❌ Aucune table trouvée ! Vous devez importer database/schema.sql</p>';
        die();
    }
    echo '</div>';

    // Vérifier les utilisateurs
    echo '<div class="section">';
    echo '<h2>4. Utilisateurs dans la base</h2>';
    try {
        $stmt = $pdo->query("SELECT id, email, name, role, is_active, company_id FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($users) > 0) {
            echo '<p class="success">✅ ' . count($users) . ' utilisateur(s) trouvé(s)</p>';
            echo '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
            echo '<tr><th>ID</th><th>Email</th><th>Nom</th><th>Rôle</th><th>Actif</th><th>Société</th></tr>';
            foreach ($users as $user) {
                echo '<tr>';
                echo '<td>' . $user['id'] . '</td>';
                echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                echo '<td>' . htmlspecialchars($user['name']) . '</td>';
                echo '<td>' . $user['role'] . '</td>';
                echo '<td>' . ($user['is_active'] ? '✅' : '❌') . '</td>';
                echo '<td>' . $user['company_id'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p class="error">❌ Aucun utilisateur trouvé ! Le compte test n\'a pas été créé.</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="error">❌ Erreur: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    echo '</div>';

    // Test du hash de mot de passe
    echo '<div class="section">';
    echo '<h2>5. Test du mot de passe</h2>';

    $testEmail = 'admin@soon22.fr';
    $testPassword = 'Admin123!';

    echo '<p>Test avec : <strong>' . htmlspecialchars($testEmail) . '</strong> / <strong>' . htmlspecialchars($testPassword) . '</strong></p>';

    try {
        $stmt = $pdo->prepare("SELECT id, email, password, is_active FROM users WHERE email = ?");
        $stmt->execute([$testEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo '<p class="success">✅ Utilisateur trouvé</p>';
            echo '<pre>';
            echo 'ID: ' . $user['id'] . "\n";
            echo 'Email: ' . htmlspecialchars($user['email']) . "\n";
            echo 'Actif: ' . ($user['is_active'] ? 'Oui' : 'Non') . "\n";
            echo 'Hash en BDD: ' . substr($user['password'], 0, 20) . '...' . "\n";
            echo '</pre>';

            if (!$user['is_active']) {
                echo '<p class="error">❌ Le compte est désactivé !</p>';
            }

            // Tester le mot de passe
            if (password_verify($testPassword, $user['password'])) {
                echo '<p class="success">✅ ✅ ✅ MOT DE PASSE CORRECT ! ✅ ✅ ✅</p>';
                echo '<p>Le mot de passe fonctionne. Le problème vient d\'ailleurs...</p>';
            } else {
                echo '<p class="error">❌ MOT DE PASSE INCORRECT !</p>';
                echo '<p>Le hash en base de données ne correspond pas au mot de passe "Admin123!"</p>';

                // Générer le bon hash
                echo '<h3>Solution : Mettre à jour le mot de passe</h3>';
                $correctHash = password_hash($testPassword, PASSWORD_BCRYPT, ['cost' => 12]);
                echo '<p>Exécutez cette requête SQL dans phpMyAdmin :</p>';
                echo '<pre>';
                echo "UPDATE users \n";
                echo "SET password = '" . $correctHash . "' \n";
                echo "WHERE email = '" . $testEmail . "';";
                echo '</pre>';
            }
        } else {
            echo '<p class="error">❌ Aucun utilisateur avec cet email</p>';
            echo '<h3>Solution : Créer l\'utilisateur</h3>';

            // Vérifier si la société existe
            $stmt = $pdo->query("SELECT id FROM companies LIMIT 1");
            $company = $stmt->fetch();

            if ($company) {
                $companyId = $company['id'];
                echo '<p>Société trouvée (ID: ' . $companyId . ')</p>';
            } else {
                echo '<p class="warning">⚠️ Aucune société trouvée, création en cours...</p>';
                $pdo->exec("INSERT INTO companies (name, is_active, created_at, updated_at) VALUES ('Soon22', 1, NOW(), NOW())");
                $companyId = $pdo->lastInsertId();
                echo '<p class="success">✅ Société créée (ID: ' . $companyId . ')</p>';
            }

            // Créer l'utilisateur
            $hash = password_hash($testPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $sql = "INSERT INTO users (company_id, email, password, name, role, is_active, gdpr_consent, gdpr_consent_date, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $companyId,
                $testEmail,
                $hash,
                'Administrateur',
                'admin',
                1,
                1
            ]);

            echo '<p class="success">✅ ✅ ✅ Utilisateur créé avec succès !</p>';
            echo '<p>Vous pouvez maintenant vous connecter avec :</p>';
            echo '<pre>';
            echo 'Email: ' . $testEmail . "\n";
            echo 'Mot de passe: ' . $testPassword;
            echo '</pre>';
        }
    } catch (PDOException $e) {
        echo '<p class="error">❌ Erreur: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    echo '</div>';

    // Vérifier les tentatives de connexion
    echo '<div class="section">';
    echo '<h2>6. Tentatives de connexion récentes</h2>';
    try {
        $stmt = $pdo->query("SELECT * FROM login_attempts ORDER BY attempted_at DESC LIMIT 5");
        $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($attempts) > 0) {
            echo '<p class="warning">⚠️ ' . count($attempts) . ' tentative(s) enregistrée(s)</p>';
            echo '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
            echo '<tr><th>Email</th><th>IP</th><th>Date</th></tr>';
            foreach ($attempts as $attempt) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($attempt['email']) . '</td>';
                echo '<td>' . $attempt['ip_address'] . '</td>';
                echo '<td>' . $attempt['attempted_at'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';

            // Vérifier le verrouillage
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM login_attempts
                WHERE email = ?
                AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
            ");
            $stmt->execute([$testEmail]);
            $count = $stmt->fetch()['count'];

            if ($count >= 5) {
                echo '<p class="error">❌ COMPTE VERROUILLÉ ! Trop de tentatives échouées.</p>';
                echo '<p><strong>Solution :</strong> Supprimez les tentatives avec cette requête SQL :</p>';
                echo '<pre>DELETE FROM login_attempts WHERE email = \'' . $testEmail . '\';</pre>';
            }
        } else {
            echo '<p class="success">✅ Aucune tentative de connexion enregistrée</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="warning">⚠️ Table login_attempts non accessible</p>';
    }
    echo '</div>';
    ?>

    <div class="section">
        <h2>✅ Actions</h2>
        <p><a href="index.php" style="padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 4px;">Essayer de se connecter</a></p>
        <p style="color: red; margin-top: 20px;"><strong>⚠️ IMPORTANT : Supprimez ce fichier (debug-login.php) après diagnostic !</strong></p>
    </div>
</body>
</html>
