<?php
/**
 * SBN v1.0 - Contrôleur des pages légales
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Core\Controller;

class LegalController extends Controller {

    /**
     * Politique de confidentialité
     */
    public function privacy() {
        $this->view('legal/privacy', [
            'title' => 'Politique de confidentialité - SBN v1.0'
        ], 'main');
    }

    /**
     * Conditions d'utilisation
     */
    public function terms() {
        $this->view('legal/terms', [
            'title' => 'Conditions d\'utilisation - SBN v1.0'
        ], 'main');
    }
}
