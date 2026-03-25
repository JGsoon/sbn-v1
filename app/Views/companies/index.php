<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1><i class="fas fa-building"></i> Ma Société</h1>
            <p>Informations de votre organisation</p>
        </div>
        <a href="<?= APP_URL ?>/companies/edit" class="btn btn-primary">
            <i class="fas fa-edit"></i> Modifier
        </a>
    </div>
</div>

<?php $company = $companies[0] ?? null; ?>

<?php if ($company): ?>
<div class="card">
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <div>
                <label style="font-size: 0.875rem; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">
                    <i class="fas fa-building"></i> Nom de la société
                </label>
                <p style="font-size: 1.125rem; font-weight: 600; color: var(--text-primary); margin: 0;">
                    <?= htmlspecialchars($company['name']) ?>
                </p>
            </div>

            <div>
                <label style="font-size: 0.875rem; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">
                    <i class="fas fa-map-marker-alt"></i> Adresse
                </label>
                <p style="font-size: 1rem; color: var(--text-primary); margin: 0;">
                    <?= $company['address'] ? htmlspecialchars($company['address']) : '<span class="text-muted">Non renseignée</span>' ?>
                </p>
            </div>

            <div>
                <label style="font-size: 0.875rem; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">
                    <i class="fas fa-phone"></i> Téléphone
                </label>
                <p style="font-size: 1rem; color: var(--text-primary); margin: 0;">
                    <?= $company['phone'] ? htmlspecialchars($company['phone']) : '<span class="text-muted">Non renseigné</span>' ?>
                </p>
            </div>

            <div>
                <label style="font-size: 0.875rem; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <p style="font-size: 1rem; color: var(--text-primary); margin: 0;">
                    <?= $company['email'] ? htmlspecialchars($company['email']) : '<span class="text-muted">Non renseigné</span>' ?>
                </p>
            </div>

            <div>
                <label style="font-size: 0.875rem; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">
                    <i class="fas fa-calendar"></i> Date de création
                </label>
                <p style="font-size: 1rem; color: var(--text-primary); margin: 0;">
                    <?= date('d/m/Y', strtotime($company['created_at'])) ?>
                </p>
            </div>

            <div>
                <label style="font-size: 0.875rem; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">
                    <i class="fas fa-check-circle"></i> Statut
                </label>
                <p style="font-size: 1rem; margin: 0;">
                    <?php if ($company['is_active']): ?>
                        <span class="status-badge status-success">
                            <i class="fas fa-check-circle"></i> Actif
                        </span>
                    <?php else: ?>
                        <span class="status-badge status-danger">
                            <i class="fas fa-times-circle"></i> Inactif
                        </span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <i class="fas fa-building"></i>
            <h3>Aucune société trouvée</h3>
            <p>Contactez votre administrateur</p>
        </div>
    </div>
</div>
<?php endif; ?>

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
