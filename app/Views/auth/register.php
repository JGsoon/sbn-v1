<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - SBN v1.0</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/main.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card auth-card-wide">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-server"></i>
                </div>
                <h1>Créer un compte</h1>
                <p>Commencez à surveiller vos sauvegardes</p>
            </div>

            <?php if (isset($_SESSION['flash'])): ?>
                <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                    <div class="alert alert-<?= $type ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                    <?php unset($_SESSION['flash'][$type]); ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <form method="POST" action="<?= APP_URL ?>/register" class="auth-form">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf_token ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-user"></i> Nom complet *
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($name ?? '') ?>"
                            placeholder="Jean Dupont"
                            required
                            autofocus
                        >
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback">
                                <?= implode('<br>', $errors['name']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="company_name">
                            <i class="fas fa-building"></i> Nom de la société *
                        </label>
                        <input
                            type="text"
                            id="company_name"
                            name="company_name"
                            class="form-control <?= isset($errors['company_name']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($company_name ?? '') ?>"
                            placeholder="Ma Société SARL"
                            required
                        >
                        <?php if (isset($errors['company_name'])): ?>
                            <div class="invalid-feedback">
                                <?= implode('<br>', $errors['company_name']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Adresse email *
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                        value="<?= htmlspecialchars($email ?? '') ?>"
                        placeholder="votre@email.com"
                        required
                    >
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback">
                            <?= implode('<br>', $errors['email']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Mot de passe *
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
                            <i class="fas fa-lock"></i> Confirmer le mot de passe *
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
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input
                            type="checkbox"
                            name="gdpr_consent"
                            id="gdpr_consent"
                            required
                            <?= isset($gdpr_consent) && $gdpr_consent ? 'checked' : '' ?>
                        >
                        <span>
                            J'accepte la <a href="<?= APP_URL ?>/privacy" target="_blank">politique de confidentialité</a>
                            et les <a href="<?= APP_URL ?>/terms" target="_blank">conditions d'utilisation</a> *
                        </span>
                    </label>
                    <?php if (isset($errors['gdpr_consent'])): ?>
                        <div class="invalid-feedback">
                            <?= implode('<br>', $errors['gdpr_consent']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Créer mon compte
                </button>
            </form>

            <div class="auth-footer">
                <p>Vous avez déjà un compte ?</p>
                <a href="<?= APP_URL ?>/login" class="btn btn-secondary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </a>
            </div>
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
