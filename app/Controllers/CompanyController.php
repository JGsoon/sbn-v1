<?php
/**
 * SBN v1.0 - Contrôleur Sociétés
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Company;

class CompanyController extends Controller {
    private $companyModel;

    public function __construct() {
        parent::__construct();
        $this->companyModel = new Company();
    }

    /**
     * Liste des sociétés
     */
    public function index() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('login');
        }

        // Seul un admin peut voir toutes les sociétés
        // Un user normal voit seulement sa société
        if ($user['role'] === 'admin') {
            $companies = $this->companyModel->findAll('name ASC');
        } else {
            $companies = [$this->companyModel->findById($user['company_id'])];
        }

        $this->view('companies/index', [
            'title' => 'Sociétés - SBN v1.0',
            'user' => $user,
            'companies' => $companies
        ]);
    }

    /**
     * Modifier une société
     */
    public function edit() {
        $user = $this->getUser();

        if (!$user) {
            $this->redirect('companies');
        }

        // Utilisateur normal ne peut éditer QUE sa société
        $companyId = $user['company_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->setFlash('error', 'Token CSRF invalide');
                $this->redirect('companies/edit');
            }

            $name = trim($this->post('name'));
            $address = trim($this->post('address'));
            $phone = trim($this->post('phone'));
            $email = trim($this->post('email'));

            $errors = [];
            if (empty($name)) {
                $errors['name'] = 'Le nom est requis';
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $_POST;
                $this->redirect('companies/edit');
            }

            try {
                $stmt = $this->db->prepare("
                    UPDATE companies
                    SET name = ?, address = ?, phone = ?, email = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $address, $phone, $email, $companyId]);

                $this->setFlash('success', 'Société modifiée avec succès');
                $this->redirect('companies');
            } catch (\Exception $e) {
                error_log('Error updating company: ' . $e->getMessage());
                $this->setFlash('error', 'Erreur lors de la modification');
                $this->redirect('companies/edit');
            }
        }

        // Récupérer les infos de la société
        $company = $this->companyModel->findById($companyId);

        $this->view('companies/edit', [
            'title' => 'Modifier ma société - SBN v1.0',
            'user' => $user,
            'company' => $company
        ]);
    }

    /**
     * Supprimer une société
     */
    public function delete() {
        $user = $this->getUser();
        $id = $this->get('id');

        if (!$user || $user['role'] !== 'admin' || !$id) {
            $this->redirect('companies');
        }

        // TODO: Implémenter la suppression
        $this->setFlash('info', 'Fonctionnalité en cours de développement');
        $this->redirect('companies');
    }
}
