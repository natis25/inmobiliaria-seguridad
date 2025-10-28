<?php
<<<<<<< HEAD
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
session_regenerate_id(true);

require_once __DIR__ . '/auth_helpers.php';

if (hit_rate_limit('rl_reg_empleado', 10)) {
    $_SESSION['flash_error'] = 'Estás enviando muy rápido. Intenta en unos segundos.';
    header("Location: ../OSI/registrarEmpleado.php");
    exit;
}

// Redirección propia para errores
$GLOBALS['__POST_REDIRECT'] = '../OSI/registrarEmpleado.php';
require_once __DIR__ . '/bootstrap_post.php';

$nombre   = cap($_POST['nombre']   ?? '', 100);
$apellido = cap($_POST['apellido'] ?? '', 100);
$usuarioI = cap($_POST['usuario']  ?? '', 100);
$tel      = cap($_POST['telefono'] ?? '', 8);
// ELIMINAMOS la variable $dir ya que no existe en la tabla
$correo   = mb_strtolower(cap($_POST['correo'] ?? '', 100), 'UTF-8');
=======
ini_set('session.cookie_httponly','1');
ini_set('session.cookie_samesite','Lax');
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
session_regenerate_id(true);

require_once __DIR__.'/auth_helpers.php'; // <-- helpers PRIMERO

if (hit_rate_limit('rl_reg_empleado', 10)){
  $_SESSION['flash_error']='Estás enviando muy rápido. Intenta en unos segundos.';
  header("Location: ../registroEmpleado.php"); exit;
}

// Redirección propia para errores
$GLOBALS['__POST_REDIRECT'] = '../registroEmpleado.php';
require_once __DIR__.'/bootstrap_post.php'; // POST/CSRF + $mysqli

$nombre   = cap($_POST['nombre']   ?? '',100);
$apellido = cap($_POST['apellido'] ?? '',100);
$usuarioI = cap($_POST['usuario']  ?? '',100);
$tel      = cap($_POST['telefono'] ?? '',8);
$dir      = cap($_POST['direccion']?? '',150);
>>>>>>> origin/eduardo/registro-usuarios
$idCargo  = ($_POST['idCargo'] ?? '') !== '' ? (int)$_POST['idCargo'] : null;
$idRol    = ($_POST['idRol']   ?? '') !== '' ? (int)$_POST['idRol']   : null;
$pwd      = (string)($_POST['password']  ?? '');
$pwd2     = (string)($_POST['password2'] ?? '');

<<<<<<< HEAD
$usuario = sanitize_username_input($usuarioI, $nombre, $apellido);
if (!preg_match('/^[a-z]+(\.[a-z0-9]+)*$/', $usuario)) {
    $usuario = build_username_base($nombre, $apellido) ?: 'usuario';
}
$usuario = username_unico($mysqli, $usuario, 'trabajador');

$err = [];
if ($nombre === '' || $apellido === '') $err[] = '1';
if (!preg_match('/^\d{8}$/', $tel)) $err[] = '2';
// ELIMINAMOS la validación de dirección
[$pwd_ok] = validar_password($pwd);
if ($pwd !== $pwd2 || !$pwd_ok) $err[] = '4';
if (correo_existe($mysqli, $correo, 'trabajador')) $err[] = '5';
if (!$idCargo || !$idRol) $err[] = '6';

if ($err) {
    $_SESSION['flash_error'] = 'No se pudo completar el alta. Revisa los datos.';
    header("Location: ../OSI/registrarEmpleado.php");
    exit;
}

$lockU = "emp:usr:" . $usuario;
$lockC = "emp:mail:" . $correo;

if (!get_named_lock($mysqli, $lockU, 5)) {
    $_SESSION['flash_error'] = 'Sistema ocupado (usuario).';
    header("Location: ../OSI/registrarEmpleado.php");
    exit;
}
if (!get_named_lock($mysqli, $lockC, 5)) {
    release_named_lock($mysqli, $lockU);
    $_SESSION['flash_error'] = 'Sistema ocupado (correo).';
    header("Location: ../OSI/registrarEmpleado.php");
    exit;
}

