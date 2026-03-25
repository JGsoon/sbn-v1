<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SBN v1.0</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/main.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-server"></i>
                </div>
                <h1>SBN v1.0</h1>
                <p>Synology Backup Notifier</p>
            </div>

            <?php if (isset($_SESSION['flash'])): ?>
                <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                    <div class="alert alert-<?= $type ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                    <?php unset($_SESSION['flash'][$type]); ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <form method="POST" action="<?= APP_URL ?>/login" class="auth-form">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf_token ?>">

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Adresse email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                        value="<?= htmlspecialchars($email ?? '') ?>"
                        placeholder="votre@email.com"
                        required
                        autofocus
                    >
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback">
                            <?= implode('<br>', $errors['email']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Mot de passe
                    </label>
                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                            placeholder="••••••••"
                            required
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback">
                            <?= implode('<br>', $errors['password']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group form-remember">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Se souvenir de moi</span>
                    </label>

                    <a href="<?= APP_URL ?>/forgot-password" class="link-forgot">
                        Mot de passe oublié ?
                    </a>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </form>

            <div class="auth-footer">
                <p>Pas encore de compte ?</p>
                <a href="<?= APP_URL ?>/register" class="btn btn-secondary btn-block">
                    <i class="fas fa-user-plus"></i> Créer un compte
                </a>
            </div>
        </div>

        <div class="auth-info">
            <p>&copy; <?= date('Y') ?> SBN v1.0 - Développé par <a href="https://soon22.fr" target="_blank">SoonJohnny Girault22</a></p>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field.nextElementSibling;
            const icon = button.querySelector('i');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
