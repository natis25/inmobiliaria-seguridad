```php
<?php
// Procesa ALTA de CLIENTE

ini_set('session.cookie_httponly','1');
ini_set('session.cookie_samesite','Lax');
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
session_regenerate_id(true);

require_once __DIR__ . '/auth_helpers.php';  // <-- helpers PRIMERO

// Rate limit antes de tocar DB
if (hit_rate_limit('rl_reg_cliente', 10)) {
  $_SESSION['flash_error']='Estás enviando muy rápido. Intenta en unos segundos.';
  header("Location: ../registroCliente.php"); exit;
}

// Indica a bootstrap_post a dónde redirigir en caso de error
$GLOBALS['__POST_REDIRECT'] = '../registroCliente.php';
require_once __DIR__ . '/bootstrap_post.php';  // valida POST/CSRF y crea $mysqli

// ---------- Inputs ----------
$nombre    = cap($_POST['nombre']    ?? '', 100);
$apellido1 = cap($_POST['apellido']  ?? '', 100);
$apellido2 = cap($_POST['apellido2'] ?? '', 100);
$correo    = mb_strtolower(cap($_POST['correo'] ?? '', 100), 'UTF-8');
$tel       = cap($_POST['telefono'] ?? '', 8);
$dir       = cap($_POST['direccion']?? '', 150);
$pwd       = (string)($_POST['password']  ?? '');
$pwd2      = (string)($_POST['password2'] ?? '');

// ---------- Validaciones ----------
$err = [];
if ($nombre==='' || $apellido1==='' || $apellido2==='')     $err[]='1';
if (!filter_var($correo, FILTER_VALIDATE_EMAIL))            $err[]='2';
if (!preg_match('/^\d{8}$/', $tel))                         $err[]='3';
if ($dir==='')                                              $err[]='4';
if ($pwd !== $pwd2)                                         $err[]='5';
[$pwd_ok] = validar_password($pwd); if (!$pwd_ok)           $err[]='6';
if (correo_existe($mysqli, $correo, 'cliente'))             $err[]='7';


if ($err){
  $_SESSION['flash_error']='No se pudo completar el registro. Revisa los datos.';
  header("Location: ../registroCliente.php"); exit;
}

// ---------- Usuario estandarizado (no editable) ----------
$usuario = generar_usuario_estandarizado($mysqli, $nombre, $apellido1, $apellido2, 'cliente');

// ---------- Bloqueos lógicos ----------
$lockU = "cli:usr:".$usuario;
$lockC = "cli:mail:".$correo;

if (!get_named_lock($mysqli, $lockU, 5)) { $_SESSION['flash_error']='Sistema ocupado (usuario).'; header("Location: ../registroCliente.php"); exit; }
if (!get_named_lock($mysqli, $lockC, 5)) { release_named_lock($mysqli,$lockU); $_SESSION['flash_error']='Sistema ocupado (correo).'; header("Location: ../registroCliente.php"); exit; }

// ---------- Transacción ----------
$mysqli->begin_transaction();
try {
  // Doble verificación
  $s=$mysqli->prepare("SELECT 1 FROM cliente WHERE Usuario=? OR Correo=? LIMIT 1");
  $s->bind_param('ss',$usuario,$correo); $s->execute(); $s->store_result();
  if ($s->num_rows>0){ $s->close(); throw new RuntimeException('Duplicado'); }
  $s->close();

  $exp = (new DateTime('+60 days'))->format('Y-m-d H:i:s');

  $sql = "INSERT INTO cliente
          (Nombre, Apellido, Apellido2, Usuario, Correo, Telefono, Direccion,
           EstadoCuenta, IntentosFallidos, password_expires_at, is_deleted)
          VALUES (?, ?, ?, ?, ?, ?, ?, 'Activo', 0, ?, 0)";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('ssssssss', $nombre, $apellido1, $apellido2, $usuario, $correo, $tel, $dir, $exp);
  $stmt->execute();
  $idCliente = (int)$stmt->insert_id;
  $stmt->close();

  // Código estandarizado único
  $codigoUsuario = build_codigo_cliente($idCliente);
  $u = $mysqli->prepare("UPDATE cliente SET CodigoUsuario=? WHERE idCliente=?");
  $u->bind_param('si', $codigoUsuario, $idCliente);
  $u->execute(); $u->close();

  // Historial de contraseñas
  $hash = password_hash($pwd, PASSWORD_DEFAULT);
  $stmt = $mysqli->prepare("INSERT INTO password_history (user_type, user_id, PasswordHash)
                            VALUES ('cliente', ?, ?)");
  $stmt->bind_param('is', $idCliente, $hash);
  $stmt->execute();
  $stmt->close();

  $mysqli->commit();
  $_SESSION['flash_success']='Registro exitoso. Ya puedes iniciar sesión.';
  header("Location: ../registroCliente.php"); exit;

} catch (Throwable $e) {
  $mysqli->rollback();
  $_SESSION['flash_error']='No se pudo completar el registro.';
  header("Location: ../registroCliente.php"); exit;
} finally {
  release_named_lock($mysqli,$lockC);
  release_named_lock($mysqli,$lockU);
}