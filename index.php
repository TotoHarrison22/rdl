<?php
// Reporte Diario de Despacho
// Entry Point

require_once 'config/database.php';
require_once 'src/Auth.php';

// Start Session
session_start();

// Basic Routing
$page = $_GET['page'] ?? 'login';
$action = $_GET['action'] ?? null;

// Handle Form Submissions (Controller Logic)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === 'auth' && $action === 'login') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $auth = new Auth();
        if ($auth->login($username, $password)) {
            header('Location: index.php?page=dashboard');
            exit;
        } else {
            $error_message = "Credenciales incorrectas.";
            $page = 'login'; // Show login again with error
        }
    }
}

// Redirect if already logged in and trying to access login
if ($page === 'login' && isset($_SESSION['user_id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Logout
if ($page === 'logout') {
    $auth = new Auth();
    $auth->logout();
    header('Location: index.php?page=login');
    exit;
}

// Protect Routes
if ($page !== 'login' && $page !== 'auth' && !isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Render View
include 'views/layout/header.php';

if (isset($error_message)) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
}

switch ($page) {
    case 'login':
        include 'views/login.php';
        break;
    case 'dashboard':
        include 'views/dashboard.php';
        break;
    case 'report_create':
        include 'views/report_create.php';
        break;
    case 'knowledge_base':
        include 'views/knowledge_base.php';
        break;
    default:
        echo "<h1>404 Página no encontrada</h1>";
        break;
}

include 'views/layout/footer.php';
