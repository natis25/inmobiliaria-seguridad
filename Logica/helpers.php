<?php
// helpers.php - Funciones para verificar permisos en MySQLi

function verificarPermiso($conn, $user_id, $modulo, $accion) {
    $stmt = $conn->prepare("
        SELECT 1 
        FROM trabajador t
        JOIN rol_permiso rp ON t.idRol = rp.idRol
        JOIN permiso p ON rp.idPermiso = p.idPermiso
        WHERE t.idTrabajador = ? AND p.Modulo = ? AND p.Accion = ? AND t.EstadoCuenta = 'Activo'
    ");
    $stmt->bind_param("iss", $user_id, $modulo, $accion);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function obtenerPermisosUsuario($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT p.Modulo, p.Accion 
        FROM trabajador t
        JOIN rol_permiso rp ON t.idRol = rp.idRol
        JOIN permiso p ON rp.idPermiso = p.idPermiso
        WHERE t.idTrabajador = ? AND t.EstadoCuenta = 'Activo'
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function esUsuarioOSI($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT r.NombreRol 
        FROM trabajador t
        JOIN rol r ON t.idRol = r.idRol
        WHERE t.idTrabajador = ? AND t.EstadoCuenta = 'Activo' AND r.NombreRol = 'OSI'
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Función para verificar si tiene permiso (sin conexión)
function tienePermiso($permisos, $modulo, $accion) {
    foreach ($permisos as $permiso) {
        if ($permiso['Modulo'] === $modulo && $permiso['Accion'] === $accion) {
            return true;
        }
    }
    return false;
}

// Función para debugging
function debugUsuarioRol($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT t.idTrabajador, t.Usuario, r.idRol, r.NombreRol 
        FROM trabajador t
        LEFT JOIN rol r ON t.idRol = r.idRol
        WHERE t.idTrabajador = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    return $data;
}
?>