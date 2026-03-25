<div class="page-header">
    <h1><i class="fas fa-cog"></i> Paramètres</h1>
    <p>Gérez votre compte et vos préférences</p>
</div>

<div class="settings-cards-grid">
    <!-- Mon Profil -->
    <a href="<?= APP_URL ?>/settings/profile" class="settings-card">
        <div class="settings-card-icon" style="background: var(--primary-gradient);">
            <i class="fas fa-user"></i>
        </div>
        <div class="settings-card-content">
            <h3>Mon Profil</h3>
            <p>Modifier vos informations personnelles (nom, email, téléphone)</p>
        </div>
        <div class="settings-card-arrow">
            <i class="fas fa-chevron-right"></i>
        </div>
    </a>

    <!-- Sécurité -->
    <a href="<?= APP_URL ?>/settings/security" class="settings-card">
        <div class="settings-card-icon" style="background: var(--danger-gradient);">
            <i class="fas fa-lock"></i>
        </div>
        <div class="settings-card-content">
            <h3>Sécurité</h3>
            <p>Changer votre mot de passe et gérer les paramètres de sécurité</p>
        </div>
        <div class="settings-card-arrow">
            <i class="fas fa-chevron-right"></i>
        </div>
    </a>

    <!-- Tokens API -->
    <a href="<?= APP_URL ?>/settings/api" class="settings-card">
        <div class="settings-card-icon" style="background: var(--secondary-gradient);">
            <i class="fas fa-key"></i>
        </div>
        <div class="settings-card-content">
            <h3>Tokens API</h3>
            <p>Générer et gérer les tokens d'authentification pour vos NAS</p>
        </div>
        <div class="settings-card-arrow">
            <i class="fas fa-chevron-right"></i>
        </div>
    </a>

    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
    <!-- Configuration Email (SMTP) - Admin uniquement -->
    <a href="<?= APP_URL ?>/settings/smtp" class="settings-card">
        <div class="settings-card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <i class="fas fa-envelope-open-text"></i>
        </div>
        <div class="settings-card-content">
            <h3>Configuration Email</h3>
            <p>Paramètres SMTP pour l'envoi d'emails et notifications</p>
        </div>
        <div class="settings-card-arrow">
            <i class="fas fa-chevron-right"></i>
        </div>
    </a>
    <?php endif; ?>

    <!-- Notifications -->
    <a href="<?= APP_URL ?>/settings/notifications" class="settings-card">
        <div class="settings-card-icon" style="background: var(--warning-gradient);">
            <i class="fas fa-bell"></i>
        </div>
        <div class="settings-card-content">
            <h3>Notifications</h3>
            <p>Configurer les alertes email et les préférences de notification</p>
        </div>
        <div class="settings-card-arrow">
            <i class="fas fa-chevron-right"></i>
        </div>
    </a>
</div>

<style>
.settings-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-xl);
    margin-bottom: var(--spacing-2xl);
}

.settings-card {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
    padding: var(--spacing-xl);
    background: white;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    text-decoration: none;
    transition: all var(--transition-base);
    cursor: pointer;
}

.settings-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
}

.settings-card-icon {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.settings-card-icon i {
    font-size: 1.75rem;
    color: white;
}

.settings-card-content {
    flex: 1;
}

.settings-card-content h3 {
    margin: 0 0 var(--spacing-xs) 0;
    font-size: 1.125rem;
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}

.settings-card-content p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.settings-card-arrow {
    color: var(--text-muted);
    font-size: 1.25rem;
    transition: transform var(--transition-fast);
}

.settings-card:hover .settings-card-arrow {
    transform: translateX(4px);
    color: var(--primary-from);
}

@media (max-width: 768px) {
    .settings-cards-grid {
        grid-template-columns: 1fr;
    }

    .settings-card {
        padding: var(--spacing-lg);
    }

    .settings-card-icon {
        width: 50px;
        height: 50px;
    }

    .settings-card-icon i {
        font-size: 1.5rem;
    }
}
</style>
