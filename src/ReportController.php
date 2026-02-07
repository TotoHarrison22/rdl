<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Auth.php';

session_start();

$auth = new Auth();
if (!$auth->isAuthenticated()) {
    header('Location: ../index.php?page=login');
    exit;
}

function handleFileUpload($fileInputName) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileTmpPath = $_FILES[$fileInputName]['tmp_name'];
        $fileName = $_FILES[$fileInputName]['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'pdf', 'doc', 'docx');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $uploadDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                return 'public/uploads/' . $newFileName;
            }
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDB();

    // 1. General Info
    $fecha = $_POST['fecha'];
    $hora_salida = $_POST['hora_salida'];
    $hora_retorno = $_POST['hora_retorno'] ?: null;
    $id_chofer = $_POST['id_chofer'];
    $id_copiloto = $_POST['id_copiloto'];
    $id_vehiculo = $_POST['id_vehiculo'];
    $ruta_descripcion = $_POST['ruta_descripcion'];
    $usuario_creacion = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // Insert Report
        $stmt = $pdo->prepare("INSERT INTO reportes_despacho (fecha, hora_salida, hora_retorno, id_chofer, id_copiloto, id_vehiculo, ruta_descripcion, usuario_creacion, estado_reporte) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Cerrado')");
        $stmt->execute([$fecha, $hora_salida, $hora_retorno, $id_chofer, $id_copiloto, $id_vehiculo, $ruta_descripcion, $usuario_creacion]);
        $id_reporte = $pdo->lastInsertId();

        // 2. Vehicle Status
        if (isset($_POST['vehicle_check'])) {
            $checks = $_POST['vehicle_check'];
            $obs_general = $_POST['vehicle_obs'] ?? '';
            $evidence_url = handleFileUpload('vehicle_evidence');

            $stmt = $pdo->prepare("INSERT INTO estado_vehiculo_reporte (id_reporte, embrague, frenos, aire_acondicionado, luces, llantas, ruidos, alertas_tablero, observacion_general, evidencia_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $id_reporte,
                $checks['embrague'] ?? 'OK',
                $checks['frenos'] ?? 'OK',
                $checks['aire_acondicionado'] ?? 'OK',
                $checks['luces'] ?? 'OK',
                $checks['llantas'] ?? 'OK',
                $checks['ruidos'] ?? 'OK',
                $checks['alertas_tablero'] ?? 'OK',
                $obs_general,
                $evidence_url
            ]);
        }

        // 3. Agency Visits
        if (isset($_POST['agencies']) && is_array($_POST['agencies'])) {
            $stmtVisit = $pdo->prepare("
                INSERT INTO visitas_agencia (
                    id_reporte, id_agencia, tipo_visita, tiempo_espera_minutos, nivel_congestion,
                    eficiencia_envio, tamano_pedido,
                    check_hoja_seguridad, check_doc_especial, check_cobro_previo,
                    check_restriccion_producto, check_rechazo_carga, check_cambio_politica,
                    observaciones
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmtCond = $pdo->prepare("INSERT INTO condiciones_acceso (id_visita, tipo_condicion, nivel_impacto, recurrencia) VALUES (?, ?, 'Medio', 'Temporal')");
            $stmtCov = $pdo->prepare("INSERT INTO cobertura_agencia (id_visita, destino_cubierto, estado_cobertura) VALUES (?, ?, 'Disponible')");

            foreach ($_POST['agencies'] as $agency) {
                if (empty($agency['id_agencia'])) continue;

                $checks = $agency['checks'] ?? [];

                $stmtVisit->execute([
                    $id_reporte,
                    $agency['id_agencia'],
                    $agency['tipo_visita'],
                    $agency['tiempo_espera'] ?: 0,
                    $agency['congestion'],
                    $agency['eficiente'],
                    $agency['tamano_pedido'] ?? 'Mediano',
                    isset($checks['hoja_seguridad']) ? 1 : 0,
                    isset($checks['doc_especial']) ? 1 : 0,
                    isset($checks['cobro_previo']) ? 1 : 0,
                    isset($checks['restriccion_producto']) ? 1 : 0,
                    isset($checks['rechazo_carga']) ? 1 : 0,
                    isset($checks['cambio_politica']) ? 1 : 0,
                    $agency['observaciones'] ?? ''
                ]);
                $id_visita = $pdo->lastInsertId();

                // Conditions (Access)
                if (isset($agency['condiciones']) && is_array($agency['condiciones'])) {
                    foreach ($agency['condiciones'] as $cond) {
                        $stmtCond->execute([$id_visita, $cond]);
                    }
                }

                // Coverage (Destinations)
                if (!empty($agency['cobertura_destinos'])) {
                    // Assuming comma separated
                    $dests = explode(',', $agency['cobertura_destinos']);
                    foreach ($dests as $dest) {
                        $dest = trim($dest);
                        if ($dest) {
                            $stmtCov->execute([$id_visita, $dest]);
                        }
                    }
                }
            }
        }

        // 4. Incidents (General)
        if (!empty($_POST['incidencia_tipo'])) {
             $evidence_url = handleFileUpload('incidencia_evidence');
             $stmtInc = $pdo->prepare("INSERT INTO incidencias (id_reporte, tipo_incidencia, descripcion, gravedad, fecha_registro, evidencia_url) VALUES (?, ?, ?, 'Media', NOW(), ?)");
             $stmtInc->execute([$id_reporte, $_POST['incidencia_tipo'], $_POST['incidencia_desc'], $evidence_url]);
        }

        // 5. Recommendations
        if (!empty($_POST['recomendacion_desc'])) {
            $stmt = $pdo->prepare("INSERT INTO recomendaciones_ruta (id_reporte, tipo_recomendacion, descripcion) VALUES (?, ?, ?)");
            $stmt->execute([$id_reporte, $_POST['recomendacion_tipo'], $_POST['recomendacion_desc']]);
        }

        $pdo->commit();
        header('Location: ../index.php?page=dashboard&msg=ReporteGuardado');
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // In production, log error to file
        die("Error al guardar el reporte: " . $e->getMessage());
    }
}
