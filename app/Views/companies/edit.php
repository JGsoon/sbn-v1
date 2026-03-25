<div class="page-header">
    <h1><i class="fas fa-edit"></i> Modifier ma société</h1>
    <p>Modifier les informations de votre organisation</p>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= APP_URL ?>/companies/edit">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-group">
                <label for="name">
                    <i class="fas fa-building"></i> Nom de la société *
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control <?= isset($_SESSION['errors']['name']) ? 'is-invalid' : '' ?>"
                    placeholder="Ma société"
                    required
                    value="<?= htmlspecialchars($_SESSION['old']['name'] ?? $company['name'] ?? '') ?>"
                >
                <?php if (isset($_SESSION['errors']['name'])): ?>
                    <div class="invalid-feedback">
                        <?= htmlspecialchars($_SESSION['errors']['name']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="address">
                    <i class="fas fa-map-marker-alt"></i> Adresse
                </label>
                <textarea
                    id="address"
                    name="address"
                    class="form-control"
                    rows="3"
                    placeholder="Adresse complète"
                ><?= htmlspecialchars($_SESSION['old']['address'] ?? $company['address'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">
                        <i class="fas fa-phone"></i> Téléphone
                    </label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        class="form-control"
                        placeholder="+33 1 23 45 67 89"
                        value="<?= htmlspecialchars($_SESSION['old']['phone'] ?? $company['phone'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="contact@societe.fr"
                        value="<?= htmlspecialchars($_SESSION['old']['email'] ?? $company['email'] ?? '') ?>"
                    >
                </div>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <a href="<?= APP_URL ?>/companies" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php unset($_SESSION['errors'], $_SESSION['old']); ?>

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
