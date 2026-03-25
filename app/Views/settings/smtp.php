<div class="page-header">
    <h1><i class="fas fa-envelope-open-text"></i> Configuration Email (SMTP)</h1>
    <p>Configurez l'envoi d'emails pour les notifications</p>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= APP_URL ?>/settings/smtp" id="smtp-form">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <span>Ces paramètres permettent d'envoyer des emails (notifications, invitations, alertes). Utilisez un compte SMTP dédié.</span>
            </div>

            <h3 style="margin: 2rem 0 1rem; font-size: 1.125rem;">
                <i class="fas fa-server"></i> Serveur SMTP
            </h3>

            <div class="form-row">
                <div class="form-group" style="flex: 2;">
                    <label for="smtp_host">
                        <i class="fas fa-server"></i> Serveur SMTP *
                    </label>
                    <input
                        type="text"
                        id="smtp_host"
                        name="smtp_host"
                        class="form-control <?= isset($_SESSION['errors']['smtp_host']) ? 'is-invalid' : '' ?>"
                        placeholder="smtp.gmail.com"
                        required
                        value="<?= htmlspecialchars($_SESSION['old']['smtp_host'] ?? $config['smtp_host'] ?? 'smtp.gmail.com') ?>"
                    >
                    <?php if (isset($_SESSION['errors']['smtp_host'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['smtp_host'] ?></div>
                    <?php endif; ?>
                    <small class="text-muted">
                        Gmail: smtp.gmail.com | Outlook: smtp-mail.outlook.com | OVH: ssl0.ovh.net
                    </small>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label for="smtp_port">
                        <i class="fas fa-plug"></i> Port *
                    </label>
                    <input
                        type="number"
                        id="smtp_port"
                        name="smtp_port"
                        class="form-control <?= isset($_SESSION['errors']['smtp_port']) ? 'is-invalid' : '' ?>"
                        placeholder="587"
                        required
                        value="<?= htmlspecialchars($_SESSION['old']['smtp_port'] ?? $config['smtp_port'] ?? '587') ?>"
                    >
                    <?php if (isset($_SESSION['errors']['smtp_port'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['smtp_port'] ?></div>
                    <?php endif; ?>
                    <small class="text-muted">TLS: 587 | SSL: 465</small>
                </div>
            </div>

            <div class="form-group">
                <label for="smtp_encryption">
                    <i class="fas fa-lock"></i> Chiffrement *
                </label>
                <select
                    id="smtp_encryption"
                    name="smtp_encryption"
                    class="form-control"
                    required
                >
                    <option value="tls" <?= ($config['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (recommandé)</option>
                    <option value="ssl" <?= ($config['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                    <option value="none" <?= ($config['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>Aucun (non sécurisé)</option>
                </select>
            </div>

            <h3 style="margin: 2rem 0 1rem; font-size: 1.125rem;">
                <i class="fas fa-user-lock"></i> Authentification
            </h3>

            <div class="form-group">
                <label for="smtp_username">
                    <i class="fas fa-user"></i> Nom d'utilisateur / Email *
                </label>
                <input
                    type="text"
                    id="smtp_username"
                    name="smtp_username"
                    class="form-control <?= isset($_SESSION['errors']['smtp_username']) ? 'is-invalid' : '' ?>"
                    placeholder="votre-email@gmail.com"
                    required
                    value="<?= htmlspecialchars($_SESSION['old']['smtp_username'] ?? $config['smtp_username'] ?? '') ?>"
                >
                <?php if (isset($_SESSION['errors']['smtp_username'])): ?>
                    <div class="invalid-feedback"><?= $_SESSION['errors']['smtp_username'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="smtp_password">
                    <i class="fas fa-key"></i> Mot de passe <?= $config ? '' : '*' ?>
                </label>
                <input
                    type="password"
                    id="smtp_password"
                    name="smtp_password"
                    class="form-control"
                    placeholder="<?= $config ? 'Laissez vide pour ne pas modifier' : 'Mot de passe SMTP' ?>"
                    <?= $config ? '' : 'required' ?>
                >
                <small class="text-muted">
                    <strong>Gmail:</strong> Utilisez un "mot de passe d'application" (2FA requis)
                    <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: var(--primary-from);">
                        → Générer
                    </a>
                </small>
            </div>

            <h3 style="margin: 2rem 0 1rem; font-size: 1.125rem;">
                <i class="fas fa-paper-plane"></i> Expéditeur
            </h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="smtp_from_email">
                        <i class="fas fa-envelope"></i> Email expéditeur *
                    </label>
                    <input
                        type="email"
                        id="smtp_from_email"
                        name="smtp_from_email"
                        class="form-control <?= isset($_SESSION['errors']['smtp_from_email']) ? 'is-invalid' : '' ?>"
                        placeholder="noreply@votre-domaine.fr"
                        required
                        value="<?= htmlspecialchars($_SESSION['old']['smtp_from_email'] ?? $config['smtp_from_email'] ?? '') ?>"
                    >
                    <?php if (isset($_SESSION['errors']['smtp_from_email'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['smtp_from_email'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="smtp_from_name">
                        <i class="fas fa-signature"></i> Nom expéditeur *
                    </label>
                    <input
                        type="text"
                        id="smtp_from_name"
                        name="smtp_from_name"
                        class="form-control"
                        placeholder="SBN Notifications"
                        required
                        value="<?= htmlspecialchars($_SESSION['old']['smtp_from_name'] ?? $config['smtp_from_name'] ?? 'SBN Notifications') ?>"
                    >
                </div>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <button type="button" class="btn btn-secondary" onclick="testSmtp()">
                    <i class="fas fa-flask"></i> Tester la configuration
                </button>
                <a href="<?= APP_URL ?>/settings" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php unset($_SESSION['errors'], $_SESSION['old']); ?>

<div id="test-result" style="margin-top: 1rem; display: none;"></div>

<script>
function testSmtp() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Test en cours...';

    const resultDiv = document.getElementById('test-result');
    resultDiv.style.display = 'none';

    fetch('<?= APP_URL ?>/settings/smtp/test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        resultDiv.style.display = 'block';
        if (data.success) {
            resultDiv.className = 'alert alert-success';
            resultDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
        } else {
            resultDiv.className = 'alert alert-error';
            resultDiv.innerHTML = '<i class="fas fa-times-circle"></i> ' + data.message;
        }
    })
    .catch(error => {
        resultDiv.style.display = 'block';
        resultDiv.className = 'alert alert-error';
        resultDiv.innerHTML = '<i class="fas fa-times-circle"></i> Erreur: ' + error;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>

<style>
.page-header {
    margin-bottom: var(--spacing-2xl);
}

.page-header h1 {
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

.page-header p {
    color: var(--text-secondary);
    font-size: 1.125rem;
}
</style>
