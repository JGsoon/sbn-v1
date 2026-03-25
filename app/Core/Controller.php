<?php
/**
 * SBN v1.0 - Contrôleur de base
 *
 * @package SBN
 * @version 1.0.0
 */

namespace App\Core;

use Config\Database;  // ← AJOUT de cette ligne

class Controller {
    protected $db;

    /**
     * Constructeur
     */
    public function __construct() {
        // Démarrer la session si pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialiser la connexion à la base de données
        $this->db = Database::getInstance()->getConnection();  // ← RETIRÉ le \
        
        // Générer automatiquement le token CSRF pour tous les formulaires
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Charger une vue
     *
     * @param string $view Nom de la vue
     * @param array $data Données à passer à la vue
     * @param string $layout Layout à utiliser (null = pas de layout)
     */
    protected function view($view, $data = [], $layout = 'main') {
        // Extraire les données pour les rendre disponibles dans la vue
        extract($data);

        // Démarrer la capture de sortie
        ob_start();

        // Charger la vue
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("La vue $view est introuvable.");
        }

        // Récupérer le contenu de la vue
        $content = ob_get_clean();

        // Si un layout est spécifié, l'utiliser
        if ($layout) {
            $layoutFile = VIEWS_PATH . '/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    /**
     * Rediriger vers une URL
     *
     * @param string $url URL de redirection
     */
    protected function redirect($url) {
        header('Location: ' . APP_URL . '/' . $url);
        exit;
    }

    /**
     * Retourner du JSON
     *
     * @param mixed $data Données à encoder en JSON
     * @param int $statusCode Code HTTP
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Vérifier si la requête est en POST
     *
     * @return bool
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Vérifier si la requête est en GET
     *
     * @return bool
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Obtenir les données POST
     *
     * @param string $key Clé à récupérer (null = toutes)
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Obtenir les données GET
     *
     * @param string $key Clé à récupérer (null = toutes)
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Définir un message flash
     *
     * @param string $type Type de message (success, error, warning, info)
     * @param string $message Message
     */
    protected function setFlash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Obtenir et supprimer un message flash
     *
     * @param string $type Type de message
     * @return string|null
     */
    protected function getFlash($type) {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }

    /**
     * Vérifier le token CSRF
     *
     * @return bool
     */
    protected function validateCsrf() {
        $token = $this->post(CSRF_TOKEN_NAME);

        if (!$token || !isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Générer un token CSRF
     *
     * @return string
     */
    protected function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Obtenir l'utilisateur connecté
     *
     * @return array|null
     */
    protected function getUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'] ?? '',
            'name' => $_SESSION['user_name'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'user',
            'company_id' => $_SESSION['company_id'] ?? null
        ];
    }

    /**
     * Vérifier si l'utilisateur est admin
     *
     * @return bool
     */
    protected function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Valider les données
     *
     * @param array $data Données à valider
     * @param array $rules Règles de validation
     * @return array Erreurs de validation
     */
    protected function validate($data, $rules) {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $fieldRules = explode('|', $fieldRules);

            foreach ($fieldRules as $rule) {
                $params = [];
                if (strpos($rule, ':') !== false) {
                    list($rule, $param) = explode(':', $rule, 2);
                    $params = explode(',', $param);
                }

                $value = $data[$field] ?? null;

                switch ($rule) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = "Le champ $field est requis.";
                        }
                        break;

                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "Le champ $field doit être une adresse email valide.";
                        }
                        break;

                    case 'min':
                        if (!empty($value) && strlen($value) < $params[0]) {
                            $errors[$field][] = "Le champ $field doit contenir au moins {$params[0]} caractères.";
                        }
                        break;

                    case 'max':
                        if (!empty($value) && strlen($value) > $params[0]) {
                            $errors[$field][] = "Le champ $field ne peut pas contenir plus de {$params[0]} caractères.";
                        }
                        break;

                    case 'unique':
                        // $params[0] = table, $params[1] = column
                        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$params[0]} WHERE {$params[1]} = ?");
                        $stmt->execute([$value]);
                        if ($stmt->fetchColumn() > 0) {
                            $errors[$field][] = "Cette valeur pour $field existe déjà.";
                        }
                        break;
                }
            }
        }

        return $errors;
    }
}
