<?php
/**
 * SBN v1.0 - Modèle NasDevice
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Models;

class NasDevice extends Model {
    protected $table = 'nas_devices';

    /**
     * Créer un NAS
     *
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->insert($data);
    }

    /**
     * Obtenir les NAS d'une société
     *
     * @param int $companyId
     * @return array
     */
    public function findByCompany($companyId) {
        return $this->findWhere(['company_id' => $companyId], 'name ASC');
    }

    /**
     * Obtenir les NAS actifs d'une société
     *
     * @param int $companyId
     * @return array
     */
    public function findActiveByCompany($companyId) {
        return $this->findWhere([
            'company_id' => $companyId,
            'is_active' => 1
        ], 'name ASC');
    }

    /**
     * Mettre à jour le dernier contact
     *
     * @param int $nasId
     * @return bool
     */
    public function updateLastSeen($nasId) {
        return $this->update($nasId, [
            'last_seen_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Obtenir les NAS avec leurs équipements de sauvegarde
     *
     * @param int $companyId
     * @return array
     */
    public function getWithBackupDevices($companyId) {
        $sql = "SELECT
                    n.*,
                    COUNT(DISTINCT bd.id) as device_count,
                    COUNT(DISTINCT b.id) as backup_count,
                    MAX(b.start_time) as last_backup_time,
                    SUM(CASE WHEN b.status = 'success' THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN b.status = 'failed' THEN 1 ELSE 0 END) as failed_count
                FROM {$this->table} n
                LEFT JOIN backup_devices bd ON n.id = bd.nas_device_id
                LEFT JOIN backups b ON bd.id = b.device_id
                WHERE n.company_id = ?
                AND n.is_active = 1
                GROUP BY n.id
                ORDER BY n.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId]);

        return $stmt->fetchAll();
    }

    /**
     * Obtenir un NAS avec ses statistiques complètes
     *
     * @param int $nasId
     * @return array|false
     */
    public function getWithStats($nasId) {
        $sql = "SELECT
                    n.*,
                    COUNT(DISTINCT bd.id) as device_count,
                    COUNT(DISTINCT b.id) as total_backups,
                    SUM(CASE WHEN b.status = 'success' THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN b.status = 'failed' THEN 1 ELSE 0 END) as failed_count,
                    SUM(CASE WHEN b.status = 'running' THEN 1 ELSE 0 END) as running_count,
                    MAX(b.start_time) as last_backup_time,
                    AVG(b.duration_seconds) as avg_duration,
                    SUM(b.size_bytes) as total_size
                FROM {$this->table} n
                LEFT JOIN backup_devices bd ON n.id = bd.nas_device_id
                LEFT JOIN backups b ON bd.id = b.device_id
                WHERE n.id = ?
                GROUP BY n.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nasId]);

        return $stmt->fetch();
    }
}
