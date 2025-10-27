<?php
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
session_regenerate_id(true);

require_once __DIR__ . '/auth_helpers.php';

if (hit_rate_limit('rl_reg_empleado', 10)) {
  $_SESSION['flash_error'] = 'Estás enviando muy rápido. Intenta en unos segundos.';
  header("Location: ../OSI/registrarEmpleado.php"); exit;
}

// Redirección propia para errores
$GLOBALS['__POST_REDIRECT'] = '../OSI/registrarEmpleado.php';
require_once __DIR__ . '/bootstrap_post.php'; // crea $mysqli + valida CSRF/POST

// ---------- Inputs ----------
$nombre    = cap($_POST['nombre']    ?? '', 100);
$apellido1 = cap($_POST['apellido']  ?? '', 100);
$apellido2 = cap($_POST['apellido2'] ?? '', 100);
$tel       = cap($_POST['telefono']  ?? '', 8);
$idCargo   = ($_POST['idCargo'] ?? '') !== '' ? (int)$_POST['idCargo'] : null;
$idRol     = ($_POST['idRol']   ?? '') !== '' ? (int)$_POST['idRol']   : null;

// ---------- Validaciones ----------
$err = [];
if ($nombre==='' || $apellido1==='' || $apellido2==='') $err[]='1';
if (!preg_match('/^\d{8}$/', $tel))                      $err[]='2';
if (!$idCargo || !$idRol)                                $err[]='3';
if ($err){
  $_SESSION['flash_error']='No se pudo completar el alta. Revisa los datos.';
  header("Location: ../OSI/registrarEmpleado.php"); exit;
}

// ---------- Usuario + correo (no editables) ----------
$usuario = generar_usuario_estandarizado($mysqli, $nombre, $apellido1, $apellido2, 'trabajador');
$correo  = generar_correo_empleado($usuario, 'droca.com'); // ajusta dominio si aplica

// ---------- Bloqueos lógicos ----------
$lockU = "emp:usr:".$usuario;
$lockC = "emp:mail:".$correo;
if (!get_named_lock($mysqli, $lockU, 5)) { $_SESSION['flash_error']='Sistema ocupado (usuario).'; header("Location: ../OSI/registrarEmpleado.php"); exit; }
if (!get_named_lock($mysqli, $lockC, 5)) { release_named_lock($mysqli,$lockU); $_SESSION['flash_error']='Sistema ocupado (correo).'; header("Location: ../OSI/registrarEmpleado.php"); exit; }

// ---------- Transacción ----------
$mysqli->begin_transaction();
try {
  // Doble verificación
  $s = $mysqli->prepare("SELECT 1 FROM trabajador WHERE Usuario=? OR Correo=? LIMIT 1");
  $s->bind_param('ss',$usuario,$correo); $s->execute(); $s->store_result();
  if ($s->num_rows>0){ $s->close(); throw new RuntimeException('Duplicado'); }
  $s->close();

  // Forzar cambio de contraseña en primer login: expira ahora
  $expira = (new DateTime('now'))->format('Y-m-d H:i:s');
  $estado = 'Activo';

  // Insert: ahora con Apellido2, sin Dirección
  $sql = "INSERT INTO trabajador
          (Nombre, Apellido, Apellido2, Usuario, Telefono, Correo,
           idCargo, idRol, EstadoCuenta, IntentosFallidos, password_expires_at,
           is_deleted, created_at, updated_at)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 0, NOW(), NOW())";
  $stmt = $mysqli->prepare($sql);
  if (!$stmt) throw new RuntimeException('Error preparando consulta: '.$mysqli->error);
  $stmt->bind_param('ssssssiiss', $nombre, $apellido1, $apellido2, $usuario, $tel, $correo, $idCargo, $idRol, $estado, $expira);
  if (!$stmt->execute()) throw new RuntimeException('Error en execute: '.$stmt->error);
  $idTrab = (int)$stmt->insert_id;
  $stmt->close();

  // CodigoUsuario estandar
  $codigo = build_codigo_empleado($idTrab);
  $u = $mysqli->prepare("UPDATE trabajador SET CodigoUsuario=? WHERE idTrabajador=?");
  $u->bind_param('si', $codigo, $idTrab);
  $u->execute(); $u->close();

  // Contraseña temporal (autogenerada) + historial
  $pwd_temporal = generar_password_temporal();
  [$pwd_ok] = validar_password($pwd_temporal);
  if (!$pwd_ok) { // por si acaso (no debería pasar)
    throw new RuntimeException('La contraseña temporal generada no cumple la política.');
  }
  $hash = password_hash($pwd_temporal, PASSWORD_DEFAULT);

  $stmt = $mysqli->prepare("INSERT INTO password_history (user_type, user_id, PasswordHash)
                           VALUES ('trabajador', ?, ?)");
  if (!$stmt) throw new RuntimeException('Error preparando password_history: '.$mysqli->error);
  $stmt->bind_param('is', $idTrab, $hash);
  if (!$stmt->execute()) throw new RuntimeException('Error al guardar contraseña: '.$stmt->error);
  $stmt->close();

  // Commit + credenciales en sesión para mostrarlas UNA vez
  $mysqli->commit();

  $_SESSION['temp_creds'] = [
    'usuario' => $usuario,
    'correo'  => $correo,
    'codigo'  => $codigo,
    'pwd'     => $pwd_temporal,
  ];

  $_SESSION['flash_success'] =
    'Trabajador creado. Entrega estas credenciales al empleado y solicita cambio inmediato.';

  // Redirige a la página que muestra la sección de credenciales
  header("Location: ../OSI/registrarEmpleado.php");
  exit;

} catch (Throwable $e) {
  $mysqli->rollback();
  error_log("Alta empleado: ".$e->getMessage());
  $_SESSION['flash_error'] = 'No se pudo completar el alta: '.$e->getMessage();
  header("Location: ../OSI/registrarEmpleado.php"); exit;

} finally {
  release_named_lock($mysqli,$lockC);
  release_named_lock($mysqli,$lockU);
}
