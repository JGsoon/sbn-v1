<?php
/**
 * SBN v1.0 - Modèle de base
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Models;

use Config\Database;
use PDO;

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    /**
     * Constructeur
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Trouver tous les enregistrements
     *
     * @param string $orderBy Ordre de tri
     * @return array
     */
    public function findAll($orderBy = null) {
        $sql = "SELECT * FROM {$this->table}";

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Trouver un enregistrement par ID
     *
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Trouver des enregistrements selon des critères
     *
     * @param array $conditions Conditions WHERE
     * @param string $orderBy Ordre de tri
     * @param int $limit Limite de résultats
     * @return array
     */
    public function findWhere($conditions, $orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE ";

        $whereClauses = [];
        $params = [];

        foreach ($conditions as $field => $value) {
            $whereClauses[] = "$field = ?";
            $params[] = $value;
        }

        $sql .= implode(' AND ', $whereClauses);

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $limit === 1 ? $stmt->fetch() : $stmt->fetchAll();
    }

    /**
     * Insérer un enregistrement
     *
     * @param array $data Données à insérer
     * @return int|false ID inséré ou false
     */
    public function insert($data) {
        $fields = array_keys($data);
        $values = array_values($data);

        $fieldsList = implode(', ', $fields);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));

        $sql = "INSERT INTO {$this->table} ($fieldsList) VALUES ($placeholders)";

        $stmt = $this->db->prepare($sql);

        if ($stmt->execute($values)) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Mettre à jour un enregistrement
     *
     * @param int $id ID de l'enregistrement
     * @param array $data Données à mettre à jour
     * @return bool
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];

        foreach ($data as $field => $value) {
            $fields[] = "$field = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Supprimer un enregistrement
     *
     * @param int $id ID de l'enregistrement
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Compter les enregistrements
     *
     * @param array $conditions Conditions WHERE (optionnel)
     * @return int
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) FROM {$this->table}";

        if (!empty($conditions)) {
            $sql .= " WHERE ";
            $whereClauses = [];
            $params = [];

            foreach ($conditions as $field => $value) {
                $whereClauses[] = "$field = ?";
                $params[] = $value;
            }

            $sql .= implode(' AND ', $whereClauses);

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } else {
            $stmt = $this->db->query($sql);
        }

        return (int) $stmt->fetchColumn();
    }

    /**
     * Exécuter une requête personnalisée
     *
     * @param string $sql Requête SQL
     * @param array $params Paramètres
     * @return array
     */
    protected function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Logger une action
     *
     * @param string $action Action effectuée
     * @param int $userId ID utilisateur
     * @param array $data Données supplémentaires
     */
    protected function logAction($action, $userId, $data = []) {
        $sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, data, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $action,
            $this->table,
            $data['id'] ?? null,
            json_encode($data),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
}
