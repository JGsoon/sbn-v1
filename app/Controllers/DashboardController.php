<?php
/**
 * SBN v1.0 - Contrôleur Dashboard
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\NasDevice;

class DashboardController extends Controller {

    /**
     * Page d'accueil du dashboard
     */
    public function index() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        // Vérifier le statut de l'abonnement
        $userModel = new User();
        if (!$userModel->checkSubscriptionStatus($user['id'])) {
            $this->setFlash('error', 'Votre abonnement a expiré. Veuillez contacter l\'administrateur.');
            $this->redirect('settings/profile');
        }

        // Obtenir les sociétés accessibles par cet utilisateur
        $accessibleCompanies = $userModel->getAccessibleCompanies($user['id']);

        // 🔴 VÉRIFICATION AJOUTÉE
        if (empty($accessibleCompanies)) {
            $this->view('dashboard/index', [
                'title' => 'Dashboard - SBN v1.0',
                'user' => $user,
                'companies_data' => [],
                'global_stats' => [
                    'companies_count' => 0,
                    'nas_count' => 0,
                    'devices_count' => 0,
                    'total_backups' => 0,
                    'success_backups' => 0,
                    'failed_backups' => 0
                ]
            ]);
            return;
        }

        // Préparer les données par société avec NAS et équipements
        $companiesData = [];

        foreach ($accessibleCompanies as $company) {
            // Vérifier le niveau d'accès
            $accessLevel = $userModel->canAccessCompany($user['id'], $company['id']);

            // Obtenir les NAS de cette société
            $nasModel = new NasDevice();

            // Récupérer les NAS avec leurs statistiques
            $nasList = $nasModel->getWithBackupDevices($company['id']);

            // Pour chaque NAS, récupérer les équipements et leurs dernières sauvegardes
            foreach ($nasList as &$nas) {
                // Récupérer les équipements de ce NAS
                $stmt = $this->db->prepare("
                    SELECT
                        bd.*,
                        (SELECT b.status FROM backups b WHERE b.device_id = bd.id ORDER BY b.start_time DESC LIMIT 1) as last_status,
                        (SELECT b.start_time FROM backups b WHERE b.device_id = bd.id ORDER BY b.start_time DESC LIMIT 1) as last_backup_time,
                        (SELECT b.size_bytes FROM backups b WHERE b.device_id = bd.id AND b.status = 'success' ORDER BY b.start_time DESC LIMIT 1) as last_size,
                        (SELECT COUNT(*) FROM backups b WHERE b.device_id = bd.id AND b.status = 'success') as success_count,
                        (SELECT COUNT(*) FROM backups b WHERE b.device_id = bd.id AND b.status = 'failed') as failed_count
                    FROM backup_devices bd
                    WHERE bd.nas_device_id = ?
                    AND bd.is_active = 1
                    ORDER BY bd.name
                ");
                $stmt->execute([$nas['id']]);
                $nas['devices'] = $stmt->fetchAll();
            }

            $companiesData[] = [
                'company' => $company,
                'access_level' => $accessLevel,
                'nas_list' => $nasList
            ];
        }

        // 🔴 CORRECTION : Créer la liste d'IDs de manière sécurisée
        $companyIds = array_map(function($c) { 
            return (int)$c['id']; 
        }, $accessibleCompanies);
        
        $placeholders = implode(',', array_fill(0, count($companyIds), '?'));

        // Statistiques globales
        $stmt = $this->db->prepare("
            SELECT
                COUNT(DISTINCT c.id) as companies_count,
                COUNT(DISTINCT n.id) as nas_count,
                COUNT(DISTINCT bd.id) as devices_count,
                COUNT(b.id) as total_backups,
                SUM(CASE WHEN b.status = 'success' THEN 1 ELSE 0 END) as success_backups,
                SUM(CASE WHEN b.status = 'failed' THEN 1 ELSE 0 END) as failed_backups
            FROM companies c
            LEFT JOIN nas_devices n ON c.id = n.company_id AND n.is_active = 1
            LEFT JOIN backup_devices bd ON n.id = bd.nas_device_id AND bd.is_active = 1
            LEFT JOIN backups b ON bd.id = b.device_id
            WHERE c.id IN ($placeholders)
        ");
        $stmt->execute($companyIds);
        $globalStats = $stmt->fetch();

        $this->view('dashboard/index', [
            'title' => 'Dashboard - SBN v1.0',
            'user' => $user,
            'companies_data' => $companiesData,
            'global_stats' => $globalStats
        ]);
    }
}
