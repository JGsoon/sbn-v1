<?php
/**
 * SBN v1.0 - Contrôleur Utilisateurs (Admin)
 *
 * Gestion complète des utilisateurs avec sécurité multi-tenant
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use PDO;

class UserController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Liste des utilisateurs (Admin uniquement)
     */
    public function index() {
        $user = $this->getUser();

        if (!$user || $user['role'] !== 'admin') {
            $this->redirect('dashboard');
        }

        // SÉCURITÉ: Récupérer UNIQUEMENT les utilisateurs de SA société
        $stmt = $this->db->prepare("
            SELECT u.*, c.name as company_name
            FROM users u
            LEFT JOIN companies c ON u.company_id = c.id
            WHERE u.company_id = ?
            ORDER BY u.created_at DESC
        ");
        $stmt->execute([$user['company_id']]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('users/index', [
            'title' => 'Utilisateurs - SBN v1.0',
            'user' => $user,
            'users' => $users
        ]);
    }

    /**
     * Formulaire d'ajout d'utilisateur
     */
    public function add() {
        $user = $this->getUser();

        if (!$user || $user['role'] !== 'admin') {
            $this->redirect('users');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Valider le CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token CSRF invalide');
                $this->redirect('users/add');
            }

            // Récupérer les données
            $name = trim($this->post('name'));
            $email = trim($this->post('email'));
            $password = $this->post('password');
            $password_confirm = $this->post('password_confirm');
            $role = $this->post('role');
            $phone = trim($this->post('phone'));

            // Validation
            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Le nom est requis';
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email invalide';
            }

            // Vérifier si l'email existe déjà
            if (!empty($email)) {
                $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $errors['email'] = 'Cet email est déjà utilisé';
                }
            }

            if (empty($password) || strlen($password) < 8) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
            }

            if ($password !== $password_confirm) {
                $errors['password_confirm'] = 'Les mots de passe ne correspondent pas';
            }

            if (!in_array($role, ['admin', 'user', 'collaborator', 'client'])) {
                $errors['role'] = 'Rôle invalide';
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $_POST;
                $this->redirect('users/add');
            }

            try {
                // Créer l'utilisateur avec le company_id de l'admin connecté
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

                // Phone peut être vide
                $phoneValue = !empty($phone) ? $phone : null;

                $stmt = $this->db->prepare("
                    INSERT INTO users (company_id, name, email, password, role, phone, is_active, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
                ");
                $stmt->execute([
                    $user['company_id'], // SÉCURITÉ: Utiliser le company_id de l'admin
                    $name,
                    $email,
                    $hashedPassword,
                    $role,
                    $phoneValue
                ]);

                // Logger l'action
                $this->logAudit(
                    $user['id'],
                    'user_created',
                    "Utilisateur créé: $name ($email)",
                    $_SERVER['REMOTE_ADDR']
                );

                $this->setFlash('success', 'Utilisateur créé avec succès');
                $this->redirect('users');

            } catch (\Exception $e) {
                error_log('Error creating user: ' . $e->getMessage());

                // En mode debug, afficher l'erreur exacte
                if (APP_DEBUG) {
                    $this->setFlash('error', 'Erreur SQL: ' . $e->getMessage());
                } else {
                    $this->setFlash('error', 'Erreur lors de la création de l\'utilisateur');
                }
                $this->redirect('users/add');
            }
        }

        // Afficher le formulaire
        $this->view('users/add', [
            'title' => 'Ajouter un utilisateur - SBN v1.0',
            'user' => $user
        ]);
    }

    /**
     * Formulaire de modification d'utilisateur
     */
    public function edit() {
        $user = $this->getUser();
        $userId = $this->get('id');

        if (!$user || $user['role'] !== 'admin' || !$userId) {
            $this->redirect('users');
        }

        // SÉCURITÉ: Vérifier que l'utilisateur appartient à la même société
        $stmt = $this->db->prepare("
            SELECT * FROM users
            WHERE id = ? AND company_id = ?
        ");
        $stmt->execute([$userId, $user['company_id']]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$targetUser) {
            $this->setFlash('error', 'Utilisateur non trouvé');
            $this->redirect('users');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Valider le CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token CSRF invalide');
                $this->redirect('users/edit?id=' . $userId);
            }

            // Récupérer les données
            $name = trim($this->post('name'));
            $email = trim($this->post('email'));
            $role = $this->post('role');
            $phone = trim($this->post('phone'));
            $is_active = $this->post('is_active') ? 1 : 0;
            $password = $this->post('password');
            $password_confirm = $this->post('password_confirm');

            // Validation
            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Le nom est requis';
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email invalide';
            }

            // Vérifier si l'email existe déjà (sauf pour cet utilisateur)
            if (!empty($email)) {
                $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $userId]);
                if ($stmt->fetch()) {
                    $errors['email'] = 'Cet email est déjà utilisé';
                }
            }

            if (!in_array($role, ['admin', 'user', 'collaborator', 'client'])) {
                $errors['role'] = 'Rôle invalide';
            }

            // Si un nouveau mot de passe est fourni
            if (!empty($password)) {
                if (strlen($password) < 8) {
                    $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
                }
                if ($password !== $password_confirm) {
                    $errors['password_confirm'] = 'Les mots de passe ne correspondent pas';
                }
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $_POST;
                $this->redirect('users/edit?id=' . $userId);
            }

            try {
                // Phone peut être vide
                $phoneValue = !empty($phone) ? $phone : null;

                // Préparer la requête de mise à jour
                if (!empty($password)) {
                    // Avec changement de mot de passe
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $stmt = $this->db->prepare("
                        UPDATE users
                        SET name = ?, email = ?, password = ?, role = ?, phone = ?, is_active = ?
                        WHERE id = ? AND company_id = ?
                    ");
                    $stmt->execute([$name, $email, $hashedPassword, $role, $phoneValue, $is_active, $userId, $user['company_id']]);
                } else {
                    // Sans changement de mot de passe
                    $stmt = $this->db->prepare("
                        UPDATE users
                        SET name = ?, email = ?, role = ?, phone = ?, is_active = ?
                        WHERE id = ? AND company_id = ?
                    ");
                    $stmt->execute([$name, $email, $role, $phoneValue, $is_active, $userId, $user['company_id']]);
                }

                // Logger l'action
                $this->logAudit(
                    $user['id'],
                    'user_updated',
                    "Utilisateur modifié: $name ($email)",
                    $_SERVER['REMOTE_ADDR']
                );

                $this->setFlash('success', 'Utilisateur modifié avec succès');
                $this->redirect('users');

            } catch (\Exception $e) {
                error_log('Error updating user: ' . $e->getMessage());

                // En mode debug, afficher l'erreur exacte
                if (APP_DEBUG) {
                    $this->setFlash('error', 'Erreur SQL: ' . $e->getMessage());
                } else {
                    $this->setFlash('error', 'Erreur lors de la modification de l\'utilisateur');
                }
                $this->redirect('users/edit?id=' . $userId);
            }
        }

        // Afficher le formulaire
        $this->view('users/edit', [
            'title' => 'Modifier un utilisateur - SBN v1.0',
            'user' => $user,
            'targetUser' => $targetUser
        ]);
    }

    /**
     * Supprimer un utilisateur
     */
    public function delete() {
        $user = $this->getUser();
        $userId = $this->get('id');

        if (!$user || $user['role'] !== 'admin' || !$userId) {
            $this->redirect('users');
        }

        // SÉCURITÉ: Empêcher de se supprimer soi-même
        if ($userId == $user['id']) {
            $this->setFlash('error', 'Vous ne pouvez pas supprimer votre propre compte');
            $this->redirect('users');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Valider le CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token CSRF invalide');
                $this->redirect('users');
            }

            try {
                // SÉCURITÉ: Vérifier que l'utilisateur appartient à la même société
                $stmt = $this->db->prepare("
                    SELECT name, email FROM users
                    WHERE id = ? AND company_id = ?
                ");
                $stmt->execute([$userId, $user['company_id']]);
                $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$targetUser) {
                    $this->setFlash('error', 'Utilisateur non trouvé');
                    $this->redirect('users');
                }

                // Supprimer l'utilisateur
                $stmt = $this->db->prepare("
                    DELETE FROM users
                    WHERE id = ? AND company_id = ?
                ");
                $stmt->execute([$userId, $user['company_id']]);

                // Logger l'action
                $this->logAudit(
                    $user['id'],
                    'user_deleted',
                    "Utilisateur supprimé: {$targetUser['name']} ({$targetUser['email']})",
                    $_SERVER['REMOTE_ADDR']
                );

                $this->setFlash('success', 'Utilisateur supprimé avec succès');

            } catch (\Exception $e) {
                error_log('Error deleting user: ' . $e->getMessage());
                $this->setFlash('error', 'Erreur lors de la suppression de l\'utilisateur');
            }
        }

        $this->redirect('users');
    }

    /**
     * Gérer l'abonnement d'un utilisateur
     */
    public function subscription() {
        $user = $this->getUser();
        $userId = $this->get('id');

        if (!$user || $user['role'] !== 'admin' || !$userId) {
            $this->redirect('users');
        }

        // SÉCURITÉ: Vérifier que l'utilisateur appartient à la même société
        $stmt = $this->db->prepare("
            SELECT * FROM users
            WHERE id = ? AND company_id = ?
        ");
        $stmt->execute([$userId, $user['company_id']]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$targetUser) {
            $this->setFlash('error', 'Utilisateur non trouvé');
            $this->redirect('users');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Valider le CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token CSRF invalide');
                $this->redirect('users/subscription?id=' . $userId);
            }

            $action = $this->post('action');

            if ($action === 'grant_days') {
                $days = (int) $this->post('days');
                $reason = trim($this->post('reason'));

                if ($days > 0) {
                    $result = $this->userModel->grantFreeDays($userId, $days, $reason, $user['id']);
                    if ($result) {
                        $this->setFlash('success', "$days jours offerts avec succès");
                    } else {
                        $this->setFlash('error', 'Erreur lors de l\'ajout des jours gratuits');
                    }
                }

            } elseif ($action === 'suspend') {
                $reason = trim($this->post('reason'));
                $result = $this->userModel->suspendAccount($userId, $reason, $user['id']);
                if ($result) {
                    $this->setFlash('success', 'Compte suspendu avec succès');
                } else {
                    $this->setFlash('error', 'Erreur lors de la suspension du compte');
                }

            } elseif ($action === 'unsuspend') {
                $result = $this->userModel->unsuspendAccount($userId, $user['id']);
                if ($result) {
                    $this->setFlash('success', 'Compte réactivé avec succès');
                } else {
                    $this->setFlash('error', 'Erreur lors de la réactivation du compte');
                }
            }

            $this->redirect('users/subscription?id=' . $userId);
        }

        // Afficher le formulaire
        $this->view('users/subscription', [
            'title' => 'Abonnement - SBN v1.0',
            'user' => $user,
            'targetUser' => $targetUser
        ]);
    }

    /**
     * Réinitialiser le mot de passe d'un utilisateur (Admin)
     */
    public function resetPassword() {
        $user = $this->getUser();
        $userId = $this->get('id');

        if (!$user || $user['role'] !== 'admin' || !$userId) {
            $this->redirect('users');
        }

        // SÉCURITÉ: Vérifier que l'utilisateur appartient à la même société
        $stmt = $this->db->prepare("
            SELECT * FROM users
            WHERE id = ? AND company_id = ?
        ");
        $stmt->execute([$userId, $user['company_id']]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$targetUser) {
            $this->setFlash('error', 'Utilisateur non trouvé');
            $this->redirect('users');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Valider le CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token CSRF invalide');
                $this->redirect('users');
            }

            // Réinitialiser le mot de passe
            $tempPassword = $this->userModel->adminResetPassword($userId, $user['id']);

            if ($tempPassword) {
                // Stocker temporairement le mot de passe dans la session pour l'afficher UNE SEULE FOIS
                $_SESSION['temp_password'] = $tempPassword;
                $_SESSION['temp_password_user'] = $targetUser['name'];

                $this->setFlash('success', 'Mot de passe réinitialisé avec succès');
                $this->redirect('users');
            } else {
                $this->setFlash('error', 'Erreur lors de la réinitialisation du mot de passe');
                $this->redirect('users');
            }
        }
    }

    /**
     * Logger un événement d'audit
     */
    private function logAudit($userId, $action, $details, $ipAddress) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO audit_logs (user_id, action, details, ip_address, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $action, $details, $ipAddress]);
        } catch (\Exception $e) {
            error_log('Audit log error: ' . $e->getMessage());
        }
    }
}
