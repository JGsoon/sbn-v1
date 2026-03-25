<div class="page-header">
    <h1><i class="fas fa-key"></i> Tokens API</h1>
    <p>Gérez les tokens d'authentification pour les webhooks NAS Synology</p>
</div>

<?php if (isset($_SESSION['new_token'])): ?>
    <!-- Modal Token -->
    <div id="tokenModal" class="token-modal">
        <div class="token-modal-content">
            <div class="token-modal-header">
                <h2>
                    <i class="fas fa-check-circle" style="color: #10b981;"></i>
                    Token créé avec succès!
                </h2>
                <button class="token-modal-close" onclick="closeTokenModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="token-modal-body">
                <div class="token-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>IMPORTANT:</strong> Ce token ne sera plus jamais affiché. Copiez-le maintenant!
                </div>

                <div class="token-display">
                    <label>Votre token API:</label>
                    <div class="token-copy-box">
                        <code id="new-token-value"><?= htmlspecialchars($_SESSION['new_token']['token']) ?></code>
                        <button class="btn btn-primary btn-sm" onclick="copyToken()">
                            <i class="fas fa-copy"></i> Copier
                        </button>
                    </div>
                </div>

                <div class="token-instructions">
                    <h3><i class="fas fa-book"></i> Comment utiliser ce token?</h3>

                    <div class="instruction-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Télécharger le fichier de configuration</h4>
                            <p>Cliquez sur le bouton <strong>"Config"</strong> dans la liste des tokens ci-dessous pour télécharger le fichier <code>config.sh</code></p>
                        </div>
                    </div>

                    <div class="instruction-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Transférer sur votre NAS Synology</h4>
                            <p>Connectez-vous à votre NAS via SSH ou File Station et placez le fichier <code>config.sh</code> dans le même dossier que le script webhook.</p>
                        </div>
                    </div>

                    <div class="instruction-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Le script utilisera automatiquement le token</h4>
                            <p>Le script <code>sbn_webhook.sh</code> lira automatiquement le fichier <code>config.sh</code> pour s'authentifier auprès de SBN.</p>
                        </div>
                    </div>

                    <div class="instruction-step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>Configurer HyperBackup</h4>
                            <p>Dans HyperBackup, configurez les notifications pour exécuter le script après chaque backup réussi ou échoué.</p>
                        </div>
                    </div>
                </div>

                <div class="token-help">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Besoin d'aide?</strong>
                        <p>Consultez la <a href="<?= APP_URL ?>/documentation" style="color: #3b82f6; font-weight: 600; text-decoration: underline;">Documentation complète</a> pour le guide d'installation pas à pas.</p>
                    </div>
                </div>
            </div>

            <div class="token-modal-footer">
                <button class="btn btn-primary btn-lg" onclick="closeTokenModal()">
                    <i class="fas fa-check"></i> J'ai copié le token
                </button>
            </div>
        </div>
    </div>

    <script>
        // Afficher la modal automatiquement
        document.getElementById('tokenModal').style.display = 'flex';
    </script>
    <?php
        // Ne pas supprimer la session ici, elle sera supprimée après fermeture de la modal
        $tokenData = $_SESSION['new_token'];
    ?>
<?php endif; ?>

