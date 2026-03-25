<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - SBN v1.0</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/main.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-key"></i>
                </div>
                <h1>Mot de passe oublié</h1>
                <p>Entrez votre email pour réinitialiser</p>
            </div>

            <?php if (isset($_SESSION['flash'])): ?>
                <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                    <div class="alert alert-<?= $type ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                    <?php unset($_SESSION['flash'][$type]); ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <form method="POST" action="<?= APP_URL ?>/forgot-password" class="auth-form">
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

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane"></i> Envoyer le lien de réinitialisation
                </button>
            </form>

            <div class="auth-footer">
                <p>Vous vous souvenez de votre mot de passe ?</p>
                <a href="<?= APP_URL ?>/login" class="btn btn-secondary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </a>
            </div>
        </div>

        <div class="auth-info">
            <p>&copy; <?= date('Y') ?> SBN v1.0 - Développé par <a href="https://soon22.fr" target="_blank">Johnny Girault</a></p>
        </div>
    </div>
</body>
</html>
