<?php
// ---- Sesión segura + CSRF ----
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
session_start();
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
session_regenerate_id(true);

// ---- CSP con NONCE para permitir <script> inline seguro ----
$CSP_NONCE = base64_encode(random_bytes(16));
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: interest-cohort=()");
header(
    "Content-Security-Policy: ".
    "default-src 'self'; ".
    "img-src 'self' data:; ".
    "style-src 'self' https://fonts.googleapis.com https://cdn.jsdelivr.net 'unsafe-inline'; ".
    "font-src https://fonts.gstatic.com; ".
    "script-src 'self' https://cdn.jsdelivr.net 'nonce-{$CSP_NONCE}'; ".
    "connect-src 'self' https://cdn.jsdelivr.net;"
);

// ---- Conexión a la BD para obtener trabajador, cargos y roles ----
require_once __DIR__ . '/../Logica/sql.php';
$conn = Conectarse();
if (!$conn) {
    $_SESSION['flash_error'] = 'Error de conexión a la base de datos.';
    header("Location: gestionarEmpleados.php");
    exit;
}

// Verificar ID del trabajador
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['flash_error'] = 'ID de trabajador inválido.';
    header("Location: gestionarEmpleados.php");
    exit;
}
$idTrabajador = (int)$_GET['id'];
$trabajador = obtenerTabajadoresPorId($idTrabajador);

if (!$trabajador) {
    $_SESSION['flash_error'] = 'Trabajador no encontrado o eliminado.';
    header("Location: gestionarEmpleados.php");
    exit;
}

// Valores por defecto para evitar errores de claves indefinidas
$trabajador = array_merge([
    'Nombre' => '',
    'Apellido' => '',
    'Usuario' => '',
    'Telefono' => '',
    'Correo' => '',
    'idCargo' => null,
    'idRol' => null
], $trabajador);

// Obtener cargos y roles
$result = $conn->query("SELECT idCargo, NombreCargo FROM cargo ORDER BY NombreCargo");
$cargos = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$result = $conn->query("SELECT idRol, NombreRol FROM rol ORDER BY NombreRol");
$roles = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();

