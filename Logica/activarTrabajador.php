<?php
session_start();
require_once 'sql.php';
require_once 'helpers.php';

// Verificar si el usuario está logueado y es OSI
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'trabajador') {
    header('Location: ../login.php');
    exit();
}

$conn = Conectarse();
if (!$conn) {
    die("Error de conexión");
}

// Verificar si el usuario tiene rol OSI
if (!esUsuarioOSI($conn, $_SESSION['user_id'])) {
    header('Location: ../panelControl.php');
    exit();
}

// Obtener ID del trabajador
$idTrabajador = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idTrabajador > 0) {
    // Activar el trabajador
    $stmt = $conn->prepare("UPDATE trabajador SET EstadoCuenta = 'Activo', IntentosFallidos = 0 WHERE idTrabajador = ?");
    $stmt->bind_param("i", $idTrabajador);
    
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "✅ Trabajador activado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "❌ Error al activar el trabajador";
        $_SESSION['tipo_mensaje'] = "danger";
    }
    $stmt->close();
} else {
    $_SESSION['mensaje'] = "❌ ID de trabajador no válido";
    $_SESSION['tipo_mensaje'] = "danger";
}

$conn->close();
header("Location: ../OSI/gestionarEmpleados.php");
exit();
?>