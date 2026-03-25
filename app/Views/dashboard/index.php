<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1><i class="fas fa-tachometer-alt"></i> Tableau de bord</h1>
            <p>Supervision des sauvegardes</p>
        </div>
    </div>
</div>

<?php if (!empty($global_stats)): ?>
<!-- Statistiques globales -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($global_stats['companies_count'] ?? 0) ?></h3>
            <p>Clients</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <i class="fas fa-server"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($global_stats['nas_count'] ?? 0) ?></h3>
            <p>NAS Synology</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <i class="fas fa-laptop"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($global_stats['devices_count'] ?? 0) ?></h3>
            <p>Équipements</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($global_stats['success_backups'] ?? 0) ?></h3>
            <p>Sauvegardes réussies</p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Liste des clients avec leurs NAS et équipements -->
<?php if (empty($companies_data)): ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <i class="fas fa-database"></i>
                <h3>Aucune donnée</h3>
                <p>Commencez par configurer vos premiers clients et NAS</p>
                <a href="<?= APP_URL ?>/companies" class="btn btn-primary mt-20">
                    <i class="fas fa-plus-circle"></i> Ajouter un client
                </a>
            </div>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($companies_data as $companyData): ?>
        <?php
        $company = $companyData['company'];
        $accessLevel = $companyData['access_level'];
        $nasList = $companyData['nas_list'];
        ?>

        <div class="client-section">
            <div class="client-header">
                <div class="client-info">
                    <h2>
                        <i class="fas fa-building"></i>
                        <?= htmlspecialchars($company['name']) ?>
                    </h2>
                    <?php if ($accessLevel !== 'admin' && $accessLevel !== 'owner'): ?>
                        <span class="badge badge-<?= $accessLevel === 'read' ? 'secondary' : 'primary' ?>">
                            <?= $accessLevel === 'read' ? 'Lecture seule' : 'Accès partagé' ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (empty($nasList)): ?>
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted text-center">
                            <i class="fas fa-info-circle"></i>
                            Aucun NAS configuré pour ce client
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($nasList as $nas): ?>
                    <div class="nas-card">
                        <div class="nas-header">
                            <div class="nas-info">
                                <h3>
                                    <i class="fas fa-server"></i>
                                    <?= htmlspecialchars($nas['name']) ?>
                                </h3>
                                <?php if (!empty($nas['quickconnect_id'])): ?>
                                    <a href="http://quickconnect.to/<?= htmlspecialchars($nas['quickconnect_id']) ?>"
                                       target="_blank"
                                       class="quickconnect-link"
                                       title="Ouvrir via QuickConnect">
                                        <i class="fas fa-external-link-alt"></i>
                                        <?= htmlspecialchars($nas['quickconnect_id']) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="nas-stats">
                                <span class="badge badge-success">
                                    <i class="fas fa-check"></i> <?= $nas['success_count'] ?? 0 ?> réussies
                                </span>
                                <?php if (($nas['failed_count'] ?? 0) > 0): ?>
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times"></i> <?= $nas['failed_count'] ?? 0 ?> échecs
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (empty($nas['devices'])): ?>
                            <div class="equipment-item">
                                <p class="text-muted">Aucun équipement configuré</p>
                            </div>
                        <?php else: ?>
                            <!-- Liste des équipements -->
                            <div class="equipment-list">
                                <?php foreach ($nas['devices'] as $device): ?>
                                    <?php
                                    $lastStatus = $device['last_status'] ?? 'unknown';
                                    $statusClass = '';
                                    $statusIcon = '';
                                    $statusText = '';

                                    switch ($lastStatus) {
                                        case 'success':
                                            $statusClass = 'status-success';
                                            $statusIcon = 'fa-check-circle';
                                            $statusText = 'Sauvegarde réussie';
                                            break;
                                        case 'failed':
                                            $statusClass = 'status-error';
                                            $statusIcon = 'fa-times-circle';
                                            $statusText = 'Échec';
                                            break;
                                        case 'running':
                                            $statusClass = 'status-running';
                                            $statusIcon = 'fa-spinner fa-spin';
                                            $statusText = 'En cours...';
                                            break;
                                        default:
                                            $statusClass = 'status-unknown';
                                            $statusIcon = 'fa-question-circle';
                                            $statusText = 'Aucune sauvegarde';
                                    }
                                    ?>

                                    <div class="equipment-item">
                                        <div class="equipment-name">
                                            <i class="fas fa-laptop"></i>
                                            <strong><?= htmlspecialchars($device['name']) ?></strong>
                                            <?php if (!empty($device['hostname'])): ?>
                                                <span class="text-muted">(<?= htmlspecialchars($device['hostname']) ?>)</span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="equipment-status <?= $statusClass ?>">
                                            <i class="fas <?= $statusIcon ?>"></i>
                                            <?= $statusText ?>
                                        </div>

                                        <?php if (!empty($device['last_backup_time'])): ?>
                                            <div class="equipment-date">
                                                <i class="far fa-clock"></i>
                                                <?php
                                                $date = new DateTime($device['last_backup_time']);
                                                $now = new DateTime();
                                                $diff = $now->diff($date);

                                                if ($diff->days == 0 && $diff->h < 24) {
                                                    if ($diff->h > 0) {
                                                        echo "Il y a {$diff->h}h";
                                                    } else {
                                                        echo "Il y a {$diff->i}min";
                                                    }
                                                } else {
                                                    echo $date->format('d/m/Y à H:i');
                                                }
                                                ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="equipment-info">
                                            <?php if (!empty($device['last_size'])): ?>
                                                <span>
                                                    <i class="fas fa-database"></i>
                                                    <?php
                                                    $size = $device['last_size'];
                                                    if ($size > 1099511627776) {
                                                        echo number_format($size / 1099511627776, 2) . ' To';
                                                    } elseif ($size > 1073741824) {
                                                        echo number_format($size / 1073741824, 2) . ' Go';
                                                    } elseif ($size > 1048576) {
                                                        echo number_format($size / 1048576, 2) . ' Mo';
                                                    } else {
                                                        echo number_format($size / 1024, 2) . ' Ko';
                                                    }
                                                    ?>
                                                </span>
                                            <?php endif; ?>

                                            <span>
                                                <i class="fas fa-check-circle"></i>
                                                <?= $device['success_count'] ?? 0 ?> réussies
                                            </span>

                                            <?php if (($device['failed_count'] ?? 0) > 0): ?>
                                                <span style="color: var(--danger);">
                                                    <i class="fas fa-times-circle"></i>
                                                    <?= $device['failed_count'] ?> échecs
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<style>
.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 2rem;
    font-weight: var(--font-weight-bold);
    margin-bottom: 0.5rem;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    gap: 1rem;
    align-items: center;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-content h3 {
    font-size: 2rem;
    font-weight: bold;
    margin: 0;
    color: var(--text-primary);
}

