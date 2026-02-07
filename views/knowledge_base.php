<?php
require_once 'src/AgencyController.php';

$controller = new AgencyController();
$search = $_GET['q'] ?? '';
$detailsId = $_GET['id'] ?? null;

$results = [];
if ($search) {
    $results = $controller->searchAgencies($search);
}

$agencyDetails = null;
if ($detailsId) {
    $agencyDetails = $controller->getAgencyDetails($detailsId);
}
?>

<div class="container">
    <h2>Base de Conocimiento Logística</h2>

    <!-- Search Box -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php">
                <input type="hidden" name="page" value="knowledge_base">
                <div class="input-group" style="display:flex;">
                    <input type="text" name="q" class="form-control" placeholder="Buscar agencia por nombre, distrito o zona..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary ml-2">Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results List -->
    <?php if ($search && empty($results)): ?>
        <div class="alert alert-warning">No se encontraron agencias.</div>
    <?php endif; ?>

    <?php if (!empty($results)): ?>
        <div class="card mb-4">
            <div class="card-header">Resultados de Búsqueda</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Agencia</th>
                            <th>Sede / Distrito</th>
                            <th>Zona</th>
                            <th>Accesibilidad</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($results as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['nombre_agencia']) ?></td>
                            <td><?= htmlspecialchars($r['sede'] . ' - ' . $r['distrito']) ?></td>
                            <td><?= htmlspecialchars($r['zona']) ?></td>
                            <td>
                                <span class="badge badge-<?= $r['nivel_accesibilidad'] == 'Alto' ? 'success' : ($r['nivel_accesibilidad'] == 'Medio' ? 'warning' : 'danger') ?>">
                                    <?= htmlspecialchars($r['nivel_accesibilidad']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?page=knowledge_base&q=<?= urlencode($search) ?>&id=<?= $r['id_agencia'] ?>" class="btn btn-sm btn-info">Ver Detalles</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Agency Details -->
    <?php if ($agencyDetails): ?>
        <div class="card border-primary" id="details-section">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><?= htmlspecialchars($agencyDetails['nombre_agencia']) ?></h3>
                <span class="badge badge-light"><?= htmlspecialchars($agencyDetails['estado']) ?></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Información General</h5>
                        <p><strong>Dirección:</strong> <?= htmlspecialchars($agencyDetails['direccion']) ?></p>
                        <p><strong>Horario:</strong> <?= htmlspecialchars($agencyDetails['horario_apertura'] . ' - ' . $agencyDetails['horario_cierre']) ?></p>
                        <p><strong>Zona:</strong> <?= htmlspecialchars($agencyDetails['zona']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Indicadores Logísticos</h5>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Tiempo Espera Promedio
                                <span class="badge badge-primary badge-pill"><?= $agencyDetails['avg_wait_time'] ?> min</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Tasa de Eficiencia
                                <span class="badge badge-success badge-pill"><?= $agencyDetails['efficiency_rate'] ?>%</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Incidencias
                                <span class="badge badge-danger badge-pill"><?= $agencyDetails['incident_count'] ?></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <?php if (!empty($agencyDetails['recent_conditions'])): ?>
                <div class="mt-4">
                    <h5>Condiciones de Acceso Recientes</h5>
                    <ul>
                        <?php foreach($agencyDetails['recent_conditions'] as $cond): ?>
                            <li>
                                <strong><?= htmlspecialchars($cond['tipo_condicion']) ?></strong>
                                (Impacto: <?= htmlspecialchars($cond['nivel_impacto']) ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <script>
            // Scroll to details
            document.getElementById('details-section').scrollIntoView({behavior: 'smooth'});
        </script>
    <?php endif; ?>
</div>
