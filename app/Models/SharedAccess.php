<?php
/**
 * SBN v1.0 - Modèle SharedAccess
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Models;

class SharedAccess extends Model {
    protected $table = 'shared_access';

    /**
     * Créer un partage
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
     * Obtenir les partages d'un utilisateur (ce qu'il a partagé)
     *
     * @param int $userId
     * @return array
     */
    public function getSharedByUser($userId) {
        $sql = "SELECT sa.*, u.name as shared_with_name, u.email as shared_with_email, c.name as company_name
                FROM {$this->table} sa
                LEFT JOIN users u ON sa.shared_with_user_id = u.id
                LEFT JOIN companies c ON sa.company_id = c.id
                WHERE sa.owner_user_id = ?
                AND sa.is_active = 1
                ORDER BY u.name, c.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    /**
     * Obtenir les partages reçus par un utilisateur
     *
     * @param int $userId
     * @return array
     */
    public function getSharedWithUser($userId) {
        $sql = "SELECT sa.*, u.name as owner_name, u.email as owner_email, c.name as company_name
                FROM {$this->table} sa
                LEFT JOIN users u ON sa.owner_user_id = u.id
                LEFT JOIN companies c ON sa.company_id = c.id
                WHERE sa.shared_with_user_id = ?
                AND sa.is_active = 1
                ORDER BY u.name, c.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    /**
     * Partager une société avec un utilisateur
     *
     * @param int $ownerId
     * @param int $sharedWithId
     * @param int $companyId
     * @param string $accessLevel
     * @return int|false
     */
    public function shareCompany($ownerId, $sharedWithId, $companyId, $accessLevel = 'read') {
        // Vérifier si le partage existe déjà
        $existing = $this->findWhere([
            'owner_user_id' => $ownerId,
            'shared_with_user_id' => $sharedWithId,
            'company_id' => $companyId
        ], null, 1);

        if ($existing) {
            // Mettre à jour le partage existant
            return $this->update($existing['id'], [
                'access_level' => $accessLevel,
                'is_active' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Créer un nouveau partage
        return $this->create([
            'owner_user_id' => $ownerId,
            'shared_with_user_id' => $sharedWithId,
            'company_id' => $companyId,
            'access_level' => $accessLevel,
            'is_active' => 1
        ]);
    }

    /**
     * Révoquer un partage
     *
     * @param int $shareId
     * @return bool
     */
    public function revokeShare($shareId) {
        return $this->update($shareId, [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Vérifier si un utilisateur peut partager une société
     *
     * @param int $userId
     * @param int $companyId
     * @return bool
     */
    public function canShareCompany($userId, $companyId) {
        // Vérifier si l'utilisateur a un token API pour cette société
        $sql = "SELECT id FROM api_tokens WHERE user_id = ? AND company_id = ? AND is_active = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $companyId]);

        return (bool) $stmt->fetch();
    }
}
