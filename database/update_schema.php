<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDB();

    // Modify incidencias table to allow NULL id_visita and add id_reporte
    $sql = "
    ALTER TABLE incidencias MODIFY id_visita INT NULL;
    ALTER TABLE incidencias ADD COLUMN IF NOT EXISTS id_reporte INT NULL AFTER id_visita;
    -- Note: Foreign key constraints might fail if already exists, so we wrap in try/catch or assume idempotent logic isn't strictly enforced here for simplicity.
    -- Better to just add columns.

    ALTER TABLE visitas_agencia ADD COLUMN IF NOT EXISTS tamano_pedido ENUM('Pequeno', 'Mediano', 'Grande');
    ALTER TABLE visitas_agencia ADD COLUMN IF NOT EXISTS check_hoja_seguridad TINYINT(1) DEFAULT 0;
    ALTER TABLE visitas_agencia ADD COLUMN IF NOT EXISTS check_doc_especial TINYINT(1) DEFAULT 0;
    ALTER TABLE visitas_agencia ADD COLUMN IF NOT EXISTS check_cobro_previo TINYINT(1) DEFAULT 0;
    ALTER TABLE visitas_agencia ADD COLUMN IF NOT EXISTS check_restriccion_producto TINYINT(1) DEFAULT 0;
    ALTER TABLE visitas_agencia ADD COLUMN IF NOT EXISTS check_rechazo_carga TINYINT(1) DEFAULT 0;
    ALTER TABLE visitas_agencia ADD COLUMN IF NOT EXISTS check_cambio_politica TINYINT(1) DEFAULT 0;
    ";

    // Split commands and execute
    $commands = explode(';', $sql);
    foreach ($commands as $cmd) {
        $cmd = trim($cmd);
        if (!empty($cmd)) {
            try {
                $pdo->exec($cmd);
            } catch (PDOException $e) {
                // Ignore errors like "Duplicate column name" for idempotent-like behavior
                echo "Warning executing command: $cmd - " . $e->getMessage() . "\n";
            }
        }
    }
    echo "Schema updated successfully.";
} catch (PDOException $e) {
    echo "Error updating schema: " . $e->getMessage();
}
