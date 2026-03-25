<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1><i class="fas fa-users"></i> Utilisateurs</h1>
            <p>Gérez les utilisateurs de votre organisation</p>
        </div>
        <a href="<?= APP_URL ?>/users/add" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Nouvel utilisateur
        </a>
    </div>
</div>

<?php if (isset($_SESSION['temp_password'])): ?>
    <div class="alert alert-warning" style="margin-bottom: 2rem;">
        <i class="fas fa-key"></i>
        <strong>Mot de passe temporaire pour <?= htmlspecialchars($_SESSION['temp_password_user']) ?> :</strong>
        <code style="font-size: 1.2rem; padding: 0.5rem 1rem; background: rgba(0,0,0,0.1); border-radius: 4px; margin: 0.5rem 0; display: inline-block;"><?= htmlspecialchars($_SESSION['temp_password']) ?></code>
        <br>
        <small>⚠️ Copiez ce mot de passe maintenant, il ne sera plus affiché. L'utilisateur devra le changer à sa prochaine connexion.</small>
        <button class="alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php unset($_SESSION['temp_password'], $_SESSION['temp_password_user']); ?>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>Aucun utilisateur</h3>
                <p>Commencez par créer votre premier utilisateur</p>
                <a href="<?= APP_URL ?>/users/add" class="btn btn-primary mt-20">
                    <i class="fas fa-plus-circle"></i> Créer un utilisateur
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Abonnement</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.125rem;">
                                            <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong style="color: var(--text-primary);"><?= htmlspecialchars($u['name']) ?></strong>
                                            <?php if ($u['id'] == $_SESSION['user_id']): ?>
                                                <span style="font-size: 0.75rem; color: var(--primary-from); margin-left: 0.5rem;">
                                                    (Vous)
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($u['email']) ?>" style="color: var(--primary-from); text-decoration: none;">
                                        <?= htmlspecialchars($u['email']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="status-badge status-info">
                                            <i class="fas fa-shield-alt"></i> Admin
                                        </span>
                                    <?php elseif ($u['role'] === 'collaborator'): ?>
                                        <span class="status-badge status-primary">
                                            <i class="fas fa-user-tie"></i> Collaborateur
                                        </span>
                                    <?php elseif ($u['role'] === 'client'): ?>
                                        <span class="status-badge status-muted">
                                            <i class="fas fa-user"></i> Client
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-secondary">
                                            <i class="fas fa-user-circle"></i> Utilisateur
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $subStatus = $u['subscription_status'] ?? 'trial';
                                    $subEnd = $u['subscription_end'] ?? null;
                                    ?>
                                    <?php if ($u['role'] !== 'admin'): ?>
                                        <a href="<?= APP_URL ?>/users/subscription?id=<?= $u['id'] ?>" style="text-decoration: none; display: block;">
                                            <?php if ($subStatus === 'active'): ?>
                                                <span class="status-badge status-success subscription-clickable">
                                                    <i class="fas fa-check-circle"></i> Actif
                                                    <i class="fas fa-edit" style="font-size: 0.75rem; margin-left: 0.25rem;"></i>
                                                </span>
                                                <?php if ($subEnd): ?>
                                                    <br><small style="color: var(--text-secondary);">Jusqu'au <?= date('d/m/Y', strtotime($subEnd)) ?></small>
                                                <?php endif; ?>
                                            <?php elseif ($subStatus === 'trial'): ?>
                                                <span class="status-badge status-warning subscription-clickable">
                                                    <i class="fas fa-clock"></i> Essai
                                                    <i class="fas fa-edit" style="font-size: 0.75rem; margin-left: 0.25rem;"></i>
                                                </span>
                                                <?php if ($subEnd): ?>
                                                    <br><small style="color: var(--text-secondary);">Fin le <?= date('d/m/Y', strtotime($subEnd)) ?></small>
                                                <?php endif; ?>
                                            <?php elseif ($subStatus === 'suspended'): ?>
                                                <span class="status-badge status-danger subscription-clickable">
                                                    <i class="fas fa-ban"></i> Suspendu
                                                    <i class="fas fa-edit" style="font-size: 0.75rem; margin-left: 0.25rem;"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge status-error subscription-clickable">
                                                    <i class="fas fa-times-circle"></i> Expiré
                                                    <i class="fas fa-edit" style="font-size: 0.75rem; margin-left: 0.25rem;"></i>
                                                </span>
                                            <?php endif; ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="status-badge status-muted">
                                            <i class="fas fa-infinity"></i> Illimité
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['is_active']): ?>
                                        <span class="status-badge status-success">
                                            <i class="fas fa-check-circle"></i>
                                            Actif
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-danger">
                                            <i class="fas fa-times-circle"></i>
                                            Inactif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?= APP_URL ?>/users/edit?id=<?= $u['id'] ?>" class="btn btn-sm btn-secondary" title="Modifier l'utilisateur">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" action="<?= APP_URL ?>/users/resetPassword?id=<?= $u['id'] ?>" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Réinitialiser le mot de passe de <?= htmlspecialchars($u['name']) ?> ?')" title="Réinitialiser le mot de passe">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                            </form>

                                            <form method="POST" action="<?= APP_URL ?>/users/delete?id=<?= $u['id'] ?>" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ? Cette action est irréversible.')" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
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

.subscription-clickable {
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.subscription-clickable:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
</style>
