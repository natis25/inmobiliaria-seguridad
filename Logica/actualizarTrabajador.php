<?php
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
session_regenerate_id(true);

require_once __DIR__ . '/auth_helpers.php';
require_once __DIR__ . '/bootstrap_post.php';
require_once __DIR__ . '/sql.php';

if (hit_rate_limit('rl_mod_empleado', 10)) {
    $_SESSION['flash_error'] = 'Estás enviando muy rápido. Intenta en unos segundos.';
    header("Location: ../OSI/modificarEmpleado.php?id=" . ($_POST['idTrabajador'] ?? '0'));
    exit;
}

// Redirección para errores
$GLOBALS['__POST_REDIRECT'] = '../OSI/modificarEmpleado.php?id=' . ($_POST['idTrabajador'] ?? '0');

// ---- Validar inputs ----
$idTrabajador = isset($_POST['idTrabajador']) && is_numeric($_POST['idTrabajador']) ? (int)$_POST['idTrabajador'] : 0;
$nombre   = cap($_POST['nombre'] ?? '', 100);
$apellido = cap($_POST['apellido'] ?? '', 100);
$usuarioI = cap($_POST['usuario'] ?? '', 100);
$tel      = cap($_POST['telefono'] ?? '', 8);
$correo   = mb_strtolower(cap($_POST['correo'] ?? '', 100), 'UTF-8');
$idCargo  = ($_POST['idCargo'] ?? '') !== '' ? (int)$_POST['idCargo'] : null;
$idRol    = ($_POST['idRol'] ?? '') !== '' ? (int)$_POST['idRol'] : null;

// ---- Validaciones ----
$err = [];
if ($idTrabajador <= 0) $err[] = 'ID de trabajador inválido';
if ($nombre === '') $err[] = 'El nombre es obligatorio';
if ($apellido === '') $err[] = 'El apellido es obligatorio';
if (!preg_match('/^\d{8}$/', $tel)) $err[] = 'El teléfono debe tener 8 dígitos';
if (!$idCargo) $err[] = 'El cargo es obligatorio';
if (!$idRol) $err[] = 'El rol es obligatorio';

// Validar unicidad de usuario (excluyendo el propio idTrabajador)
$usuario = sanitize_username_input($usuarioI, $nombre, $apellido);
if (!preg_match('/^[a-z]+(\.[a-z0-9]+)*$/', $usuario)) {
    $usuario = build_username_base($nombre, $apellido) ?: 'usuario';
}
$usuario = username_unico($mysqli, $usuario, 'trabajador', $idTrabajador);

// Validar correo: Solo falla si el correo existe y NO pertenece al idTrabajador actual
$conn = Conectarse();
if (!$conn) {
    $_SESSION['flash_error'] = 'Error de conexión a la base de datos.';
    header("Location: ../OSI/modificarEmpleado.php?id=$idTrabajador");
    exit;
}
$stmt = $conn->prepare("SELECT idTrabajador FROM trabajador WHERE Correo = ? AND idTrabajador != ? AND is_deleted = 0");
$stmt->bind_param('si', $correo, $idTrabajador);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $err[] = 'El correo ya está en uso por otro empleado';
}
$stmt->close();

// Validar existencia de idCargo y idRol
$stmt = $conn->prepare("SELECT 1 FROM cargo WHERE idCargo = ?");
$stmt->bind_param('i', $idCargo);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $err[] = 'El cargo seleccionado no existe';
}
$stmt->close();

$stmt = $conn->prepare("SELECT 1 FROM rol WHERE idRol = ?");
$stmt->bind_param('i', $idRol);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $err[] = 'El rol seleccionado no existe';
}
$stmt->close();

if ($err) {
    $conn->close();
    $_SESSION['flash_error'] = implode('. ', $err) . '.';
    header("Location: ../OSI/modificarEmpleado.php?id=$idTrabajador");
    exit;
}

// ---- Bloqueos lógicos ----
$lockU = "emp:usr:" . $usuario;
$lockC = "emp:mail:" . $correo;

if (!get_named_lock($conn, $lockU, 5)) {
    $conn->close();
    $_SESSION['flash_error'] = 'Sistema ocupado (usuario).';
    header("Location: ../OSI/modificarEmpleado.php?id=$idTrabajador");
    exit;
}
if (!get_named_lock($conn, $lockC, 5)) {
    release_named_lock($conn, $lockU);
    $conn->close();
    $_SESSION['flash_error'] = 'Sistema ocupado (correo).';
    header("Location: ../OSI/modificarEmpleado.php?id=$idTrabajador");
    exit;
}

// ---- Transacción ----
$conn->begin_transaction();
try {
    $sql = "UPDATE trabajador SET 
            Nombre = ?, Apellido = ?, Usuario = ?, Telefono = ?, Correo = ?, 
            idCargo = ?, idRol = ?, updated_at = NOW()
            WHERE idTrabajador = ? AND is_deleted = 0";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Error al preparar la consulta: ' . $conn->error);
    }
    $stmt->bind_param('sssssiis', $nombre, $apellido, $usuario, $tel, $correo, $idCargo, $idRol, $idTrabajador);
    $stmt->execute();
    if ($stmt->affected_rows === 0) {
        throw new RuntimeException('No se actualizó ningún registro.');
    }
    $stmt->close();

    $conn->commit();
    $_SESSION['flash_success'] = 'Empleado actualizado correctamente.';
    header("Location: ../OSI/gestionarEmpleados.php");
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    $_SESSION['flash_error'] = 'No se pudo actualizar el empleado: ' . $e->getMessage();
    header("Location: ../OSI/modificarEmpleado.php?id=$idTrabajador");
    exit;
} finally {
    release_named_lock($conn, $lockC);
    release_named_lock($conn, $lockU);
    $conn->close();
}
?>