$mysqli->begin_transaction();
try {
    $s = $mysqli->prepare("SELECT 1 FROM trabajador WHERE Usuario=? OR Correo=? LIMIT 1");
    $s->bind_param('ss', $usuario, $correo);
    $s->execute();
    $s->store_result();
    if ($s->num_rows > 0) {
        $s->close();
        throw new RuntimeException('Duplicado');
    }
    $s->close();

    $exp = (new DateTime('now'))->format('Y-m-d H:i:s');
    $estado = 'Activo';

    // CONSULTA CORREGIDA: Sin el campo Direccion
    $sql = "INSERT INTO trabajador
            (Nombre, Apellido, Usuario, Telefono, Correo, idCargo, idRol, EstadoCuenta, IntentosFallidos, password_expires_at, is_deleted, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 0, NOW(), NOW())";
    
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Error preparando consulta: ' . $mysqli->error);
    }
    
    $stmt->bind_param('sssssiiss', $nombre, $apellido, $usuario, $tel, $correo, $idCargo, $idRol, $estado, $exp);
    
    if (!$stmt->execute()) {
        throw new RuntimeException('Error en execute: ' . $stmt->error);
    }
    
    $idTrab = $stmt->insert_id;
    $stmt->close();

    $hash = password_hash($pwd, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO password_history (user_type, user_id, PasswordHash) VALUES ('trabajador', ?, ?)");
    if (!$stmt) {
        throw new RuntimeException('Error preparando consulta de password: ' . $mysqli->error);
    }
    
    $stmt->bind_param('is', $idTrab, $hash);
    
    if (!$stmt->execute()) {
        throw new RuntimeException('Error al guardar contraseña: ' . $stmt->error);
    }
    
    $stmt->close();

    $mysqli->commit();
    $_SESSION['flash_success'] = 'Trabajador creado exitosamente. Se requerirá cambio de contraseña al primer ingreso.';
    header("Location: ../OSI/gestionarEmpleados.php");
    exit;
} catch (Throwable $e) {
    $mysqli->rollback();
    error_log("Error en transacción: " . $e->getMessage());
    $_SESSION['flash_error'] = 'No se pudo completar el alta: ' . $e->getMessage();
    header("Location: ../OSI/registrarEmpleado.php");
    exit;
} finally {
    release_named_lock($mysqli, $lockC);
    release_named_lock($mysqli, $lockU);
}
?>
=======
$usuario = sanitize_username_input($usuarioI,$nombre,$apellido);
if (!preg_match('/^[a-z]+(\.[a-z0-9]+)*$/',$usuario)){
  $usuario = build_username_base($nombre,$apellido) ?: 'usuario';
}
$usuario = username_unico($mysqli,$usuario,'trabajador');

$correo = generar_correo_empleado($usuario,'droca.local');

$err=[];
if ($nombre===''||$apellido==='') $err[]='1';
if (!preg_match('/^\d{8}$/',$tel)) $err[]='2';
if ($dir==='') $err[]='3';
[$pwd_ok] = validar_password($pwd);
if ($pwd!==$pwd2 || !$pwd_ok) $err[]='4';
if (correo_existe($mysqli,$correo,'trabajador')) $err[]='5';

if ($err){
  $_SESSION['flash_error'] = 'No se pudo completar el alta. Revisa los datos.';
  header("Location: ../registroEmpleado.php"); exit;
}

$lockU = "emp:usr:".$usuario;
$lockC = "emp:mail:".$correo;

if (!get_named_lock($mysqli,$lockU,5)){ $_SESSION['flash_error']='Sistema ocupado (usuario).'; header("Location: ../registroEmpleado.php"); exit; }
if (!get_named_lock($mysqli,$lockC,5)){ release_named_lock($mysqli,$lockU); $_SESSION['flash_error']='Sistema ocupado (correo).'; header("Location: ../registroEmpleado.php"); exit; }

$mysqli->begin_transaction();
try{
  $s=$mysqli->prepare("SELECT 1 FROM trabajador WHERE Usuario=? OR Correo=? LIMIT 1");
  $s->bind_param('ss',$usuario,$correo); $s->execute(); $s->store_result();
  if ($s->num_rows>0){ $s->close(); throw new RuntimeException('Duplicado'); }
  $s->close();

  $exp = (new DateTime('now'))->format('Y-m-d H:i:s'); // fuerza cambio al primer login
  $estado = 'Activo';

  $sql="INSERT INTO trabajador
        (Nombre,Apellido,Usuario,Telefono,Correo,idCargo,idRol,EstadoCuenta,IntentosFallidos,password_expires_at,is_deleted)
        VALUES (?,?,?,?,?,?,?,?,0,?,0)";
  $stmt=$mysqli->prepare($sql);
  $stmt->bind_param('sssssiiss', $nombre,$apellido,$usuario,$tel,$correo,$idCargo,$idRol,$estado,$exp);
  $stmt->execute();
  $idTrab=$stmt->insert_id; $stmt->close();

  $hash = password_hash($pwd, PASSWORD_DEFAULT);
  $stmt=$mysqli->prepare("INSERT INTO password_history (user_type,user_id,PasswordHash) VALUES ('trabajador', ?, ?)");
  $stmt->bind_param('is',$idTrab,$hash);
  $stmt->execute(); $stmt->close();

  $mysqli->commit();
  $_SESSION['flash_success'] = 'Trabajador creado. Se requerirá cambio de contraseña al primer ingreso.';
  header("Location: ../registroEmpleado.php"); exit;

}catch(Throwable $e){
  $mysqli->rollback();
  $_SESSION['flash_error'] = 'No se pudo completar el alta.';
  header("Location: ../registroEmpleado.php"); exit;
} finally {
  release_named_lock($mysqli,$lockC);
  release_named_lock($mysqli,$lockU);
}
>>>>>>> origin/eduardo/registro-usuarios
