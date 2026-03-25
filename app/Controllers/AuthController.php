<?php
/**
 * SBN v1.0 - Contrôleur d'authentification
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Page de connexion
     */
    public function login() {
        // Si déjà connecté, rediriger vers le dashboard
        if ($this->getUser()) {
            $this->redirect('dashboard');
        }

        if ($this->isPost()) {
            // Valider le CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
                $this->redirect('login');
            }

            $email = $this->post('email');
            $password = $this->post('password');
            $remember = $this->post('remember') === 'on';

            // Validation
            $errors = $this->validate([
                'email' => $email,
                'password' => $password
            ], [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if (!empty($errors)) {
                $this->view('auth/login', [
                    'errors' => $errors,
                    'email' => $email,
                    'csrf_token' => $this->generateCsrfToken()
                ], null);
                return;
            }

            // Authentifier
            $user = $this->userModel->authenticate($email, $password);

            if (!$user) {
                $this->setFlash('error', 'Email ou mot de passe incorrect.');
                $this->view('auth/login', [
                    'email' => $email,
                    'csrf_token' => $this->generateCsrfToken()
                ], null);
                return;
            }

            // Créer la session
            $this->createUserSession($user);

            // Gérer "Se souvenir de moi"
            if ($remember) {
                $this->setRememberMeCookie($user['id']);
            }

            // Rediriger
            $returnUrl = $this->get('return', 'dashboard');
            $this->setFlash('success', 'Bienvenue ' . htmlspecialchars($user['name']) . ' !');
            $this->redirect($returnUrl);
        }

        // Afficher le formulaire de connexion
        $this->view('auth/login', [
            'csrf_token' => $this->generateCsrfToken()
        ], null);
    }

    /**
     * Inscription
     */
    public function register() {
        // Si déjà connecté, rediriger vers le dashboard
        if ($this->getUser()) {
            $this->redirect('dashboard');
        }

        if ($this->isPost()) {
            // Valider le CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
                $this->redirect('register');
            }

            $name = $this->post('name');
            $email = $this->post('email');
            $password = $this->post('password');
            $passwordConfirm = $this->post('password_confirm');
            $companyName = $this->post('company_name');
            $gdprConsent = $this->post('gdpr_consent') === 'on';

            // Validation
            $errors = $this->validate([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'company_name' => $companyName
            ], [
                'name' => 'required|min:2|max:100',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:' . PASSWORD_MIN_LENGTH,
                'company_name' => 'required|min:2|max:100'
            ]);

            // Vérifier la confirmation du mot de passe
            if ($password !== $passwordConfirm) {
                $errors['password_confirm'][] = 'Les mots de passe ne correspondent pas.';
            }

            // Vérifier le consentement RGPD
            if (!$gdprConsent) {
                $errors['gdpr_consent'][] = 'Vous devez accepter la politique de confidentialité.';
            }

            if (!empty($errors)) {
                $this->view('auth/register', [
                    'errors' => $errors,
                    'name' => $name,
                    'email' => $email,
                    'company_name' => $companyName,
                    'csrf_token' => $this->generateCsrfToken()
                ], null);
                return;
            }

            // Créer la société
            $companyModel = new \App\Models\Company();
            $companyId = $companyModel->create([
                'name' => $companyName,
                'is_active' => 1
            ]);

            if (!$companyId) {
                $this->setFlash('error', 'Erreur lors de la création de la société.');
                $this->redirect('register');
            }

            // Créer l'utilisateur
            $userId = $this->userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'company_id' => $companyId,
                'role' => 'admin', // Premier utilisateur = admin de sa société
                'is_active' => 1,
                'gdpr_consent' => 1,
                'gdpr_consent_date' => date('Y-m-d H:i:s')
            ]);

            if (!$userId) {
                $this->setFlash('error', 'Erreur lors de la création du compte.');
                $this->redirect('register');
            }

            $this->setFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');
            $this->redirect('login');
        }

        // Afficher le formulaire d'inscription
        $this->view('auth/register', [
            'csrf_token' => $this->generateCsrfToken()
        ], null);
    }

    /**
     * Mot de passe oublié
     */
    public function forgotPassword() {
        if ($this->isPost()) {
            // Valider le CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
                $this->redirect('forgot-password');
            }

            $email = $this->post('email');

            // Validation
            $errors = $this->validate(['email' => $email], ['email' => 'required|email']);

            if (!empty($errors)) {
                $this->view('auth/forgot-password', [
                    'errors' => $errors,
                    'email' => $email,
                    'csrf_token' => $this->generateCsrfToken()
                ], null);
                return;
            }

            // Générer le token
            $token = $this->userModel->createPasswordResetToken($email);

            if ($token) {
                // TODO: Envoyer l'email avec le lien de réinitialisation
                // Pour le moment, afficher le token (en dev uniquement)
                if (APP_DEBUG) {
                    $resetLink = APP_URL . '/reset-password?token=' . $token;
                    $this->setFlash('success', 'Lien de réinitialisation (DEV): ' . $resetLink);
                } else {
                    $this->setFlash('success', 'Un email avec les instructions a été envoyé.');
                }
            } else {
                // Ne pas révéler si l'email existe ou non (sécurité)
                $this->setFlash('success', 'Si cet email existe, un lien de réinitialisation a été envoyé.');
            }

            $this->redirect('login');
        }

        $this->view('auth/forgot-password', [
            'csrf_token' => $this->generateCsrfToken()
        ], null);
    }

    /**
     * Réinitialisation du mot de passe
     */
    public function resetPassword() {
        $token = $this->get('token');

        if (!$token) {
            $this->setFlash('error', 'Token invalide.');
            $this->redirect('login');
        }

        // Vérifier le token
        $user = $this->userModel->verifyResetToken($token);

        if (!$user) {
            $this->setFlash('error', 'Token invalide ou expiré.');
            $this->redirect('login');
        }

        if ($this->isPost()) {
            // Valider le CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
                $this->redirect('reset-password?token=' . $token);
            }

            $password = $this->post('password');
            $passwordConfirm = $this->post('password_confirm');

            // Validation
            $errors = $this->validate(['password' => $password], ['password' => 'required|min:' . PASSWORD_MIN_LENGTH]);

            if ($password !== $passwordConfirm) {
                $errors['password_confirm'][] = 'Les mots de passe ne correspondent pas.';
            }

            if (!empty($errors)) {
                $this->view('auth/reset-password', [
                    'errors' => $errors,
                    'token' => $token,
                    'csrf_token' => $this->generateCsrfToken()
                ], null);
                return;
            }

            // Réinitialiser le mot de passe
            if ($this->userModel->resetPassword($user['id'], $password)) {
                $this->setFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
                $this->redirect('login');
            } else {
                $this->setFlash('error', 'Erreur lors de la réinitialisation du mot de passe.');
            }
        }

        $this->view('auth/reset-password', [
            'token' => $token,
            'csrf_token' => $this->generateCsrfToken()
        ], null);
    }

    /**
     * Déconnexion
     */
    public function logout() {
        // Logger la déconnexion
        $user = $this->getUser();
        if ($user) {
            $this->logAudit($user['id'], 'user_logout', 'Déconnexion', $_SERVER['REMOTE_ADDR']);
        }

        // Détruire la session
        session_destroy();

        // Supprimer le cookie "remember me"
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        $this->setFlash('success', 'Vous avez été déconnecté avec succès.');
        $this->redirect('login');
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

    /**
     * Créer la session utilisateur
     */
    private function createUserSession($user) {
        // Régénérer l'ID de session (sécurité)
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['company_id'] = $user['company_id'];
        $_SESSION['last_activity'] = time();
    }

    /**
     * Définir le cookie "remember me"
     */
    private function setRememberMeCookie($userId) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = time() + (30 * 24 * 60 * 60); // 30 jours

        // Sauvegarder le token dans la base de données
        $this->userModel->update($userId, [
            'remember_token' => hash('sha256', $token)
        ]);

        // Définir le cookie
        setcookie('remember_token', $token, $expiresAt, '/', '', false, true);
    }
}
