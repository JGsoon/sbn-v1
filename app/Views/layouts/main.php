<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO -->
    <title><?= $title ?? 'SBN v1.0 - Synology Backup Notifier' ?></title>
    <meta name="description" content="<?= $description ?? 'Plateforme de monitoring de sauvegardes Active Backup pour Synology' ?>">
    <meta name="author" content="Johnny Girault">

    <!-- Sécurité -->
    <meta name="robots" content="noindex, nofollow">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= APP_URL ?>/public/assets/images/favicon.png">

    <!-- CSS Moderne -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/main.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/modern.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/dashboard.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="<?= APP_URL ?>/dashboard">
                    <i class="fas fa-server"></i> SBN v1.0
                </a>
            </div>

            <ul class="navbar-menu">
                <li><a href="<?= APP_URL ?>/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="<?= APP_URL ?>/backups"><i class="fas fa-database"></i> Sauvegardes</a></li>
                <li><a href="<?= APP_URL ?>/companies"><i class="fas fa-building"></i> Sociétés</a></li>
                <li><a href="<?= APP_URL ?>/documentation"><i class="fas fa-book"></i> Documentation</a></li>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <li><a href="<?= APP_URL ?>/users"><i class="fas fa-users"></i> Utilisateurs</a></li>
                <?php endif; ?>
            </ul>

            <div class="navbar-user">
                <div class="dropdown">
                    <button class="dropdown-toggle">
                        <i class="fas fa-user-circle"></i>
                        <?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?= APP_URL ?>/settings/profile"><i class="fas fa-user"></i> Mon profil</a>
                        <a href="<?= APP_URL ?>/settings/security"><i class="fas fa-lock"></i> Sécurité</a>
                        <a href="<?= APP_URL ?>/settings/api"><i class="fas fa-key"></i> Tokens API</a>
                        <a href="<?= APP_URL ?>/settings"><i class="fas fa-cog"></i> Paramètres</a>
                        <hr>
                        <a href="<?= APP_URL ?>/logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Messages Flash -->
    <?php if (isset($_SESSION['flash'])): ?>
        <?php foreach ($_SESSION['flash'] as $type => $message): ?>
            <div class="alert alert-<?= $type ?>" role="alert">
                <?php if ($type === 'success'): ?>
                    <i class="fas fa-check-circle"></i>
                <?php elseif ($type === 'error'): ?>
                    <i class="fas fa-exclamation-circle"></i>
                <?php elseif ($type === 'warning'): ?>
                    <i class="fas fa-exclamation-triangle"></i>
                <?php else: ?>
                    <i class="fas fa-info-circle"></i>
                <?php endif; ?>
                <?= htmlspecialchars($message) ?>
                <button class="alert-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['flash'][$type]); ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Contenu principal -->
    <main class="main-content">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-left">
                <p>&copy; <?= date('Y') ?> SBN v1.0 - Synology Backup Notifier</p>
                <p>Développé par <a href="https://soon22.fr" target="_blank">Johnny Girault</a></p>
            </div>
            <div class="footer-right">
                <a href="<?= APP_URL ?>/privacy">Politique de confidentialité</a>
                <a href="<?= APP_URL ?>/terms">Conditions d'utilisation</a>
                <a href="<?= APP_URL ?>/gdpr/export">Exporter mes données</a>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?= APP_URL ?>/public/js/main.js"></script>
    <?php if (isset($js)): ?>
        <?php foreach ((array)$js as $jsFile): ?>
            <script src="<?= APP_URL ?>/public/js/<?= $jsFile ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
