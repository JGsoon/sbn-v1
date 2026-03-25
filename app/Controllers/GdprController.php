<?php
/**
 * SBN v1.0 - Contrôleur RGPD
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class GdprController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Exporter les données personnelles (RGPD Article 20)
     */
    public function export() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        // Valider CSRF
        if ($this->isPost() && !$this->validateCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect('settings');
        }

        if ($this->isPost()) {
            // Collecter toutes les données de l'utilisateur
            $userData = $this->userModel->exportUserData($user['id']);

            // Obtenir les données de notifications
            $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $notifications = $stmt->fetchAll();

            // Obtenir les logs d'audit
            $stmt = $this->db->prepare("SELECT * FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 100");
            $stmt->execute([$user['id']]);
            $auditLogs = $stmt->fetchAll();

            // Préparer l'export
            $exportData = [
                'export_date' => date('Y-m-d H:i:s'),
                'user' => $userData,
                'notifications' => $notifications,
                'audit_logs' => $auditLogs
            ];

            // Logger l'export
            $this->userModel->logAction('gdpr_data_export', $user['id']);

            // Générer le JSON
            $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            // Télécharger le fichier
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="mes-donnees-sbn-' . date('Y-m-d') . '.json"');
            header('Content-Length: ' . strlen($json));
            echo $json;
            exit;
        }

        $this->view('gdpr/export', [
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Supprimer le compte et les données personnelles (RGPD Article 17)
     */
    public function delete() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        if ($this->isPost()) {
            // Valider CSRF
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token de sécurité invalide.');
                $this->redirect('settings');
            }

            $password = $this->post('password');
            $confirmation = $this->post('confirmation');

            // Vérifier le mot de passe
            $userDb = $this->userModel->findById($user['id']);
            if (!password_verify($password, $userDb['password'])) {
                $this->setFlash('error', 'Mot de passe incorrect.');
                $this->view('gdpr/delete', [
                    'user' => $user,
                    'csrf_token' => $this->generateCsrfToken()
                ]);
                return;
            }

            // Vérifier la confirmation
            if ($confirmation !== 'SUPPRIMER') {
                $this->setFlash('error', 'Veuillez taper SUPPRIMER pour confirmer.');
                $this->view('gdpr/delete', [
                    'user' => $user,
                    'csrf_token' => $this->generateCsrfToken()
                ]);
                return;
            }

            // Supprimer les données
            if ($this->userModel->deleteUserData($user['id'])) {
                // Détruire la session
                session_destroy();

                // Rediriger avec message
                session_start();
                $this->setFlash('success', 'Votre compte et vos données ont été supprimés avec succès.');
                $this->redirect('login');
            } else {
                $this->setFlash('error', 'Erreur lors de la suppression des données.');
            }
        }

        $this->view('gdpr/delete', [
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
}
