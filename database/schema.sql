-- Sistema: Reporte Diario de Despacho
-- Base de Datos: rdl_db

CREATE DATABASE IF NOT EXISTS rdl_db;
USE rdl_db;

-- 1. TABLA: usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    cargo VARCHAR(50),
    area VARCHAR(50),
    rol ENUM('Despacho', 'Ventas', 'Operaciones', 'Admin', 'Gerencia') NOT NULL,
    password VARCHAR(255) NOT NULL, -- Added for authentication
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. TABLA: vehiculos
CREATE TABLE IF NOT EXISTS vehiculos (
    id_vehiculo INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(20) NOT NULL UNIQUE,
    modelo VARCHAR(50),
    marca VARCHAR(50),
    anio INT,
    estado ENUM('Activo', 'Mantenimiento', 'Inactivo') DEFAULT 'Activo',
    kilometraje_actual INT,
    fecha_ultimo_mantenimiento DATE
);

-- 3. TABLA: agencias
CREATE TABLE IF NOT EXISTS agencias (
    id_agencia INT AUTO_INCREMENT PRIMARY KEY,
    nombre_agencia VARCHAR(100) NOT NULL,
    sede VARCHAR(100),
    direccion VARCHAR(255),
    distrito VARCHAR(50),
    zona VARCHAR(50),
    estado ENUM('Activa', 'Restringida', 'No recomendada') DEFAULT 'Activa',
    horario_apertura TIME,
    horario_cierre TIME,
    atiende_domingo TINYINT(1) DEFAULT 0,
    atiende_feriado TINYINT(1) DEFAULT 0,
    requiere_documentacion_seguridad TINYINT(1) DEFAULT 0,
    cobra_previo_envio TINYINT(1) DEFAULT 0,
    nivel_accesibilidad ENUM('Alto', 'Medio', 'Bajo') DEFAULT 'Medio',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. TABLA: reportes_despacho
CREATE TABLE IF NOT EXISTS reportes_despacho (
    id_reporte INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    hora_salida TIME,
    hora_retorno TIME,
    id_chofer INT,
    id_copiloto INT,
    id_vehiculo INT,
    ruta_descripcion TEXT,
    estado_reporte ENUM('Borrador', 'Cerrado', 'Validado') DEFAULT 'Borrador',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion INT,
    FOREIGN KEY (id_chofer) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_copiloto) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_vehiculo) REFERENCES vehiculos(id_vehiculo),
    FOREIGN KEY (usuario_creacion) REFERENCES usuarios(id_usuario)
);

-- 5. TABLA: visitas_agencia
CREATE TABLE IF NOT EXISTS visitas_agencia (
    id_visita INT AUTO_INCREMENT PRIMARY KEY,
    id_reporte INT NOT NULL,
    id_agencia INT NOT NULL,
    tipo_visita ENUM('Despacho', 'Consulta', 'Recojo', 'Cliente directo'),
    tiempo_espera_minutos INT,
    nivel_congestion ENUM('Bajo', 'Medio', 'Alto'),
    eficiencia_envio TINYINT(1), -- Si/No
    tamano_pedido ENUM('Pequeno', 'Mediano', 'Grande'),
    check_hoja_seguridad TINYINT(1) DEFAULT 0,
    check_doc_especial TINYINT(1) DEFAULT 0,
    check_cobro_previo TINYINT(1) DEFAULT 0,
    check_restriccion_producto TINYINT(1) DEFAULT 0,
    check_rechazo_carga TINYINT(1) DEFAULT 0,
    check_cambio_politica TINYINT(1) DEFAULT 0,
    observaciones TEXT,
    FOREIGN KEY (id_reporte) REFERENCES reportes_despacho(id_reporte) ON DELETE CASCADE,
    FOREIGN KEY (id_agencia) REFERENCES agencias(id_agencia)
);

-- 6. TABLA: condiciones_acceso
CREATE TABLE IF NOT EXISTS condiciones_acceso (
    id_condicion INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    tipo_condicion VARCHAR(100), -- Calle cerrada, Obras civiles, etc.
    nivel_impacto ENUM('Bajo', 'Medio', 'Alto'),
    recurrencia ENUM('Temporal', 'Permanente'),
    observacion TEXT,
    FOREIGN KEY (id_visita) REFERENCES visitas_agencia(id_visita) ON DELETE CASCADE
);

