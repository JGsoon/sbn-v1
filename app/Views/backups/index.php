<div class="page-header">
    <h1><i class="fas fa-database"></i> Sauvegardes</h1>
    <p>Historique complet de vos sauvegardes (50 dernières)</p>
</div>

<?php if (empty($backups)): ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <i class="fas fa-database"></i>
                <h3>Aucune sauvegarde</h3>
                <p>Aucune sauvegarde n'a encore été enregistrée pour votre société.</p>
                <p class="text-muted" style="margin-top: 1rem; font-size: 0.875rem;">
                    Les sauvegardes seront automatiquement ajoutées lorsque Synology Active Backup enverra des webhooks.
                </p>
                <a href="<?= APP_URL ?>/dashboard" class="btn btn-primary mt-20">
                    <i class="fas fa-arrow-left"></i> Retour au dashboard
                </a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            <h2>Historique des sauvegardes</h2>
            <div class="card-header-actions">
                <button class="btn btn-secondary btn-sm">
                    <i class="fas fa-filter"></i> Filtrer
                </button>
                <button class="btn btn-secondary btn-sm">
                    <i class="fas fa-download"></i> Exporter
                </button>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Appareil</th>
                            <th>Date de début</th>
                            <th>Date de fin</th>
                            <th>Statut</th>
                            <th>Taille</th>
                            <th>Durée</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td>
                                    <div class="device-info">
                                        <i class="fas fa-laptop"></i>
                                        <strong><?= htmlspecialchars($backup['device_name'] ?? 'Appareil inconnu') ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($backup['start_time']): ?>
                                        <span class="date-display">
                                            <?= date('d/m/Y H:i', strtotime($backup['start_time'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($backup['end_time']): ?>
                                        <span class="date-display">
                                            <?= date('d/m/Y H:i', strtotime($backup['end_time'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">En cours...</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    $statusIcon = '';
                                    $statusText = '';

                                    switch($backup['status']) {
                                        case 'success':
                                            $statusClass = 'status-success';
                                            $statusIcon = 'fa-check-circle';
                                            $statusText = 'Réussie';
                                            break;
                                        case 'failed':
                                            $statusClass = 'status-danger';
                                            $statusIcon = 'fa-times-circle';
                                            $statusText = 'Échouée';
                                            break;
                                        case 'warning':
                                            $statusClass = 'status-warning';
                                            $statusIcon = 'fa-exclamation-triangle';
                                            $statusText = 'Avertissement';
                                            break;
                                        case 'running':
                                            $statusClass = 'status-info';
                                            $statusIcon = 'fa-spinner fa-spin';
                                            $statusText = 'En cours';
                                            break;
                                        default:
                                            $statusClass = 'status-muted';
                                            $statusIcon = 'fa-question-circle';
                                            $statusText = 'Inconnu';
                                    }
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <i class="fas <?= $statusIcon ?>"></i>
                                        <?= $statusText ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($backup['size_bytes']): ?>
                                        <span class="size-display">
                                            <?php
                                            $size = $backup['size_bytes'];
                                            $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
                                            $unit = 0;
                                            while ($size >= 1024 && $unit < count($units) - 1) {
                                                $size /= 1024;
                                                $unit++;
                                            }
                                            echo number_format($size, 2, ',', ' ') . ' ' . $units[$unit];
                                            ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($backup['start_time'] && $backup['end_time']): ?>
                                        <?php
                                        $start = new DateTime($backup['start_time']);
                                        $end = new DateTime($backup['end_time']);
                                        $interval = $start->diff($end);

                                        $duration = '';
                                        if ($interval->h > 0) {
                                            $duration .= $interval->h . 'h ';
                                        }
                                        if ($interval->i > 0) {
                                            $duration .= $interval->i . 'min ';
                                        }
                                        if ($interval->s > 0 && $interval->h == 0) {
                                            $duration .= $interval->s . 's';
                                        }
                                        ?>
                                        <span class="duration-display"><?= trim($duration) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?= APP_URL ?>/backups/detail?id=<?= $backup['id'] ?>"
                                           class="btn btn-sm btn-secondary"
                                           title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
