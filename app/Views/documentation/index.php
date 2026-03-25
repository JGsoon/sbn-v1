<div class="page-header">
    <h1><i class="fas fa-book"></i> Documentation</h1>
    <p>Guide complet pour installer et configurer SBN sur vos NAS Synology avec Active Backup for Business et HyperBackup</p>
</div>

<!-- Section Téléchargements -->
<div class="card" style="margin-bottom: var(--spacing-2xl);">
    <div class="card-header" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
        <h2><i class="fas fa-download"></i> Téléchargements</h2>
    </div>
    <div class="card-body">
        <p style="margin-bottom: 1.5rem;">Téléchargez les scripts nécessaires pour connecter votre NAS à SBN:</p>

        <div class="download-grid">
            <a href="<?= APP_URL ?>/documentation/download-webhook" class="download-card">
                <div class="download-icon">
                    <i class="fas fa-file-code"></i>
                </div>
                <div class="download-info">
                    <h3>sbn_webhook.sh</h3>
                    <p>Script principal qui envoie les notifications vers SBN</p>
                    <span class="download-badge">Requis</span>
                </div>
            </a>

            <a href="<?= APP_URL ?>/documentation/download-config" class="download-card">
                <div class="download-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="download-info">
                    <h3>config.sh.example</h3>
                    <p>Exemple de configuration (référence uniquement)</p>
                    <span class="download-badge download-badge-optional">Référence</span>
                </div>
            </a>

            <a href="<?= APP_URL ?>/documentation/download-install" class="download-card">
                <div class="download-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="fas fa-magic"></i>
                </div>
                <div class="download-info">
                    <h3>install.sh</h3>
                    <p>Script d'installation automatique</p>
                    <span class="download-badge download-badge-optional">Optionnel</span>
                </div>
            </a>
        </div>

        <div class="alert alert-info" style="margin-top: 1.5rem; position: relative;">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>Important:</strong> Votre fichier <code>config.sh</code> personnalisé se télécharge depuis
                <a href="<?= APP_URL ?>/settings/api" style="color: #3b82f6; text-decoration: underline;">Paramètres → Tokens API</a>
                après avoir créé un token.
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; cursor: pointer; color: inherit; opacity: 0.6; font-size: 1.25rem; padding: 0.25rem 0.5rem; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<!-- Guide d'installation -->
<div class="card" style="margin-bottom: var(--spacing-2xl);">
    <div class="card-header">
        <h2><i class="fas fa-rocket"></i> Guide d'installation rapide</h2>
    </div>
    <div class="card-body">
        <div class="installation-steps">

            <!-- Étape 1 -->
            <div class="install-step">
                <div class="step-header">
                    <div class="step-number">1</div>
                    <h3>Créer un token API</h3>
                </div>
                <div class="step-content">
                    <p>Générez votre token d'authentification unique:</p>
                    <ol>
                        <li>Allez dans <a href="<?= APP_URL ?>/settings/api"><strong>Paramètres → Tokens API</strong></a></li>
                        <li>Cliquez sur <strong>"Générer le token"</strong></li>
                        <li>Donnez-lui un nom (ex: "NAS-PROD-01")</li>
                        <li><strong>Copiez le token</strong> (affiché une seule fois!)</li>
                        <li>Téléchargez le fichier <code>config.sh</code> personnalisé</li>
                    </ol>
                    <div class="step-visual">
                        <div class="code-block">
                            <div class="code-header">
                                <i class="fas fa-key"></i> Exemple de token généré
                            </div>
                            <code>sbn_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6...</code>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Étape 2 -->
            <div class="install-step">
                <div class="step-header">
                    <div class="step-number">2</div>
                    <h3>Télécharger les scripts</h3>
                </div>
                <div class="step-content">
                    <p>Récupérez les fichiers nécessaires:</p>
                    <ul>
                        <li><strong>sbn_webhook.sh</strong> → Script principal (requis)</li>
                        <li><strong>config.sh</strong> → Votre configuration personnalisée (depuis Tokens API)</li>
                        <li><strong>install.sh</strong> → Script d'installation automatique (optionnel)</li>
                    </ul>
                </div>
            </div>

            <!-- Étape 3 -->
            <div class="install-step">
                <div class="step-header">
                    <div class="step-number">3</div>
                    <h3>Transférer sur votre NAS</h3>
                </div>
                <div class="step-content">
                    <p>Deux méthodes possibles:</p>

                    <div class="method-tabs">
                        <div class="method-tab">
                            <h4><i class="fas fa-terminal"></i> Méthode A: Via SSH (recommandé)</h4>
                            <div class="code-block">
                                <div class="code-header">
                                    <i class="fas fa-terminal"></i> Commandes
                                </div>
                                <pre><code># Connexion SSH
