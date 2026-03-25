<div class="dashboard">
    <div class="dashboard-header">
        <h1>Tableau de bord</h1>
        <p>Bienvenue, <?= htmlspecialchars($user['name']) ?></p>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <i class="fas fa-database"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['total_backups']) ?></div>
                <div class="stat-label">Sauvegardes totales</div>
            </div>
        </div>

        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['success_backups']) ?></div>
                <div class="stat-label">Sauvegardes réussies</div>
            </div>
        </div>

        <div class="stat-card stat-danger">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['failed_backups']) ?></div>
                <div class="stat-label">Sauvegardes échouées</div>
            </div>
        </div>

        <div class="stat-card stat-info">
            <div class="stat-icon">
                <i class="fas fa-hdd"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['total_size'] > 0 ? number_format($stats['total_size'] / 1073741824, 2) . ' Go' : '0 Go' ?></div>
                <div class="stat-label">Taille totale</div>
            </div>
        </div>

        <div class="stat-card stat-warning">
            <div class="stat-icon">
                <i class="fas fa-laptop"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['devices_count']) ?></div>
                <div class="stat-label">Appareils actifs</div>
            </div>
        </div>
    </div>

    <!-- Dernières sauvegardes -->
    <div class="card mt-20">
        <div class="card-header">
            <h2><i class="fas fa-history"></i> Dernières sauvegardes</h2>
            <a href="<?= APP_URL ?>/backups" class="btn btn-primary btn-sm">Voir tout</a>
        </div>
        <div class="card-body">
            <?php if (empty($recent_backups)): ?>
                <p class="text-center text-muted">Aucune sauvegarde pour le moment.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Appareil</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Durée</th>
                                <th>Taille</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_backups as $backup): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($backup['device_name'] ?? 'Inconnu') ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?= htmlspecialchars($backup['backup_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($backup['status'] === 'success'): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Réussie
                                            </span>
                                        <?php elseif ($backup['status'] === 'failed'): ?>
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times"></i> Échouée
                                            </span>
                                        <?php elseif ($backup['status'] === 'running'): ?>
                                            <span class="badge badge-info">
                                                <i class="fas fa-spinner"></i> En cours
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">
                                                <?= htmlspecialchars($backup['status']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($backup['start_time'])) ?></td>
                                    <td>
                                        <?php
                                        if ($backup['duration_seconds']) {
                                            $minutes = floor($backup['duration_seconds'] / 60);
                                            $seconds = $backup['duration_seconds'] % 60;
                                            echo $minutes > 0 ? "{$minutes}min " : "";
                                            echo "{$seconds}s";
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($backup['size_bytes']) {
                                            echo number_format($backup['size_bytes'] / 1048576, 2) . ' Mo';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Graphique des sauvegardes (7 derniers jours) -->
    <?php if (!empty($backups_by_day)): ?>
        <div class="card mt-20">
            <div class="card-header">
                <h2><i class="fas fa-chart-line"></i> Sauvegardes des 7 derniers jours</h2>
            </div>
            <div class="card-body">
                <div class="chart-simple">
                    <?php foreach (array_reverse($backups_by_day) as $day): ?>
                        <div class="chart-day">
                            <div class="chart-label"><?= date('d/m', strtotime($day['date'])) ?></div>
                            <div class="chart-bars">
                                <div class="chart-bar chart-bar-success" style="height: <?= min(100, ($day['success'] / max(1, $day['total'])) * 100) ?>%"
                                     title="<?= $day['success'] ?> réussies">
                                </div>
                                <div class="chart-bar chart-bar-danger" style="height: <?= min(100, ($day['failed'] / max(1, $day['total'])) * 100) ?>%"
                                     title="<?= $day['failed'] ?> échouées">
                                </div>
                            </div>
                            <div class="chart-total"><?= $day['total'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.dashboard-header {
    margin-bottom: 32px;
}

.dashboard-header h1 {
    font-size: 32px;
    margin-bottom: 8px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
}

.stat-primary .stat-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-success .stat-icon { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); }
.stat-danger .stat-icon { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }
.stat-info .stat-icon { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
.stat-warning .stat-icon { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }

.stat-value {
    font-size: 32px;
    font-weight: 700;
    line-height: 1;
}

.stat-label {
    color: #666;
    font-size: 14px;
}

.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    font-size: 20px;
    margin: 0;
}

.card-body {
    padding: 24px;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.table th {
    font-weight: 600;
    color: #666;
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.badge-success { background: #d4edda; color: #155724; }
.badge-danger { background: #f8d7da; color: #721c24; }
.badge-info { background: #d1ecf1; color: #0c5460; }
.badge-secondary { background: #e2e3e5; color: #383d41; }

.chart-simple {
    display: flex;
    gap: 16px;
    align-items: flex-end;
    height: 200px;
    padding: 20px 0;
}

.chart-day {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.chart-bars {
    flex: 1;
    width: 100%;
    display: flex;
    gap: 4px;
    align-items: flex-end;
}

.chart-bar {
    flex: 1;
    min-height: 20px;
    border-radius: 4px 4px 0 0;
}

.chart-bar-success { background: #2ecc71; }
.chart-bar-danger { background: #e74c3c; }

.chart-label,
.chart-total {
    font-size: 12px;
    color: #666;
}
</style>
