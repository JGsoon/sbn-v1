<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1><i class="fas fa-calendar-alt"></i> Gérer l'abonnement</h1>
            <p>Utilisateur: <?= htmlspecialchars($targetUser['name']) ?> (<?= htmlspecialchars($targetUser['email']) ?>)</p>
        </div>
        <a href="<?= APP_URL ?>/users" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-info-circle"></i> Informations actuelles
            </h3>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <strong>Statut :</strong>
                    <?php
                    $subStatus = $targetUser['subscription_status'] ?? 'trial';
                    ?>
                    <?php if ($subStatus === 'active'): ?>
                        <span class="status-badge status-success">
                            <i class="fas fa-check-circle"></i> Actif
                        </span>
                    <?php elseif ($subStatus === 'trial'): ?>
                        <span class="status-badge status-warning">
                            <i class="fas fa-clock"></i> Essai
                        </span>
                    <?php elseif ($subStatus === 'suspended'): ?>
                        <span class="status-badge status-danger">
                            <i class="fas fa-ban"></i> Suspendu
                        </span>
                    <?php else: ?>
                        <span class="status-badge status-error">
                            <i class="fas fa-times-circle"></i> Expiré
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($targetUser['subscription_start'])): ?>
                    <div>
                        <strong>Début :</strong>
                        <?= date('d/m/Y', strtotime($targetUser['subscription_start'])) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($targetUser['subscription_end'])): ?>
                    <div>
                        <strong>Fin :</strong>
                        <?= date('d/m/Y', strtotime($targetUser['subscription_end'])) ?>
                        <?php
                        $daysLeft = (strtotime($targetUser['subscription_end']) - time()) / 86400;
                        if ($daysLeft > 0): ?>
                            <small style="color: var(--text-secondary);">(<?= floor($daysLeft) ?> jours restants)</small>
                        <?php else: ?>
                            <small style="color: var(--danger);">(Expiré depuis <?= abs(floor($daysLeft)) ?> jours)</small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($targetUser['free_days_granted']) && $targetUser['free_days_granted'] > 0): ?>
                    <div>
                        <strong>Jours gratuits offerts :</strong>
                        <?= $targetUser['free_days_granted'] ?> jours
                    </div>
                <?php endif; ?>

                <?php if (!empty($targetUser['subscription_notes'])): ?>
                    <div>
                        <strong>Notes :</strong>
                        <pre style="white-space: pre-wrap; font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.5rem;"><?= htmlspecialchars($targetUser['subscription_notes']) ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
    <!-- Offrir des jours gratuits -->
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-gift"></i> Offrir des jours gratuits
            </h3>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="grant_days">

                <div class="form-group">
                    <label for="days">Nombre de jours *</label>
                    <select id="days" name="days" class="form-control" required>
                        <option value="7">7 jours (1 semaine)</option>
                        <option value="30">30 jours (1 mois)</option>
                        <option value="90">90 jours (3 mois)</option>
                        <option value="180">180 jours (6 mois)</option>
                        <option value="365" selected>365 jours (1 an)</option>
                        <option value="730">730 jours (2 ans)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="reason">Raison *</label>
                    <textarea id="reason" name="reason" class="form-control" rows="3" required placeholder="Ex: Abonnement annuel payé, période d'essai étendue, etc."></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-gift"></i> Offrir ces jours
                </button>
            </form>
        </div>
    </div>

    <!-- Suspendre / Réactiver -->
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-ban"></i> Suspendre / Réactiver
            </h3>

            <?php if ($subStatus === 'suspended'): ?>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    Ce compte est actuellement suspendu. Vous pouvez le réactiver pour permettre à l'utilisateur de se reconnecter.
                </p>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="unsuspend">

                    <button type="submit" class="btn btn-success" onclick="return confirm('Réactiver ce compte ?')">
                        <i class="fas fa-check-circle"></i> Réactiver le compte
                    </button>
                </form>
            <?php else: ?>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    Suspendre un compte empêche l'utilisateur de se connecter, mais conserve toutes ses données. Utile pour les mauvais payeurs.
                </p>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="suspend">

                    <div class="form-group">
                        <label for="suspend_reason">Raison de la suspension *</label>
                        <textarea id="suspend_reason" name="reason" class="form-control" rows="3" required placeholder="Ex: Non-paiement, fraude, etc."></textarea>
                    </div>

                    <button type="submit" class="btn btn-danger" onclick="return confirm('Suspendre ce compte ?')">
                        <i class="fas fa-ban"></i> Suspendre le compte
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

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
