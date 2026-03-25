<div class="settings-container">
    <div class="settings-header">
        <h1><i class="fas fa-shield-alt"></i> Sécurité</h1>
        <p>Gérez votre mot de passe et la sécurité de votre compte</p>
    </div>

    <div class="settings-content">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-key"></i> Changer le mot de passe</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/settings/security">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf_token ?>">

                    <div class="form-group">
                        <label for="current_password">
                            <i class="fas fa-lock"></i> Mot de passe actuel
                        </label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['current_password'])): ?>
                            <div class="invalid-feedback">
                                <?= implode('<br>', $errors['current_password']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="new_password">
                            <i class="fas fa-lock"></i> Nouveau mot de passe
                        </label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Minimum <?= PASSWORD_MIN_LENGTH ?> caractères</small>
                        <?php if (isset($errors['new_password'])): ?>
                            <div class="invalid-feedback">
                                <?= implode('<br>', $errors['new_password']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Confirmer le nouveau mot de passe
                        </label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback">
                                <?= implode('<br>', $errors['confirm_password']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Changer le mot de passe
                        </button>
                        <a href="<?= APP_URL ?>/dashboard" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.parentElement.querySelector('.password-toggle');
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

<style>
.settings-container {
    max-width: 800px;
    margin: 0 auto;
}

.settings-header {
    margin-bottom: var(--spacing-2xl);
}

.settings-header h1 {
    font-size: 2rem;
    font-weight: var(--font-weight-bold);
    margin-bottom: var(--spacing-sm);
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.settings-header p {
    color: var(--text-secondary);
    font-size: 1.125rem;
}

.form-actions {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-xl);
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }
}
</style>
