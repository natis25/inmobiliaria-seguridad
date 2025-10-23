<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'trabajador') {
    header('Location: ../login.php');
    exit();
}

// Incluir archivos (corregido: sube al root para acceder a Logica/)
require_once '../Logica/sql.php';
require_once '../Logica/helpers.php';

$conn = Conectarse();
if (!$conn) {
    die("Error de conexión a la base de datos");
}

// Obtener el ID del usuario
$user_id = $_SESSION['user_id'];

// DEBUG: Obtener información del usuario
$debug_info = debugUsuarioRol($conn, $user_id);

// TEMPORAL: Redirigir si es Kami Jimenez (ID 11) o si tiene rol 5
if ($user_id == 11 || ($debug_info['idRol'] ?? 0) == 5) {
    error_log("✅ Redirigiendo usuario OSI (ID: $user_id, Rol: " . ($debug_info['idRol'] ?? 'N/A') . ")");
    $conn->close();
    header('Location: panelControlOSI.php');
    exit();
}

// Obtener permisos del usuario
$permisos = obtenerPermisosUsuario($conn, $user_id);
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Gestión</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            height: 100vh;
            display: grid;
            place-items: center;
        }
        .grid-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            width: 90%;
            max-width: 400px;
        }
        .grid-container button {
            padding: 20px;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
            width: 100%;
        }
        .grid-container button:hover {
            transform: scale(1.05);
        }
        .grid-container button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        .btn-citas { background-color: #007bff; }
        .btn-inmuebles { background-color: #28a745; }
        .btn-empleados { background-color: #dc3545; }
        .btn-roles { background-color: #fd7e14; }
        .btn-reportes { background-color: #6f42c1; }
        .btn-inicio { background-color: #6c757d; }
        .btn-oraculo { background-color: #17a2b8; }
        
        .debug-info {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: #f8f9fa;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 12px;
            max-width: 300px;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="grid-container">
        <?php if (tienePermiso($permisos, 'citas', 'ver')): ?>
            <button class="btn-citas" onclick="location.href='../gestionarCitas.php'">📅 Gestionar Citas</button>
        <?php endif; ?>
        
        <?php if (tienePermiso($permisos, 'propiedades', 'ver')): ?>
            <button class="btn-inmuebles" onclick="location.href='../gestionarInmuebles.php'">🏠 Gestionar Inmuebles</button>
        <?php endif; ?>
        
        <?php if (tienePermiso($permisos, 'empleados', 'ver')): ?>
            <button class="btn-empleados" onclick="location.href='gestionarEmpleados.php'">👥 Gestionar Empleados</button>
        <?php endif; ?>
        
        <?php if (tienePermiso($permisos, 'roles', 'ver')): ?>
            <button class="btn-roles" onclick="location.href='gestionarRolesyPermisos.php'">🔐 Gestionar Roles</button>
        <?php endif; ?>
        
        <?php if (tienePermiso($permisos, 'reportes', 'ver')): ?>
            <button class="btn-reportes" onclick="location.href='../gestionarReportes.php'">📊 Generar Reportes</button>
        <?php endif; ?>
        
        <button class="btn-oraculo" onclick="location.href='../oraculo.php'">🔮 Oráculo de Decisiones</button>
        <button class="btn-inicio" onclick="location.href='../index.php'">🏠 Volver a Inicio</button>
    </div>

    <!-- DEBUG: Mostrar información del usuario -->
    <div class="debug-info">
        <strong>🔧 DEBUG INFO:</strong><br>
        User ID: <?php echo $_SESSION['user_id']; ?><br>
        Username: <?php echo $_SESSION['username']; ?><br>
        Rol ID: <?php echo $debug_info['idRol'] ?? 'No asignado'; ?><br>
        Rol Nombre: <strong><?php echo $debug_info['NombreRol'] ?? 'No asignado'; ?></strong><br>
        <?php if ($_SESSION['user_id'] == 11): ?>
            <small style="color: green;">✅ Usuario OSI (Kami) - Redirigiendo...</small>
        <?php else: ?>
            <small style="color: orange;">⚠️ Usuario normal</small>
        <?php endif; ?>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>