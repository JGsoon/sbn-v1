<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe - SBN v1.0</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/main.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-lock"></i>
                </div>
                <h1>Nouveau mot de passe</h1>
                <p>Choisissez un mot de passe sécurisé</p>
            </div>

            <?php if (isset($_SESSION['flash'])): ?>
                <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                    <div class="alert alert-<?= $type ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                    <?php unset($_SESSION['flash'][$type]); ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <form method="POST" action="<?= APP_URL ?>/reset-password?token=<?= urlencode($token) ?>" class="auth-form">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf_token ?>">

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Nouveau mot de passe
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
                    <small>Minimum <?= PASSWORD_MIN_LENGTH ?> caractères</small>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback">
                            <?= implode('<br>', $errors['password']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password_confirm">
                        <i class="fas fa-lock"></i> Confirmer le mot de passe
                    </label>
                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="password_confirm"
                            name="password_confirm"
                            class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                            placeholder="••••••••"
                            required
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirm')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['password_confirm'])): ?>
                        <div class="invalid-feedback">
                            <?= implode('<br>', $errors['password_confirm']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-check"></i> Réinitialiser le mot de passe
                </button>
            </form>
        </div>

        <div class="auth-info">
            <p>&copy; <?= date('Y') ?> SBN v1.0 - Développé par <a href="https://soon22.fr" target="_blank">Johnny Girault</a></p>
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
