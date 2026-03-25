<?php
/**
 * SBN v1.0 - Modèle Company
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Models;

class Company extends Model {
    protected $table = 'companies';

    /**
     * Créer une nouvelle société
     *
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $companyId = $this->insert($data);

        if ($companyId && isset($data['created_by'])) {
            $this->logAction('company_created', $data['created_by'], ['company_id' => $companyId]);
        }

        return $companyId;
    }

    /**
     * Obtenir toutes les sociétés actives
     *
     * @return array
     */
    public function findActive() {
        return $this->findWhere(['is_active' => 1], 'name ASC');
    }

    /**
     * Obtenir le nombre d'utilisateurs d'une société
     *
     * @param int $companyId
     * @return int
     */
    public function getUserCount($companyId) {
        $sql = "SELECT COUNT(*) FROM users WHERE company_id = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtenir le nombre de sauvegardes d'une société
     *
     * @param int $companyId
     * @return int
     */
    public function getBackupCount($companyId) {
        $sql = "SELECT COUNT(*) FROM backups WHERE company_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId]);
        return (int) $stmt->fetchColumn();
    }
}
