<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Diario de Despacho</title>
    <link rel="stylesheet" href="public/css/style.css">
    <!-- FontAwesome for icons (optional, using CDN if available, otherwise fallback) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container navbar">
            <a href="index.php" class="navbar-brand">RDL System</a>
            <nav class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="index.php?page=dashboard">Dashboard</a>
                    <a href="index.php?page=report_create">Nuevo Reporte</a>
                    <a href="index.php?page=knowledge_base">Base Conocimiento</a>
                    <a href="index.php?page=logout">Salir (<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>)</a>
                <?php else: ?>
                    <a href="index.php?page=login">Ingresar</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="container mt-3">
