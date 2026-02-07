<?php
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $pdo;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->pdo = getDB();
    }

    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE nombre = ? AND estado = 'Activo' LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_role'] = $user['rol'];
            return true;
        }
        return false;
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    public function requireRole($allowed_roles) {
        if (!$this->isAuthenticated()) {
            header('Location: index.php?page=login');
            exit;
        }

        if (!in_array($_SESSION['user_role'], $allowed_roles)) {
            die("Acceso denegado: Rol insuficiente.");
        }
    }
}