ssh admin@<span class="code-var">IP_DE_VOTRE_NAS</span>

# Créer le dossier
sudo mkdir -p /volume1/scripts/sbn
cd /volume1/scripts/sbn

# Transférer les fichiers (depuis votre PC)
scp sbn_webhook.sh config.sh install.sh admin@<span class="code-var">IP_NAS</span>:/volume1/scripts/sbn/

# Rendre exécutables
sudo chmod +x sbn_webhook.sh install.sh
sudo chmod 600 config.sh</code></pre>
                            </div>
                        </div>

                        <div class="method-tab">
                            <h4><i class="fas fa-folder"></i> Méthode B: Via File Station</h4>
                            <ol>
                                <li>Ouvrez <strong>File Station</strong> sur votre NAS</li>
                                <li>Créez le dossier: <code>/volume1/scripts/sbn</code></li>
                                <li>Uploadez tous les fichiers dans ce dossier</li>
                                <li>Via SSH, rendez les scripts exécutables:
                                    <div class="code-block" style="margin-top: 0.5rem;">
                                        <code>sudo chmod +x /volume1/scripts/sbn/*.sh</code>
                                    </div>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Étape 4 -->
            <div class="install-step">
                <div class="step-header">
                    <div class="step-number">4</div>
                    <h3>Configurer votre solution de sauvegarde</h3>
                </div>
                <div class="step-content">
                    <p>Configurez votre solution de sauvegarde pour exécuter automatiquement le script:</p>

                    <h4 style="margin-top: 1rem;"><i class="fas fa-hdd"></i> Active Backup for Business (Principal)</h4>
                    <ol>
                        <li>Ouvrez <strong>Active Backup for Business</strong> sur votre NAS</li>
                        <li>Sélectionnez votre tâche de sauvegarde</li>
                        <li>Allez dans <strong>Paramètres</strong> → <strong>Notifications</strong></li>
                        <li>Activez <strong>"Exécuter un script personnalisé"</strong></li>
                        <li>Entrez le chemin du script:
                            <div class="code-block" style="margin-top: 0.5rem;">
                                <code>/volume1/scripts/sbn/sbn_webhook.sh</code>
                            </div>
                        </li>
                        <li>Configurez les événements (succès, échec, avertissement)</li>
                        <li>Cliquez sur <strong>Appliquer</strong></li>
                    </ol>

                    <h4 style="margin-top: 1.5rem;"><i class="fas fa-cloud"></i> HyperBackup (Compatible)</h4>
                    <ol>
                        <li>Ouvrez <strong>HyperBackup</strong> sur votre NAS</li>
                        <li>Sélectionnez votre tâche de sauvegarde</li>
                        <li>Cliquez sur <strong>Paramètres</strong> (roue dentée)</li>
                        <li>Allez dans l'onglet <strong>Notifications</strong></li>
                        <li>Activez <strong>"Exécuter un script personnalisé après la tâche"</strong></li>
                        <li>Entrez le même chemin:
                            <div class="code-block" style="margin-top: 0.5rem;">
                                <code>/volume1/scripts/sbn/sbn_webhook.sh</code>
                            </div>
                        </li>
                        <li>Cliquez sur <strong>OK</strong></li>
                    </ol>

                    <div class="alert alert-success" style="margin-top: 1rem; position: relative;">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>C'est fait!</strong> Votre NAS enverra désormais automatiquement les statuts de sauvegarde vers SBN.
                        </div>
                        <button class="alert-close" onclick="this.parentElement.remove()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; cursor: pointer; color: inherit; opacity: 0.6; font-size: 1.25rem; padding: 0.25rem 0.5rem; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Étape 5 -->
            <div class="install-step">
                <div class="step-header">
                    <div class="step-number">5</div>
                    <h3>Tester l'installation</h3>
                </div>
                <div class="step-content">
                    <p>Vérifiez que tout fonctionne correctement:</p>

                    <h4>Test manuel via SSH:</h4>
                    <div class="code-block">
                        <div class="code-header">
                            <i class="fas fa-terminal"></i> Commandes de test
                        </div>
                        <pre><code># Test avec sauvegarde réussie
sudo /volume1/scripts/sbn/sbn_webhook.sh success

# Test avec sauvegarde échouée
sudo /volume1/scripts/sbn/sbn_webhook.sh failed

# Voir les logs
tail -f /volume1/scripts/sbn/webhook.log</code></pre>
                    </div>

                    <h4 style="margin-top: 1.5rem;">Vérification dans SBN:</h4>
                    <p>Allez dans <a href="<?= APP_URL ?>/dashboard"><strong>Dashboard</strong></a> pour voir apparaître vos notifications de test.</p>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Troubleshooting -->
<div class="card" style="margin-bottom: var(--spacing-2xl);">
    <div class="card-header">
        <h2><i class="fas fa-question-circle"></i> Dépannage</h2>
    </div>
    <div class="card-body">
        <div class="troubleshooting-grid">

            <div class="trouble-item">
                <h4><i class="fas fa-times-circle"></i> Le webhook ne fonctionne pas</h4>
                <div class="trouble-solution">
                    <p><strong>Solutions:</strong></p>
                    <ul>
                        <li>Vérifiez que le fichier <code>config.sh</code> existe et contient votre token</li>
                        <li>Vérifiez les permissions: <code>chmod +x sbn_webhook.sh</code></li>
                        <li>Consultez les logs: <code>tail -50 /volume1/scripts/sbn/webhook.log</code></li>
                        <li>Activez le debug dans <code>config.sh</code>: <code>SBN_DEBUG="true"</code></li>
                    </ul>
                </div>
            </div>

            <div class="trouble-item">
                <h4><i class="fas fa-lock"></i> Erreur "Token invalide"</h4>
                <div class="trouble-solution">
                    <p><strong>Solutions:</strong></p>
                    <ul>
                        <li>Vérifiez que le token n'a pas été révoqué dans <a href="<?= APP_URL ?>/settings/api">Tokens API</a></li>
                        <li>Vérifiez qu'il n'y a pas d'espaces avant/après le token dans config.sh</li>
                        <li>Le token commence bien par <code>sbn_</code></li>
                        <li>Si nécessaire, générez un nouveau token</li>
                    </ul>
                </div>
            </div>

            <div class="trouble-item">
                <h4><i class="fas fa-file-alt"></i> Fichier config.sh introuvable</h4>
                <div class="trouble-solution">
                    <p><strong>Solutions:</strong></p>
                    <ul>
                        <li>Le fichier doit être dans le même dossier que sbn_webhook.sh</li>
                        <li>Chemin complet: <code>/volume1/scripts/sbn/config.sh</code></li>
                        <li>Téléchargez-le depuis <a href="<?= APP_URL ?>/settings/api">Tokens API</a></li>
                        <li>N'utilisez PAS config.sh.example, il faut votre config.sh personnalisé</li>
                    </ul>
                </div>
            </div>

            <div class="trouble-item">
                <h4><i class="fas fa-network-wired"></i> Erreur de connexion</h4>
                <div class="trouble-solution">
                    <p><strong>Solutions:</strong></p>
                    <ul>
                        <li>Vérifiez que l'URL dans config.sh est correcte</li>
                        <li>Votre NAS a-t-il accès à Internet?</li>
                        <li>Testez: <code>curl -I <?= APP_URL ?>/api/webhook</code></li>
                        <li>Vérifiez votre pare-feu Synology</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Support -->
<div class="card">
    <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
        <h2><i class="fas fa-life-ring"></i> Besoin d'aide?</h2>
    </div>
    <div class="card-body">
        <div class="support-grid">
            <div class="support-card">
                <i class="fas fa-key"></i>
                <h3>Tokens API</h3>
                <p>Gérez vos tokens d'authentification</p>
                <a href="<?= APP_URL ?>/settings/api" class="btn btn-secondary">Accéder</a>
            </div>

            <div class="support-card">
                <i class="fas fa-history"></i>
                <h3>Logs d'audit</h3>
                <p>Consultez l'historique des actions</p>
                <a href="<?= APP_URL ?>/settings" class="btn btn-secondary">Accéder</a>
            </div>

            <div class="support-card">
                <i class="fas fa-tachometer-alt"></i>
                <h3>Dashboard</h3>
                <p>Voir les sauvegardes en temps réel</p>
                <a href="<?= APP_URL ?>/dashboard" class="btn btn-secondary">Accéder</a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Download Grid */
    .download-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .download-card {
        display: flex;
        gap: 1rem;
        padding: 1.5rem;
        background: white;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-lg);
        text-decoration: none;
        color: var(--text-primary);
        transition: all 0.3s;
        align-items: center;
    }

    .download-card:hover {
        border-color: var(--primary-from);
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
    }

    .download-icon {
        flex-shrink: 0;
        width: 60px;
        height: 60px;
        background: var(--primary-gradient);
        color: white;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    .download-info h3 {
        margin: 0 0 0.5rem 0;
        font-size: 1.1rem;
        color: var(--text-primary);
    }

    .download-info p {
        margin: 0 0 0.75rem 0;
        font-size: 0.9rem;
        color: var(--text-muted);
    }

    .download-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .download-badge-optional {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    }

    /* Installation Steps */
    .installation-steps {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .install-step {
        border-left: 4px solid var(--primary-from);
        padding-left: 2rem;
        position: relative;
    }

    .step-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .step-number {
        flex-shrink: 0;
        width: 50px;
        height: 50px;
        background: var(--primary-gradient);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.5rem;
        box-shadow: var(--shadow-lg);
    }

    .step-header h3 {
        margin: 0;
        font-size: 1.25rem;
    }

    .step-content {
        padding-left: 66px;
    }

    .step-content ol,
    .step-content ul {
        margin: 1rem 0;
        padding-left: 1.5rem;
    }

    .step-content li {
        margin-bottom: 0.5rem;
        line-height: 1.6;
    }

    .step-visual {
        margin-top: 1.5rem;
    }

    .code-block {
        background: #1e293b;
        border-radius: var(--radius-md);
        overflow: hidden;
        margin-top: 1rem;
    }

    .code-header {
        padding: 0.75rem 1rem;
        background: #0f172a;
        color: #94a3b8;
        font-weight: 600;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .code-block code {
        display: block;
        padding: 1rem;
        color: #e2e8f0;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        line-height: 1.6;
        overflow-x: auto;
    }

    .code-block pre {
        margin: 0;
    }

    .code-var {
        color: #fbbf24;
        font-weight: 600;
    }

    .method-tabs {
        display: grid;
        gap: 1.5rem;
        margin-top: 1rem;
    }

    .method-tab {
        padding: 1.5rem;
        background: var(--gray-50);
        border-radius: var(--radius-lg);
        border: 2px solid var(--border-color);
    }

    .method-tab h4 {
        margin: 0 0 1rem 0;
        color: var(--primary-from);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Troubleshooting */
    .troubleshooting-grid {
        display: grid;
        gap: 1.5rem;
    }

    .trouble-item {
        padding: 1.5rem;
        background: var(--gray-50);
        border-radius: var(--radius-lg);
        border-left: 4px solid #ef4444;
    }

    .trouble-item h4 {
        margin: 0 0 1rem 0;
        color: #dc2626;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .trouble-solution ul {
        margin: 0.5rem 0 0 0;
        padding-left: 1.5rem;
    }

    .trouble-solution li {
        margin-bottom: 0.5rem;
    }

    /* Support Grid */
    .support-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .support-card {
        text-align: center;
        padding: 2rem;
        background: var(--gray-50);
        border-radius: var(--radius-lg);
        border: 2px solid var(--border-color);
        transition: all 0.3s;
    }

    .support-card:hover {
        border-color: var(--primary-from);
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .support-card i {
        font-size: 3rem;
        color: var(--primary-from);
        margin-bottom: 1rem;
    }

    .support-card h3 {
        margin: 0 0 0.5rem 0;
    }

    .support-card p {
        margin: 0 0 1.5rem 0;
        color: var(--text-muted);
    }

    @media (max-width: 768px) {
        .step-content {
            padding-left: 0;
        }

        .download-grid,
        .support-grid {
            grid-template-columns: 1fr;
        }
    }
</style>