.stat-content p {
    margin: 0;
    color: var(--text-secondary);
}

.client-section {
    margin-bottom: 3rem;
}

.client-header {
    background: white;
    border-radius: 12px 12px 0 0;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left: 4px solid #667eea;
}

.client-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.client-info h2 {
    font-size: 1.5rem;
    margin: 0;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nas-card {
    background: white;
    border-radius: 12px;
    margin-top: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.nas-header {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.nas-info h3 {
    font-size: 1.25rem;
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quickconnect-link {
    color: #667eea;
    text-decoration: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.quickconnect-link:hover {
    text-decoration: underline;
}

.nas-stats {
    display: flex;
    gap: 0.75rem;
}

.badge {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
}

.badge-success {
    background: #43e97b;
    color: white;
}

.badge-danger {
    background: #f5576c;
    color: white;
}

.badge-secondary {
    background: #6c757d;
    color: white;
}

.badge-primary {
    background: #667eea;
    color: white;
}

.equipment-list {
    padding: 0.5rem;
}

.equipment-item {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 2fr;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid #f0f0f0;
    align-items: center;
}

.equipment-item:last-child {
    border-bottom: none;
}

.equipment-name {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.equipment-name strong {
    color: var(--text-primary);
}

.equipment-status {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: center;
}

.status-success {
    background: #d4edda;
    color: #155724;
}

.status-error {
    background: #f8d7da;
    color: #721c24;
}

.status-running {
    background: #fff3cd;
    color: #856404;
}

.status-unknown {
    background: #f0f0f0;
    color: #6c757d;
}

.equipment-date {
    color: var(--text-secondary);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.equipment-info {
    display: flex;
    gap: 1.5rem;
    font-size: 0.9rem;
    color: var(--text-secondary);
}

.equipment-info span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

@media (max-width: 1024px) {
    .equipment-item {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }

    .equipment-status {
        justify-content: flex-start;
    }
}
</style>
