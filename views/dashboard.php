<?php
$pdo = getDB();

// KPIs
$totalReports = $pdo->query("SELECT COUNT(*) FROM reportes_despacho")->fetchColumn();
$avgWaitTime = round($pdo->query("SELECT AVG(tiempo_espera_minutos) FROM visitas_agencia")->fetchColumn(), 0);
$totalIncidents = $pdo->query("SELECT COUNT(*) FROM incidencias")->fetchColumn();
$efficiencyRate = 0;
$totalVisits = $pdo->query("SELECT COUNT(*) FROM visitas_agencia")->fetchColumn();
if ($totalVisits > 0) {
    $efficientVisits = $pdo->query("SELECT COUNT(*) FROM visitas_agencia WHERE eficiencia_envio = 1")->fetchColumn();
    $efficiencyRate = round(($efficientVisits / $totalVisits) * 100, 1);
}

// Recent Incidents (Updated for nullable id_visita)
$recentIncidents = $pdo->query("
    SELECT i.tipo_incidencia, i.descripcion, r.fecha, a.nombre_agencia
    FROM incidencias i
    LEFT JOIN visitas_agencia v ON i.id_visita = v.id_visita
    LEFT JOIN agencias a ON v.id_agencia = a.id_agencia
    LEFT JOIN reportes_despacho r ON (i.id_reporte = r.id_reporte OR v.id_reporte = r.id_reporte)
    ORDER BY i.fecha_registro DESC LIMIT 5
")->fetchAll();

// Vehicle Alerts (Urgent Maintenance)
$vehicleAlerts = $pdo->query("
    SELECT v.placa, e.observacion_general, r.fecha
    FROM estado_vehiculo_reporte e
    JOIN reportes_despacho r ON e.id_reporte = r.id_reporte
    JOIN vehiculos v ON r.id_vehiculo = v.id_vehiculo
    WHERE e.embrague = 'Urgente' OR e.frenos = 'Urgente' OR e.aire_acondicionado = 'Urgente'
       OR e.luces = 'Urgente' OR e.llantas = 'Urgente' OR e.ruidos = 'Urgente' OR e.alertas_tablero = 'Urgente'
    ORDER BY r.fecha DESC LIMIT 5
")->fetchAll();

// Top Agencies (Lowest Wait Time)
$topAgencies = $pdo->query("
    SELECT a.nombre_agencia, AVG(v.tiempo_espera_minutos) as avg_wait
    FROM visitas_agencia v
    JOIN agencias a ON v.id_agencia = a.id_agencia
    GROUP BY a.id_agencia
    ORDER BY avg_wait ASC
    LIMIT 5
")->fetchAll();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Dashboard Gerencial</h2>
        <div>
            <a href="index.php?page=report_create" class="btn btn-success">+ Nuevo Reporte</a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center text-white bg-primary mb-3">
                <div class="card-header">Reportes Totales</div>
                <div class="card-body">
                    <h3 class="card-title"><?= $totalReports ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center text-white bg-success mb-3">
                <div class="card-header">Eficiencia Envíos</div>
                <div class="card-body">
                    <h3 class="card-title"><?= $efficiencyRate ?>%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center text-white bg-warning mb-3">
                <div class="card-header">Tiempo Espera Prom.</div>
                <div class="card-body">
                    <h3 class="card-title"><?= $avgWaitTime ?> min</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center text-white bg-danger mb-3">
                <div class="card-header">Incidencias</div>
                <div class="card-body">
                    <h3 class="card-title"><?= $totalIncidents ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Section -->
    <?php if (!empty($vehicleAlerts)): ?>
    <div class="alert alert-danger">
        <strong>¡Alertas de Mantenimiento Urgente!</strong>
        <ul>
            <?php foreach($vehicleAlerts as $alert): ?>
                <li><?= htmlspecialchars($alert['placa']) ?> (<?= htmlspecialchars($alert['fecha']) ?>): <?= htmlspecialchars($alert['observacion_general'] ?: 'Revisión requerida') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Recent Incidents -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Últimas Incidencias</div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Agencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentIncidents as $inc): ?>
                            <tr>
                                <td><?= htmlspecialchars($inc['fecha'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($inc['tipo_incidencia']) ?></td>
                                <td><?= htmlspecialchars($inc['nombre_agencia'] ?? 'General') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($recentIncidents)): ?>
                            <tr><td colspan="3" class="text-center">Sin incidencias recientes</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Agencies -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Top Agencias (Menor Espera)</div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Agencia</th>
                                <th>Tiempo Promedio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($topAgencies as $ag): ?>
                            <tr>
                                <td><?= htmlspecialchars($ag['nombre_agencia']) ?></td>
                                <td><?= round($ag['avg_wait'], 1) ?> min</td>
                            </tr>
                            <?php endforeach; ?>
                             <?php if(empty($topAgencies)): ?>
                            <tr><td colspan="2" class="text-center">No hay datos suficientes</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
