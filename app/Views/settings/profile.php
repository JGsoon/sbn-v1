<div class="settings-container">
    <div class="settings-header">
        <h1><i class="fas fa-user-circle"></i> Mon profil</h1>
        <p>Gérez vos informations personnelles</p>
    </div>

    <div class="settings-content">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-id-card"></i> Informations personnelles</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/settings/profile">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf_token ?>">

                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-user"></i> Nom complet
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($userData['name'] ?? '') ?>"
                            required
                        >
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback">
                                <?= implode('<br>', $errors['name']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Adresse email
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($userData['email'] ?? '') ?>"
                            required
                        >
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback">
                                <?= implode('<br>', $errors['email']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Rôle</label>
                        <input
                            type="text"
                            class="form-control"
                            value="<?= ucfirst($userData['role'] ?? '') ?>"
                            disabled
                        >
                        <small class="text-muted">Le rôle ne peut pas être modifié</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                        <a href="<?= APP_URL ?>/dashboard" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-20">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Informations du compte</h2>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Société</div>
                        <div class="info-value"><?= htmlspecialchars($userData['company_id'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Compte créé le</div>
                        <div class="info-value">
                            <?= isset($userData['created_at']) ? date('d/m/Y', strtotime($userData['created_at'])) : 'N/A' ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Dernière connexion</div>
                        <div class="info-value">
                            <?= isset($userData['last_login_at']) ? date('d/m/Y à H:i', strtotime($userData['last_login_at'])) : 'Jamais' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($userData['role'] !== 'admin'): ?>
        <!-- Informations d'abonnement -->
        <div class="card mt-20">
            <div class="card-header">
                <h2><i class="fas fa-calendar-check"></i> Mon abonnement</h2>
            </div>
            <div class="card-body">
                <?php
                $subStatus = $userData['subscription_status'] ?? 'trial';
                $subStart = $userData['subscription_start'] ?? null;
                $subEnd = $userData['subscription_end'] ?? null;
                $freeDays = $userData['free_days_granted'] ?? 0;
                ?>

                <div class="subscription-status">
                    <div class="status-badge-large <?= $subStatus === 'active' ? 'badge-success' : ($subStatus === 'trial' ? 'badge-warning' : ($subStatus === 'suspended' ? 'badge-danger' : 'badge-error')) ?>">
                        <?php if ($subStatus === 'active'): ?>
                            <i class="fas fa-check-circle"></i>
                            <span>Abonnement actif</span>
                        <?php elseif ($subStatus === 'trial'): ?>
                            <i class="fas fa-clock"></i>
                            <span>Période d'essai</span>
                        <?php elseif ($subStatus === 'suspended'): ?>
                            <i class="fas fa-ban"></i>
                            <span>Compte suspendu</span>
                        <?php else: ?>
                            <i class="fas fa-times-circle"></i>
                            <span>Abonnement expiré</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="info-grid mt-20">
                    <?php if ($subStart): ?>
                    <div class="info-item">
                        <div class="info-label">Date de début</div>
                        <div class="info-value">
                            <?= date('d/m/Y', strtotime($subStart)) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($subEnd): ?>
                    <div class="info-item">
                        <div class="info-label">Date de fin</div>
                        <div class="info-value">
                            <?= date('d/m/Y', strtotime($subEnd)) ?>
                            <?php
                            $daysLeft = (strtotime($subEnd) - time()) / 86400;
                            if ($daysLeft > 0): ?>
                                <small style="display: block; color: var(--success); margin-top: 0.25rem;">
                                    (<?= floor($daysLeft) ?> jours restants)
                                </small>
                            <?php else: ?>
                                <small style="display: block; color: var(--danger); margin-top: 0.25rem;">
                                    (Expiré depuis <?= abs(floor($daysLeft)) ?> jours)
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($freeDays > 0): ?>
                    <div class="info-item">
                        <div class="info-label">Jours offerts</div>
                        <div class="info-value">
                            <i class="fas fa-gift" style="color: var(--warning);"></i>
                            <?= $freeDays ?> jours
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($subStatus === 'expired' || $subStatus === 'suspended'): ?>
                <div class="alert alert-warning mt-20">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php if ($subStatus === 'expired'): ?>
                        Votre abonnement a expiré. Veuillez contacter l'administrateur pour renouveler votre accès.
                    <?php else: ?>
                        Votre compte est actuellement suspendu. Veuillez contacter l'administrateur.
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

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

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-lg);
}

.info-item {
    padding: var(--spacing-md);
    background: var(--gray-50);
    border-radius: var(--radius-lg);
}

.info-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
    font-weight: var(--font-weight-medium);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: var(--spacing-xs);
}

.info-value {
    font-size: 1.125rem;
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}

.subscription-status {
    display: flex;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.status-badge-large {
    padding: 1rem 2rem;
    border-radius: 12px;
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: white;
}

.status-badge-large.badge-success {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.status-badge-large.badge-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.status-badge-large.badge-danger {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.status-badge-large.badge-error {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>
