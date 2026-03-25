<div class="settings-container">
    <div class="settings-header">
        <h1><i class="fas fa-bell"></i> Notifications</h1>
        <p>Configurez vos préférences de notifications</p>
    </div>

    <div class="settings-content">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-cog"></i> Paramètres des notifications</h2>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Cette fonctionnalité est en cours de développement. Bientôt vous pourrez configurer vos préférences de notifications.
                </div>

                <div class="notification-option">
                    <div class="notification-info">
                        <h4><i class="fas fa-check-circle"></i> Sauvegardes réussies</h4>
                        <p>Recevoir une notification lors d'une sauvegarde réussie</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" disabled>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="notification-option">
                    <div class="notification-info">
                        <h4><i class="fas fa-exclamation-triangle"></i> Sauvegardes échouées</h4>
                        <p>Recevoir une notification lors d'une sauvegarde échouée</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" checked disabled>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="notification-option">
                    <div class="notification-info">
                        <h4><i class="fas fa-envelope"></i> Notifications par email</h4>
                        <p>Recevoir les notifications importantes par email</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" checked disabled>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-actions mt-20">
            <a href="<?= APP_URL ?>/dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour au dashboard
            </a>
        </div>
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

.notification-option {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-color);
}

.notification-option:last-child {
    border-bottom: none;
}

.notification-info h4 {
    font-size: 1rem;
    font-weight: var(--font-weight-semibold);
    margin-bottom: var(--spacing-xs);
    color: var(--text-primary);
}

.notification-info p {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin: 0;
}

/* Switch Toggle */
.switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: not-allowed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--gray-300);
    transition: 0.4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}

input:checked + .slider {
    background: var(--primary-gradient);
}

input:checked + .slider:before {
    transform: translateX(24px);
}

.form-actions {
    display: flex;
    gap: var(--spacing-md);
}

@media (max-width: 768px) {
    .notification-option {
        flex-direction: column;
        gap: var(--spacing-md);
        align-items: flex-start;
    }
}
</style>