-- 7. TABLA: cobertura_agencia
CREATE TABLE IF NOT EXISTS cobertura_agencia (
    id_cobertura INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    destino_cubierto VARCHAR(100),
    estado_cobertura ENUM('Disponible', 'Suspendido'),
    observacion TEXT,
    FOREIGN KEY (id_visita) REFERENCES visitas_agencia(id_visita) ON DELETE CASCADE
);

-- 8. TABLA: incidencias
CREATE TABLE IF NOT EXISTS incidencias (
    id_incidencia INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NULL, -- Made Nullable
    id_reporte INT NULL, -- Added to link to report
    tipo_incidencia VARCHAR(100), -- Daño producto, Rechazo carga, etc.
    gravedad ENUM('Baja', 'Media', 'Alta'),
    descripcion TEXT,
    evidencia_url VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_visita) REFERENCES visitas_agencia(id_visita) ON DELETE CASCADE,
    FOREIGN KEY (id_reporte) REFERENCES reportes_despacho(id_reporte) ON DELETE CASCADE
);

-- 9. TABLA: estado_vehiculo_reporte
CREATE TABLE IF NOT EXISTS estado_vehiculo_reporte (
    id_estado INT AUTO_INCREMENT PRIMARY KEY,
    id_reporte INT NOT NULL,
    embrague ENUM('OK', 'Revisar', 'Urgente'),
    frenos ENUM('OK', 'Revisar', 'Urgente'),
    aire_acondicionado ENUM('OK', 'Revisar', 'Urgente'),
    luces ENUM('OK', 'Revisar', 'Urgente'),
    llantas ENUM('OK', 'Revisar', 'Urgente'),
    ruidos ENUM('OK', 'Revisar', 'Urgente'),
    alertas_tablero ENUM('OK', 'Revisar', 'Urgente'),
    observacion_general TEXT,
    evidencia_url VARCHAR(255),
    FOREIGN KEY (id_reporte) REFERENCES reportes_despacho(id_reporte) ON DELETE CASCADE
);

-- 10. TABLA: recomendaciones_ruta
CREATE TABLE IF NOT EXISTS recomendaciones_ruta (
    id_recomendacion INT AUTO_INCREMENT PRIMARY KEY,
    id_reporte INT NOT NULL,
    descripcion TEXT,
    tipo_recomendacion ENUM('Agencia recomendada', 'Agencia evitar', 'Mejores horarios', 'Mejora operativa'),
    FOREIGN KEY (id_reporte) REFERENCES reportes_despacho(id_reporte) ON DELETE CASCADE
);

-- Datos de Prueba (Seed Data)

-- Usuarios (Password: '123456' hashed with PASSWORD_DEFAULT)
INSERT INTO usuarios (nombre, cargo, area, rol, password) VALUES
('Juan Perez', 'Conductor', 'Logistica', 'Despacho', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Maria Lopez', 'Copiloto', 'Logistica', 'Despacho', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Carlos Gerente', 'Gerente Ops', 'Operaciones', 'Gerencia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Ana Admin', 'Admin', 'Sistemas', 'Admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Vehiculos
INSERT INTO vehiculos (placa, modelo, marca, anio, kilometraje_actual) VALUES
('ABC-123', 'H1', 'Hyundai', 2020, 50000),
('XYZ-789', 'Sprinter', 'Mercedes', 2019, 120000);

-- Agencias
INSERT INTO agencias (nombre_agencia, sede, direccion, distrito, zona, horario_apertura, horario_cierre) VALUES
('Transportes Cruz del Sur', 'Javier Prado', 'Av. Javier Prado 123', 'La Victoria', 'Centro', '08:00', '18:00'),
('Shalom', 'Tomas Valle', 'Av. Tomas Valle 456', 'San Martin de Porres', 'Norte', '09:00', '19:00'),
('Marvisur', 'Villa El Salvador', 'Av. El Sol 789', 'Villa El Salvador', 'Sur', '08:30', '17:30');
