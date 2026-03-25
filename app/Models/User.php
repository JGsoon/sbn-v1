<?php
/**
 * SBN v1.0 - Modèle User
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Models;

class User extends Model {
    protected $table = 'users';

    /**
     * Trouver un utilisateur par email
     *
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email) {
        return $this->findWhere(['email' => $email], null, 1);
    }

    /**
     * Créer un nouvel utilisateur
     *
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        // Hash du mot de passe
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        // Générer un token de vérification
        $data['verification_token'] = bin2hex(random_bytes(32));
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $userId = $this->insert($data);

        if ($userId) {
            $this->logAction('user_created', $userId, ['email' => $data['email']]);
        }

        return $userId;
    }

    /**
     * Vérifier le mot de passe
     *
     * @param string $email
     * @param string $password
     * @return array|false
     */
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);

        if (!$user) {
            return false;
        }

        // Vérifier si le compte est actif
        if (!$user['is_active']) {
            return false;
        }

        // Vérifier le mot de passe
        if (!password_verify($password, $user['password'])) {
            // Logger la tentative échouée
            $this->logFailedLogin($user['id'], $email);
            return false;
        }

        // Vérifier si le compte est verrouillé
        if ($this->isAccountLocked($user['id'])) {
            return false;
        }

        // Réinitialiser les tentatives échouées
        $this->resetFailedLogins($user['id']);

        // Mettre à jour la dernière connexion
        $this->update($user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ]);

        // Logger la connexion réussie
        $this->logAction('user_login', $user['id'], ['email' => $email]);

        return $user;
    }

    /**
     * Logger une tentative de connexion échouée
     *
     * @param int $userId
     * @param string $email
     */
    private function logFailedLogin($userId, $email) {
        $sql = "INSERT INTO login_attempts (user_id, email, ip_address, user_agent, attempted_at)
                VALUES (?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $email,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);

        $this->logAction('failed_login', $userId, ['email' => $email]);
    }

    /**
     * Vérifier si le compte est verrouillé
     *
     * @param int $userId
     * @return bool
     */
    private function isAccountLocked($userId) {
        $sql = "SELECT COUNT(*) FROM login_attempts
                WHERE user_id = ?
                AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, LOGIN_LOCKOUT_TIME]);

        $attempts = (int) $stmt->fetchColumn();

        return $attempts >= MAX_LOGIN_ATTEMPTS;
    }

    /**
     * Réinitialiser les tentatives de connexion échouées
     *
     * @param int $userId
     */
    private function resetFailedLogins($userId) {
        $sql = "DELETE FROM login_attempts WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
    }

    /**
     * Générer un token de réinitialisation de mot de passe
     *
     * @param string $email
     * @return string|false
     */
    public function createPasswordResetToken($email) {
        $user = $this->findByEmail($email);

        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->update($user['id'], [
            'reset_token' => $token,
            'reset_token_expires_at' => $expiresAt
        ]);

        $this->logAction('password_reset_requested', $user['id'], ['email' => $email]);

        return $token;
    }

    /**
     * Vérifier et utiliser un token de réinitialisation
     *
     * @param string $token
     * @return array|false
     */
    public function verifyResetToken($token) {
        $sql = "SELECT * FROM {$this->table}
                WHERE reset_token = ?
                AND reset_token_expires_at > NOW()
                AND is_active = 1
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);

        return $stmt->fetch();
    }

    /**
     * Réinitialiser le mot de passe
     *
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function resetPassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        $result = $this->update($userId, [
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_token_expires_at' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($result) {
            $this->logAction('password_reset', $userId);
        }

        return $result;
    }

    /**
     * Changer le mot de passe
     *
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->findById($userId);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return false;
        }

        return $this->resetPassword($userId, $newPassword);
    }

    /**
     * Obtenir les utilisateurs par société
     *
     * @param int $companyId
     * @return array
     */
    public function findByCompany($companyId) {
        return $this->findWhere(['company_id' => $companyId], 'name ASC');
    }

    /**
     * Activer/Désactiver un utilisateur
     *
     * @param int $userId
     * @param bool $isActive
     * @return bool
     */
    public function setActive($userId, $isActive) {
        $result = $this->update($userId, [
            'is_active' => $isActive ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($result) {
            $action = $isActive ? 'user_activated' : 'user_deactivated';
            $this->logAction($action, $userId);
        }

        return $result;
    }

    /**
     * Exporter les données utilisateur (RGPD)
     *
     * @param int $userId
     * @return array
     */
    public function exportUserData($userId) {
        $user = $this->findById($userId);

        if (!$user) {
            return [];
        }

        // Supprimer le mot de passe des données exportées
        unset($user['password']);
        unset($user['reset_token']);
        unset($user['verification_token']);

        return $user;
    }

    /**
     * Supprimer définitivement un utilisateur (RGPD)
     *
     * @param int $userId
     * @return bool
     */
    public function deleteUserData($userId) {
        // Anonymiser les données au lieu de supprimer (pour garder l'historique)
        $result = $this->update($userId, [
            'email' => 'deleted_' . $userId . '@deleted.local',
            'name' => 'Utilisateur supprimé',
            'password' => '',
            'is_active' => 0,
            'deleted_at' => date('Y-m-d H:i:s')
        ]);

        if ($result) {
            $this->logAction('user_deleted_gdpr', $userId);
        }

        return $result;
    }

    /**
     * Vérifier le statut de l'abonnement
     *
     * @param int $userId
     * @return bool
     */
    public function checkSubscriptionStatus($userId) {
        $user = $this->findById($userId);

        if (!$user) {
            return false;
        }

        // Les admins ont toujours accès
        if ($user['role'] === 'admin') {
            return true;
        }

        // Vérifier si l'abonnement est actif ou en trial
        if ($user['subscription_status'] === 'active' || $user['subscription_status'] === 'trial') {
            // Vérifier si la date de fin n'est pas dépassée
            if ($user['subscription_end'] && strtotime($user['subscription_end']) < time()) {
                // Mettre à jour le statut en expired
                $this->update($userId, [
                    'subscription_status' => 'expired',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Offrir des jours/mois/années gratuits
     *
     * @param int $userId
     * @param int $days
     * @param string $reason
     * @param int $adminId
     * @return bool
     */
    public function grantFreeDays($userId, $days, $reason = '', $adminId = null) {
        $user = $this->findById($userId);

        if (!$user) {
            return false;
        }

        $currentEnd = $user['subscription_end'] ? strtotime($user['subscription_end']) : time();
        $newEnd = date('Y-m-d', strtotime("+{$days} days", $currentEnd));

        $result = $this->update($userId, [
            'subscription_end' => $newEnd,
            'subscription_status' => 'active',
            'free_days_granted' => ($user['free_days_granted'] ?? 0) + $days,
            'subscription_notes' => ($user['subscription_notes'] ?? '') . "\n" . date('Y-m-d H:i:s') . " - {$days} jours offerts. Raison: {$reason}",
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($result && $adminId) {
            $this->logAction('subscription_extended', $userId, [
                'days' => $days,
                'admin_id' => $adminId,
                'reason' => $reason
            ]);
        }

        return $result;
    }

    /**
     * Suspendre un compte (mauvais payeur)
     *
     * @param int $userId
     * @param string $reason
     * @param int $adminId
     * @return bool
     */
    public function suspendAccount($userId, $reason = '', $adminId = null) {
        $result = $this->update($userId, [
            'subscription_status' => 'suspended',
            'subscription_notes' => ($this->findById($userId)['subscription_notes'] ?? '') . "\n" . date('Y-m-d H:i:s') . " - Compte suspendu. Raison: {$reason}",
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($result && $adminId) {
            $this->logAction('account_suspended', $userId, [
                'admin_id' => $adminId,
                'reason' => $reason
            ]);
        }

        return $result;
    }

    /**
     * Réactiver un compte suspendu
     *
     * @param int $userId
     * @param int $adminId
     * @return bool
     */
    public function unsuspendAccount($userId, $adminId = null) {
        $result = $this->update($userId, [
            'subscription_status' => 'active',
            'subscription_notes' => ($this->findById($userId)['subscription_notes'] ?? '') . "\n" . date('Y-m-d H:i:s') . " - Compte réactivé",
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($result && $adminId) {
            $this->logAction('account_unsuspended', $userId, [
                'admin_id' => $adminId
            ]);
        }

        return $result;
    }

    /**
     * Réinitialiser le mot de passe par un admin (RGPD compliant)
     *
     * @param int $userId
     * @param int $adminId
     * @return string|false Retourne le nouveau mot de passe temporaire
     */
    public function adminResetPassword($userId, $adminId) {
        // Générer un mot de passe temporaire sécurisé
        $tempPassword = bin2hex(random_bytes(8)); // 16 caractères

        $hashedPassword = password_hash($tempPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        $result = $this->update($userId, [
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_token_expires_at' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($result) {
            // Logger la réinitialisation dans la table d'historique
            $sql = "INSERT INTO password_reset_history (user_id, reset_by_admin_id, reset_type, ip_address, created_at)
                    VALUES (?, ?, 'admin', ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userId,
                $adminId,
                $_SERVER['REMOTE_ADDR'] ?? ''
            ]);

            $this->logAction('password_reset_by_admin', $userId, ['admin_id' => $adminId]);

            return $tempPassword;
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     *
     * @param int $userId
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($userId, $roles) {
        $user = $this->findById($userId);

        if (!$user) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($user['role'], $roles);
        }

        return $user['role'] === $roles;
    }

    /**
     * Obtenir les sociétés accessibles par un utilisateur (incluant les partages)
     *
     * @param int $userId
     * @return array
     */
    public function getAccessibleCompanies($userId) {
        $user = $this->findById($userId);

        if (!$user) {
            return [];
        }

        // Les admins peuvent tout voir
        if ($user['role'] === 'admin') {
            $sql = "SELECT * FROM companies WHERE is_active = 1 ORDER BY name";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        }

        // Obtenir les sociétés propres + celles partagées
        $sql = "SELECT DISTINCT c.* FROM companies c
                LEFT JOIN shared_access sa ON (c.id = sa.company_id AND sa.shared_with_user_id = ? AND sa.is_active = 1)
                WHERE (c.id IN (
                    SELECT company_id FROM api_tokens WHERE user_id = ?
                ) OR sa.id IS NOT NULL)
                AND c.is_active = 1
                ORDER BY c.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $userId]);

        return $stmt->fetchAll();
    }

    /**
     * Vérifier si un utilisateur peut accéder à une société
     *
     * @param int $userId
     * @param int $companyId
     * @return bool|string Retourne false si pas d'accès, 'owner' si propriétaire, 'read' ou 'write' si partagé
     */
    public function canAccessCompany($userId, $companyId) {
        $user = $this->findById($userId);

        if (!$user) {
            return false;
        }

        // Les admins peuvent tout voir
        if ($user['role'] === 'admin') {
            return 'admin';
        }

        // Vérifier si c'est le propriétaire (via token API)
        $sql = "SELECT id FROM api_tokens WHERE user_id = ? AND company_id = ? AND is_active = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $companyId]);

        if ($stmt->fetch()) {
            return 'owner';
        }

        // Vérifier les accès partagés
        $sql = "SELECT access_level FROM shared_access
                WHERE shared_with_user_id = ?
                AND company_id = ?
                AND is_active = 1
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $companyId]);

        $access = $stmt->fetch();

        if ($access) {
            return $access['access_level'];
        }

        return false;
    }
}
