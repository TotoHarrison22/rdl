# Manual de Usuario y Técnico - Sistema de Reporte Diario de Despacho (RDL)

## 1. Descripción del Proyecto
El sistema RDL permite digitalizar el proceso de reporte de rutas de despacho, consolidando información sobre agencias, tiempos de atención, incidencias y estado vehicular.

## 2. Instalación y Configuración

### Requisitos
- Servidor Web (Apache/Nginx)
- PHP 7.4 o superior
- MySQL 5.7 o superior

### Pasos de Instalación
1. **Base de Datos**:
   - Ejecute el script `database/schema.sql` en su servidor MySQL para crear la base de datos `rdl_db` y las tablas necesarias.
   - Usuarios por defecto:
     - `Juan Perez` (Despacho)
     - `Carlos Gerente` (Gerencia)
     - Clave por defecto para todos (hash): `123456` (Nota: en producción cambiar hashes).

2. **Configuración**:
   - Edite el archivo `config/database.php` con las credenciales de su servidor:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'rdl_db');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

3. **Despliegue**:
   - Copie todos los archivos al directorio público de su servidor web.

## 3. Manual de Uso

### 3.1 Inicio de Sesión
- Ingrese con su **Nombre** de usuario registrado.

### 3.2 Crear Reporte de Despacho (Perfil: Despacho)
1. Vaya a **Nuevo Reporte**.
2. Complete la **Información General** (Chofer, Vehículo, Ruta).
3. En **Agencias Visitadas**, use el botón "+ Agregar Agencia" para añadir cada punto de entrega. Indique tiempos y observaciones.
4. Complete el **Checklist Vehicular**. Si marca "Urgente", se generará una alerta.
5. Registre **Incidencias** si ocurrieron eventos relevantes.
6. Guarde el reporte.

### 3.3 Dashboard y Consultas (Perfil: Gerencia/Ventas)
- **Dashboard**: Visualice KPIs como tiempos de espera promedio y alertas de mantenimiento.
- **Base de Conocimiento**: Use el buscador para encontrar agencias. Vea el historial de accesibilidad y recomendaciones.

## 4. Diccionario de Datos (Resumen)

### Tablas Principales
- `reportes_despacho`: Cabecera del reporte diario.
- `visitas_agencia`: Detalle de cada parada (1 a N).
- `agencias`: Maestro de clientes/destinos.
- `incidencias`: Eventos negativos durante la ruta.
- `estado_vehiculo_reporte`: Auditoría mecánica diaria.

## 5. Estructura del Código
- `config/`: Conexión BD.
- `src/`: Lógica de negocio (Controladores).
- `views/`: Interfaz de usuario (HTML/PHP).
- `public/`: Archivos estáticos (CSS/JS).

## 6. Soporte
Para soporte técnico, contactar al área de Sistemas.
