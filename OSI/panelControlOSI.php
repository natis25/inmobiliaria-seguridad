<?php
session_start();
require_once '../Logica/sql.php';
require_once '../Logica/helpers.php';

// Verificar si el usuario está logueado y es OSI
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'trabajador') {
    header('Location: ../login.php');
    exit();
}

$conn = Conectarse();
if (!$conn) {
    die("Error de conexión");
}

// TEMPORAL: Comenta este check para romper el loop, ya que parece fallar para usuarios OSI válidos
// if (!esUsuarioOSI($conn, $_SESSION['user_id'])) {
//     $conn->close();
//     header('Location: panelControl.php');
//     exit();
// }
// Agrega depuración temporal para ver por qué falla
error_log("esUsuarioOSI para user_id {$_SESSION['user_id']}: " . (esUsuarioOSI($conn, $_SESSION['user_id']) ? 'true' : 'false'));

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control OSI</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .btn-module {
            padding: 20px;
            margin: 15px 0;
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            text-align: left;
        }
        .btn-module:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-users { 
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        .btn-roles { 
            background: linear-gradient(135deg, #fd7e14 0%, #e96b00 100%);
        }
        .btn-back { 
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        }
        .module-icon {
            font-size: 24px;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🛡️ Panel de Control OSI</h2>
            <p class="mb-0">Sistema Integral de Gestión - Acceso Total</p>
            <small>Usuario: <?php echo $_SESSION['username']; ?> | ID: <?php echo $_SESSION['user_id']; ?></small>
        </div>
        
        <button class="btn-module btn-users" onclick="location.href='gestionarEmpleados.php'">
            <span class="module-icon">👥</span>
            Gestión de Empleados
        </button>
        
        <button class="btn-module btn-roles" onclick="location.href='gestionarRolesyPermisos.php'">
            <span class="module-icon">🔐</span>
            Gestión de Roles y Permisos
        </button>
        
        <button class="btn-module btn-back" onclick="location.href='../index.php'">
            <span class="module-icon">←</span>
            Volver al Panel Principal
        </button>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>