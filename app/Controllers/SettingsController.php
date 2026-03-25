<?php
/**
 * SBN v1.0 - Contrôleur Paramètres
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class SettingsController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Page paramètres principale
     */
    public function index() {
        $this->redirect('settings/profile');
    }

    /**
     * Profil utilisateur
     */
    public function profile() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        $userData = $this->userModel->findById($user['id']);

        if ($this->isPost()) {
            // Valider CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token de sécurité invalide.');
                $this->redirect('settings/profile');
            }

            $name = $this->post('name');
            $email = $this->post('email');

            // Validation
            $errors = $this->validate([
                'name' => $name,
                'email' => $email
            ], [
                'name' => 'required|min:2|max:100',
                'email' => 'required|email'
            ]);

            if (empty($errors)) {
                // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel)
                $existingUser = $this->userModel->findByEmail($email);
                if ($existingUser && $existingUser['id'] != $user['id']) {
                    $errors['email'][] = 'Cet email est déjà utilisé.';
                }
            }

            if (empty($errors)) {
                if ($this->userModel->update($user['id'], [
                    'name' => $name,
                    'email' => $email,
                    'updated_at' => date('Y-m-d H:i:s')
                ])) {
                    // Mettre à jour la session
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;

                    $this->setFlash('success', 'Profil mis à jour avec succès.');
                    $this->redirect('settings/profile');
                } else {
                    $this->setFlash('error', 'Erreur lors de la mise à jour.');
                }
            }

            $userData['name'] = $name;
            $userData['email'] = $email;
        }

        $this->view('settings/profile', [
            'title' => 'Mon profil - SBN v1.0',
            'user' => $user,
            'userData' => $userData,
            'errors' => $errors ?? [],
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Sécurité (changement de mot de passe)
     */
    public function security() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        if ($this->isPost()) {
            // Valider CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token de sécurité invalide.');
                $this->redirect('settings/security');
            }

            $currentPassword = $this->post('current_password');
            $newPassword = $this->post('new_password');
            $confirmPassword = $this->post('confirm_password');

            // Validation
            $errors = $this->validate([
                'current_password' => $currentPassword,
                'new_password' => $newPassword
            ], [
                'current_password' => 'required',
                'new_password' => 'required|min:' . PASSWORD_MIN_LENGTH
            ]);

            if ($newPassword !== $confirmPassword) {
                $errors['confirm_password'][] = 'Les mots de passe ne correspondent pas.';
            }

            if (empty($errors)) {
                if ($this->userModel->changePassword($user['id'], $currentPassword, $newPassword)) {
                    $this->setFlash('success', 'Mot de passe modifié avec succès.');
                    $this->redirect('settings/security');
                } else {
                    $errors['current_password'][] = 'Mot de passe actuel incorrect.';
                }
            }
        }

        $this->view('settings/security', [
            'title' => 'Sécurité - SBN v1.0',
            'user' => $user,
            'errors' => $errors ?? [],
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Notifications
     */
    public function notifications() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        $this->view('settings/notifications', [
            'title' => 'Notifications - SBN v1.0',
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
}