<div class="settings-grid" style="margin-bottom: var(--spacing-2xl);">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-plus-circle"></i> Créer un nouveau token</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= APP_URL ?>/settings/api/create">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="form-group">
                    <label for="token_name">
                        <i class="fas fa-tag"></i> Nom du token
                    </label>
                    <input
                        type="text"
                        id="token_name"
                        name="token_name"
                        class="form-control <?= isset($_SESSION['errors']['token_name']) ? 'is-invalid' : '' ?>"
                        placeholder="Ex: NAS-PROD-01"
                        required
                        value="<?= htmlspecialchars($_SESSION['old']['token_name'] ?? '') ?>"
                    >
                    <?php if (isset($_SESSION['errors']['token_name'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($_SESSION['errors']['token_name']) ?>
                        </div>
                    <?php endif; ?>
                    <small class="text-muted">
                        Donnez un nom descriptif pour identifier facilement ce token (ex: nom du NAS)
                    </small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-key"></i> Générer le token
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="background: var(--gray-50);">
            <h3 style="margin-bottom: 1rem; color: var(--text-primary);">
                <i class="fas fa-shield-alt"></i> Sécurité des tokens
            </h3>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li style="margin-bottom: 0.75rem; display: flex; gap: 0.75rem;">
                    <i class="fas fa-check-circle" style="color: var(--secondary-from); margin-top: 0.25rem;"></i>
                    <span>Ne partagez <strong>JAMAIS</strong> vos tokens API</span>
                </li>
                <li style="margin-bottom: 0.75rem; display: flex; gap: 0.75rem;">
                    <i class="fas fa-check-circle" style="color: var(--secondary-from); margin-top: 0.25rem;"></i>
                    <span>Chaque token permet d'écrire dans votre base de données</span>
                </li>
                <li style="margin-bottom: 0.75rem; display: flex; gap: 0.75rem;">
                    <i class="fas fa-check-circle" style="color: var(--secondary-from); margin-top: 0.25rem;"></i>
                    <span>Révoquez immédiatement tout token compromis</span>
                </li>
                <li style="margin-bottom: 0.75rem; display: flex; gap: 0.75rem;">
                    <i class="fas fa-check-circle" style="color: var(--secondary-from); margin-top: 0.25rem;"></i>
                    <span>Les données sont isolées par société automatiquement</span>
                </li>
            </ul>

            <div style="margin-top: 1.5rem; padding: 1rem; background: white; border-radius: var(--radius-md); border-left: 3px solid var(--primary-from);">
                <strong style="color: var(--primary-from);">📚 Documentation</strong>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem;">
                    Consultez la <a href="<?= APP_URL ?>/documentation" style="color: var(--primary-from); font-weight: 600; text-decoration: underline;">Documentation</a> pour le guide complet d'installation sur votre NAS.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Tokens actifs</h2>
    </div>
    <div class="card-body">
        <?php if (empty($tokens)): ?>
            <div class="empty-state">
                <i class="fas fa-key"></i>
                <h3>Aucun token API</h3>
                <p>Créez votre premier token pour connecter vos NAS Synology.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Token</th>
                            <th>Statut</th>
                            <th>Dernière utilisation</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tokens as $token): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="fas fa-tag" style="color: var(--primary-from);"></i>
                                        <strong><?= htmlspecialchars($token['name']) ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <code style="font-size: 0.875rem; color: var(--text-muted);">
                                        <?= htmlspecialchars(substr($token['token'], 0, 20)) ?>...
                                    </code>
                                </td>
                                <td>
                                    <?php if ($token['is_active']): ?>
                                        <span class="status-badge status-success">
                                            <i class="fas fa-check-circle"></i>
                                            Actif
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-muted">
                                            <i class="fas fa-ban"></i>
                                            Révoqué
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($token['last_used_at']): ?>
                                        <span class="date-display">
                                            <?= date('d/m/Y H:i', strtotime($token['last_used_at'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Jamais utilisé</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="date-display">
                                        <?= date('d/m/Y H:i', strtotime($token['created_at'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($token['is_active']): ?>
                                            <a href="<?= APP_URL ?>/settings/api/download-spk?token_id=<?= $token['id'] ?>" class="btn btn-sm btn-primary" title="Télécharger le package Synology (.spk) - Installation automatique">
                                                <i class="fas fa-box"></i> Package .SPK
                                            </a>

                                            <a href="<?= APP_URL ?>/settings/api/download?id=<?= $token['id'] ?>" class="btn btn-sm btn-secondary" title="Télécharger config.sh (installation manuelle)">
                                                <i class="fas fa-download"></i> Config
                                            </a>

                                            <form method="POST" action="<?= APP_URL ?>/settings/api/revoke" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <input type="hidden" name="token_id" value="<?= $token['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Voulez-vous vraiment révoquer ce token ?')">
                                                    <i class="fas fa-ban"></i> Révoquer
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="POST" action="<?= APP_URL ?>/settings/api/delete" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <input type="hidden" name="token_id" value="<?= $token['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer ce token ? Cette action est irréversible.')">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </form>
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

<?php
// Nettoyer les erreurs et anciennes valeurs
unset($_SESSION['errors'], $_SESSION['old']);
?>

<style>
.settings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-xl);
}

@media (max-width: 1024px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
}

.btn-warning {
    background: var(--warning-gradient);
    color: white;
}

.btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.btn-danger {
    background: var(--danger-gradient);
    color: white;
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

/* Modal Token */
.token-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    padding: 2rem;
    overflow-y: auto;
}

.token-modal-content {
    background: white;
    border-radius: var(--radius-lg);
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.token-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2rem;
    border-bottom: 1px solid var(--border-color);
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
}

.token-modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.token-modal-close {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1.25rem;
    color: var(--text-muted);
}

.token-modal-close:hover {
    background: var(--gray-100);
    color: var(--text-primary);
    transform: scale(1.1);
}

.token-modal-body {
    padding: 2rem;
}

.token-warning {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    background: rgba(245, 158, 11, 0.15);
    border-left: 4px solid #f59e0b;
    border-radius: var(--radius-md);
    margin-bottom: 2rem;
    color: #92400e;
}

.token-warning i {
    font-size: 1.5rem;
    color: #f59e0b;
}

.token-display {
    margin-bottom: 2rem;
}

.token-display label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.token-copy-box {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: var(--gray-50);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    align-items: center;
}

.token-copy-box code {
    flex: 1;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    word-break: break-all;
    color: var(--primary-from);
    font-weight: 600;
}

.token-instructions {
    margin-top: 2rem;
    padding: 1.5rem;
    background: var(--gray-50);
    border-radius: var(--radius-lg);
}

.token-instructions h3 {
    margin: 0 0 1.5rem 0;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.instruction-step {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.instruction-step:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.step-number {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    background: var(--primary-gradient);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.25rem;
}

.step-content h4 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
    font-size: 1rem;
}

.step-content p {
    margin: 0;
    color: var(--text-muted);
    line-height: 1.6;
}

.step-content code {
    background: white;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    font-size: 0.875rem;
    color: var(--primary-from);
    border: 1px solid var(--border-color);
}

.token-help {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding: 1rem 1.5rem;
    background: rgba(59, 130, 246, 0.1);
    border-left: 4px solid #3b82f6;
    border-radius: var(--radius-md);
}

.token-help i {
    font-size: 1.5rem;
    color: #3b82f6;
    flex-shrink: 0;
}

.token-help strong {
    display: block;
    margin-bottom: 0.25rem;
    color: var(--text-primary);
}

.token-help p {
    margin: 0;
    color: var(--text-muted);
    font-size: 0.9rem;
}

.token-modal-footer {
    padding: 1.5rem 2rem;
    border-top: 1px solid var(--border-color);
    background: var(--gray-50);
    display: flex;
    justify-content: center;
}

.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .token-modal-content {
        margin: 0;
        max-height: 100vh;
        border-radius: 0;
    }

    .instruction-step {
        flex-direction: column;
    }
}
</style>

<script>
function copyToken() {
    const tokenValue = document.getElementById('new-token-value').textContent;
    const copyBtn = event.target.closest('button');
    const originalHTML = copyBtn.innerHTML;

    navigator.clipboard.writeText(tokenValue).then(() => {
        // Feedback visuel
        copyBtn.innerHTML = '<i class="fas fa-check"></i> Copié!';
        copyBtn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';

        // Restaurer après 2 secondes
        setTimeout(() => {
            copyBtn.innerHTML = originalHTML;
            copyBtn.style.background = '';
        }, 2000);
    }).catch(err => {
        console.error('Erreur lors de la copie:', err);
        copyBtn.innerHTML = '<i class="fas fa-times"></i> Erreur';
        copyBtn.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';

        setTimeout(() => {
            copyBtn.innerHTML = originalHTML;
            copyBtn.style.background = '';
        }, 2000);
    });
}

function closeTokenModal() {
    const modal = document.getElementById('tokenModal');

    // Animation de fermeture
    modal.querySelector('.token-modal-content').style.animation = 'modalSlideOut 0.3s ease';

    setTimeout(() => {
        modal.style.display = 'none';

        // Supprimer la session du token via AJAX
        fetch('<?= APP_URL ?>/settings/api/clear-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'csrf_token=<?= $_SESSION['csrf_token'] ?>'
        });
    }, 300);
}

// Animation de sortie
const style = document.createElement('style');
style.textContent = `
    @keyframes modalSlideOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(-50px);
        }
    }
`;
document.head.appendChild(style);

// Empêcher la fermeture en cliquant sur le fond (l'utilisateur DOIT cliquer sur le bouton)
document.getElementById('tokenModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        // Animation de shake pour indiquer qu'il faut cliquer sur le bouton
        const content = this.querySelector('.token-modal-content');
        content.style.animation = 'shake 0.5s ease';
        setTimeout(() => {
            content.style.animation = 'modalSlideIn 0.3s ease';
        }, 500);
    }
});

// Animation shake
const shakeStyle = document.createElement('style');
shakeStyle.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }
`;
document.head.appendChild(shakeStyle);
</script>
