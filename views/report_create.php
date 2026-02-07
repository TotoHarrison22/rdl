<?php
// Fetch necessary data for dropdowns
$pdo = getDB();
$drivers = $pdo->query("SELECT id_usuario, nombre FROM usuarios WHERE rol = 'Despacho'")->fetchAll();
$vehicles = $pdo->query("SELECT id_vehiculo, placa, marca, modelo FROM vehiculos WHERE estado = 'Activo'")->fetchAll();
$agencies = $pdo->query("SELECT id_agencia, nombre_agencia FROM agencias WHERE estado != 'No recomendada'")->fetchAll(); // Pre-fetch agencies for autocomplete
?>

<div class="container">
    <h2>Nuevo Reporte Diario de Despacho</h2>

    <form id="dispatchForm" method="POST" action="src/ReportController.php" enctype="multipart/form-data">

        <!-- 1. General Information -->
        <div class="card">
            <div class="card-header">1. Información General</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Hora Salida</label>
                        <input type="time" name="hora_salida" class="form-control" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Hora Retorno (Estimada)</label>
                        <input type="time" name="hora_retorno" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Chofer</label>
                        <select name="id_chofer" class="form-control" required>
                            <option value="">Seleccione Chofer</option>
                            <?php foreach($drivers as $d): ?>
                                <option value="<?= $d['id_usuario'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Copiloto</label>
                        <select name="id_copiloto" class="form-control" required>
                            <option value="">Seleccione Copiloto</option>
                            <?php foreach($drivers as $d): ?>
                                <option value="<?= $d['id_usuario'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Vehículo</label>
                        <select name="id_vehiculo" class="form-control" required>
                            <option value="">Seleccione Vehículo</option>
                            <?php foreach($vehicles as $v): ?>
                                <option value="<?= $v['id_vehiculo'] ?>"><?= htmlspecialchars($v['placa'] . ' - ' . $v['marca'] . ' ' . $v['modelo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Ruta / Zonas</label>
                        <input type="text" name="ruta_descripcion" class="form-control" placeholder="Ej: Lima Norte, Comas, Carabayllo">
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Visitas a Agencias (Dynamic) -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>2. Registro de Agencias Visitadas</span>
                <button type="button" class="btn btn-sm btn-success" onclick="addAgencyRow()">+ Agregar Agencia</button>
            </div>
            <div class="card-body" id="agencies-container">
                <!-- Rows will be added here by JS -->
                <div class="alert alert-info" id="no-agency-msg">Presione "+ Agregar Agencia" para registrar visitas.</div>
            </div>
        </div>

        <!-- 3. Estado del Vehículo -->
        <div class="card">
            <div class="card-header">3. Estado del Vehículo (Checklist)</div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $checks = ['embrague', 'frenos', 'aire_acondicionado', 'luces', 'llantas', 'ruidos', 'alertas_tablero'];
                    foreach($checks as $check):
                        $label = ucfirst(str_replace('_', ' ', $check));
                    ?>
                    <div class="col-md-4 form-group">
                        <label><?= $label ?></label>
                        <select name="vehicle_check[<?= $check ?>]" class="form-control">
                            <option value="OK">OK</option>
                            <option value="Revisar">Revisar</option>
                            <option value="Urgente">Urgente</option>
                        </select>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-group">
                    <label>Observación General Vehículo</label>
                    <textarea name="vehicle_obs" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Evidencia Fotográfica (Opcional)</label>
                    <input type="file" name="vehicle_evidence" class="form-control-file">
                </div>
            </div>
        </div>

        <!-- 4. Incidencias -->
        <div class="card">
            <div class="card-header">4. Incidencias (Opcional)</div>
            <div class="card-body">
                <div class="form-group">
                    <label>Tipo de Incidencia</label>
                    <select name="incidencia_tipo" class="form-control">
                        <option value="">Ninguna</option>
                        <option value="Daño producto">Daño producto</option>
                        <option value="Rechazo carga">Rechazo carga</option>
                        <option value="Problema documentación">Problema documentación</option>
                        <option value="Accidente">Accidente</option>
                        <option value="Retraso">Retraso</option>
                        <option value="Mal servicio agencia">Mal servicio agencia</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Descripción Incidencia</label>
                    <textarea name="incidencia_desc" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Adjuntar Evidencia (Opcional)</label>
                    <input type="file" name="incidencia_evidence" class="form-control-file">
                </div>
            </div>
        </div>

        <!-- 5. Recomendaciones -->
        <div class="card">
            <div class="card-header">5. Recomendaciones del Personal</div>
            <div class="card-body">
                <div class="form-group">
                    <label>Tipo Recomendación</label>
                    <select name="recomendacion_tipo" class="form-control">
                        <option value="Mejora operativa">Mejora operativa</option>
                        <option value="Agencia recomendada">Agencia recomendada</option>
                        <option value="Agencia evitar">Agencia a evitar</option>
                        <option value="Mejores horarios">Mejores horarios</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Detalle</label>
                    <textarea name="recomendacion_desc" class="form-control" rows="2" required></textarea>
                </div>
            </div>
        </div>

        <div class="text-center mb-5">
            <button type="submit" class="btn btn-primary btn-lg">Guardar Reporte</button>
        </div>
    </form>
</div>

<!-- Template for Agency Row (Hidden) -->
<template id="agency-row-template">
    <div class="agency-row border p-3 mb-3 rounded" style="background-color: #f9f9f9;">
        <div class="d-flex justify-content-between">
            <h5>Agencia #<span class="agency-index"></span></h5>
            <button type="button" class="btn btn-danger btn-sm remove-agency">Eliminar</button>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label>Agencia</label>
                <select name="agencies[INDEX][id_agencia]" class="form-control agency-select" required>
                    <option value="">Seleccione Agencia</option>
                    <!-- Options populated by JS -->
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label>Tipo Visita</label>
                <select name="agencies[INDEX][tipo_visita]" class="form-control">
                    <option value="Despacho">Despacho</option>
                    <option value="Consulta">Consulta</option>
                    <option value="Recojo">Recojo</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 form-group">
                <label>Tiempo Espera (min)</label>
                <input type="number" name="agencies[INDEX][tiempo_espera]" class="form-control" placeholder="0">
            </div>
            <div class="col-md-3 form-group">
                <label>Congestión</label>
                <select name="agencies[INDEX][congestion]" class="form-control">
                    <option value="Bajo">Baja</option>
                    <option value="Medio">Media</option>
                    <option value="Alto">Alta</option>
                </select>
            </div>
            <div class="col-md-3 form-group">
                <label>¿Eficiente?</label>
                <select name="agencies[INDEX][eficiente]" class="form-control">
                    <option value="1">Sí</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="col-md-3 form-group">
                <label>Tamaño Pedido</label>
                <select name="agencies[INDEX][tamano_pedido]" class="form-control">
                    <option value="Pequeno">Pequeño</option>
                    <option value="Mediano">Mediano</option>
                    <option value="Grande">Grande</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <label><strong>Condiciones del Servicio</strong></label>
                <div class="d-flex flex-wrap border p-2 bg-white rounded">
                    <label class="mr-3"><input type="checkbox" name="agencies[INDEX][checks][hoja_seguridad]" value="1"> Pide Hojas Seguridad</label>
                    <label class="mr-3"><input type="checkbox" name="agencies[INDEX][checks][doc_especial]" value="1"> Doc. Especial</label>
                    <label class="mr-3"><input type="checkbox" name="agencies[INDEX][checks][cobro_previo]" value="1"> Cobra Antes</label>
                    <label class="mr-3"><input type="checkbox" name="agencies[INDEX][checks][restriccion_producto]" value="1"> Restric. Prod.</label>
                    <label class="mr-3"><input type="checkbox" name="agencies[INDEX][checks][rechazo_carga]" value="1"> Rechazo Carga</label>
                    <label class="mr-3"><input type="checkbox" name="agencies[INDEX][checks][cambio_politica]" value="1"> Cambio Política</label>
                </div>
            </div>
        </div>

        <div class="form-group mt-2">
            <label>Condiciones de Acceso (Opcional)</label>
            <div class="d-flex flex-wrap">
                <label class="mr-3"><input type="checkbox" name="agencies[INDEX][condiciones][]" value="Calle cerrada"> Calle cerrada</label> &nbsp;
                <label class="mr-3"><input type="checkbox" name="agencies[INDEX][condiciones][]" value="Obras civiles"> Obras</label> &nbsp;
                <label class="mr-3"><input type="checkbox" name="agencies[INDEX][condiciones][]" value="Acceso restringido"> Acceso restringido</label> &nbsp;
                <label class="mr-3"><input type="checkbox" name="agencies[INDEX][condiciones][]" value="Zona peligrosa"> Zona peligrosa</label>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>Cobertura Logística</label>
                <input type="text" name="agencies[INDEX][cobertura_destinos]" class="form-control" placeholder="Destinos cubiertos (separar por comas)">
            </div>
            <div class="col-md-6 form-group">
                <label>Observaciones</label>
                <input type="text" name="agencies[INDEX][observaciones]" class="form-control" placeholder="Observaciones específicas">
            </div>
        </div>
    </div>
</template>

<script>
    // Pass PHP agencies to JS
    const availableAgencies = <?= json_encode($agencies) ?>;
</script>
