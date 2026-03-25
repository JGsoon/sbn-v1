<?php
/**
 * SBN v1.0 - Contrôleur Backups
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Core\Controller;

class BackupController extends Controller {

    /**
     * Liste des sauvegardes
     */
    public function index() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        // Récupérer les sauvegardes de la société
        $stmt = $this->db->prepare("
            SELECT b.*, d.name as device_name
            FROM backups b
            LEFT JOIN backup_devices d ON b.device_id = d.id
            WHERE b.company_id = ?
            ORDER BY b.start_time DESC
            LIMIT 50
        ");
        $stmt->execute([$user['company_id']]);
        $backups = $stmt->fetchAll();

        $this->view('backups/index', [
            'title' => 'Sauvegardes - SBN v1.0',
            'user' => $user,
            'backups' => $backups
        ]);
    }

    /**
     * Détails d'une sauvegarde
     */
    public function detail() {
        $user = $this->getUser();
        $id = $this->get('id');

        if (!$user || !$id) {
            $this->redirect('backups');
        }

        // TODO: Implémenter la vue détaillée
        $this->redirect('backups');
    }

    /**
     * Historique des sauvegardes
     */
    public function history() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        // TODO: Implémenter l'historique complet
        $this->redirect('backups');
    }
}