// Depuración: Verificar si cargos y roles están vacíos
if (empty($cargos)) {
    $_SESSION['flash_error'] = 'No se encontraron cargos en la base de datos.';
    header("Location: gestionarEmpleados.php");
    exit;
}
if (empty($roles)) {
    $_SESSION['flash_error'] = 'No se encontraron roles en la base de datos.';
    header("Location: gestionarEmpleados.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modificar Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --brand: #4b41d9; }
        * { font-family: Inter, system-ui, Segoe UI, Roboto, Arial, sans-serif; }
        .card-lite { border: 1px solid #eee; border-radius: 16px; }
        .req-list li { margin: .15rem 0; }
        .req-bad { color: #b42318; }
        .req-ok { color: #16794f; }
    </style>
</head>
<body class="bg-light">

<header class="navbar navbar-expand-lg bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">
            <img src="../images/Logo.png" height="28" class="me-2" alt="Logo">InmobiliariaModerna
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Menú">
            <span class="navbar-toggler-icon"></span>
        </button>
        <nav id="nav" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="../index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="../inmuebles.php">Propiedades</a></li>
                <li class="nav-item"><a class="btn btn-outline-primary" href="../login.php">Iniciar Sesión</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="container my-5" style="max-width:880px">
    <div class="card card-lite p-4 p-md-5 mx-auto">
        <h1 class="h3 fw-bold text-center mb-2">Modificar Empleado</h1>
        <p class="text-center text-muted mb-4">Actualiza los datos del empleado.</p>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><strong>Ups:</strong> <?= htmlspecialchars($_SESSION['flash_error']) ?></div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php elseif (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success" id="msg-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <form method="post" action="../Logica/actualizarTrabajador.php" novalidate autocomplete="off">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <input type="hidden" name="idTrabajador" value="<?= htmlspecialchars($idTrabajador) ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre</label>
                    <input name="nombre" maxlength="100" class="form-control" placeholder="Nombre del empleado" 
                           value="<?= htmlspecialchars($trabajador['Nombre']) ?>" required autocomplete="given-name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apellido</label>
                    <input name="apellido" maxlength="100" class="form-control" placeholder="Apellido del empleado" 
                           value="<?= htmlspecialchars($trabajador['Apellido']) ?>" required autocomplete="family-name">
                </div>

                <div class="col-12">
                    <label class="form-label">Usuario</label>
                    <input name="usuario" id="usuario" maxlength="100" class="form-control" 
                           placeholder="usuario.unico" autocapitalize="none" autocomplete="username" 
                           value="<?= htmlspecialchars($trabajador['Usuario']) ?>">
                    <small class="text-muted">Asegúrate de que el usuario sea único.</small>
                </div>

                <div class="col-12">
                    <label class="form-label">Correo Electrónico</label>
                    <input name="correo" id="correo" class="form-control" readonly 
                           value="<?= htmlspecialchars($trabajador['Usuario'] . '@droca.com') ?>" 
                           placeholder="Se generará como usuario@droca.com">
                    <small class="text-muted">El correo se genera automáticamente a partir del usuario.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Teléfono (8 dígitos)</label>
                    <input name="telefono" pattern="\d{8}" maxlength="8" class="form-control" 
                           placeholder="12345678" required inputmode="numeric" autocomplete="tel" 
                           value="<?= htmlspecialchars($trabajador['Telefono']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Cargo</label>
                    <select name="idCargo" class="form-control" required>
                        <option value="">Selecciona un cargo</option>
                        <?php foreach ($cargos as $cargo): ?>
                            <option value="<?= $cargo['idCargo'] ?>" 
                                    <?= $trabajador['idCargo'] == $cargo['idCargo'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cargo['NombreCargo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rol</label>
                    <select name="idRol" class="form-control" required>
                        <option value="">Selecciona un rol</option>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= $rol['idRol'] ?>" 
                                    <?= $trabajador['idRol'] == $rol['idRol'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rol['NombreRol']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <button class="btn btn-primary w-100 py-2">Guardar Cambios</button>
                </div>
            </div>
        </form>

        <p class="text-center mt-3 mb-0">
            <a href="gestionarEmpleados.php" class="btn btn-outline-secondary">Volver a Gestión de Empleados</a>
        </p>
    </div>
</main>

<footer class="border-top py-4">
    <div class="container d-flex justify-content-between">
        <span class="text-muted">© <?= date('Y') ?> InmobiliariaModerna</span>
        <span class="text-muted">Recursos · Compañía</span>
    </div>
</footer>

<script nonce="<?= $CSP_NONCE ?>">
// --- Generación automática y dinámica del usuario y correo ---
const nombre = document.querySelector('input[name="nombre"]');
const apellido = document.querySelector('input[name="apellido"]');
const usuario = document.getElementById('usuario');
const correo = document.getElementById('correo');
let usuarioModificado = false;

function toASCII(s) { return s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/ñ/gi, 'n'); }
function norm(s) { return toASCII(s).toLowerCase().replace(/[^a-z0-9 ]/g, ' ').trim().replace(/\s+/g, ' '); }

function sugerirUsuario() {
    if (usuarioModificado) return; // No sobrescribir si el usuario edita
    const n = (norm(nombre.value).split(' ')[0] || '').replace(/[^a-z]/g, '');
    const a = (norm(apellido.value).split(' ').slice(-1)[0] || '').replace(/[^a-z]/g, '');
    const user = n && a ? `${n}.${a}` : (n || a || '');
    usuario.value = user;
    correo.value = user ? `${user}@droca.com` : '';
}
nombre.addEventListener('input', sugerirUsuario);
apellido.addEventListener('input', sugerirUsuario);
usuario.addEventListener('input', () => {
    usuarioModificado = true;
    correo.value = usuario.value ? `${user}@droca.com` : '';
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" nonce="<?= $CSP_NONCE ?>"></script>
</body>
</html>