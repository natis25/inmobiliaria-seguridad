
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
    "img-src 'self' ; ".  // <-- permite SVG embebidos de Bootstrap
    "style-src 'self' https://fonts.googleapis.com   https://cdn.jsdelivr.net   'unsafe-inline'; ".
    "font-src https://fonts.gstatic.com  ; ".
    "script-src 'self' https://cdn.jsdelivr.net   'nonce-{$CSP_NONCE}'; ".
    "connect-src 'self' https://cdn.jsdelivr.net  ;" // Añadido connect-src
);

// ---- Conexión a la BD para obtener cargos y roles ----
require_once __DIR__ . '/../Logica/sql.php';
$conn = Conectarse();
if (!$conn) {
    $_SESSION['flash_error'] = 'Error de conexión a la base de datos.';
    header("Location: gestionarEmpleados.php");
    exit;
}

// Obtener cargos
$result = $conn->query("SELECT idCargo, NombreCargo FROM cargo ORDER BY NombreCargo");
$cargos = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Obtener roles
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
  <title>Crear Cuenta de Empleado</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css  " rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root { --brand: #4b41d9; }
    * { font-family: Inter, system-ui, Segoe UI, Roboto, Arial, sans-serif; }
    .card-lite { border: 1px solid #eee; border-radius: 16px; }
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
    <h1 class="h3 fw-bold text-center mb-2">Crear Cuenta de Empleado</h1>
    <p class="text-center text-muted mb-4">Registra un nuevo empleado para gestionar propiedades y citas.</p>

    <?php if (!empty($_SESSION['flash_error'])): ?>
      <div class="alert alert-danger"><strong>Ups:</strong> <?= htmlspecialchars($_SESSION['flash_error']) ?></div>
      <?php unset($_SESSION['flash_error']); ?>
    <?php elseif (!empty($_SESSION['flash_success'])): ?>
      <div class="alert alert-success" id="msg-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
      <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['temp_creds'])): $tc = $_SESSION['temp_creds']; ?>
        <div class="card shadow-sm border-0 mt-3 cred-card">
          <div class="card-header d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill text-bg-warning">Nuevo</span>
            <span class="fw-semibold">Credenciales generadas (mostrar al empleado)</span>
          </div>
          <div class="btn-group btn-group-sm" role="group" aria-label="acciones credenciales">
            <button type="button" class="btn btn-outline-secondary" data-copy="#cred-all">Copiar todo</button>
            <button type="button" id="togglePwd" class="btn btn-outline-secondary" aria-controls="cred-pwd">Mostrar</button>
          </div>
          </div>

          <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
            <label class="form-label small text-muted mb-1">Usuario</label>
            <div class="input-group input-group-sm">
              <input type="text" class="form-control font-monospace" value="<?= htmlspecialchars($tc['usuario']) ?>" readonly id="cred-usuario">
              <button type="button" class="btn btn-outline-secondary" data-copy="#cred-usuario">Copiar</button>
            </div>
            </div>

            <div class="col-md-5">
            <label class="form-label small text-muted mb-1">Correo corporativo</label>
            <div class="input-group input-group-sm">
              <input type="text" class="form-control font-monospace" value="<?= htmlspecialchars($tc['correo']) ?>" readonly id="cred-correo">
              <button type="button" class="btn btn-outline-secondary" data-copy="#cred-correo">Copiar</button>
            </div>
            </div>

            <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Código</label>
            <div class="input-group input-group-sm">
              <input type="text" class="form-control font-monospace" value="<?= htmlspecialchars($tc['codigo']) ?>" readonly id="cred-codigo">
              <button type="button" class="btn btn-outline-secondary" data-copy="#cred-codigo">Copiar</button>
            </div>
            </div>

            <div class="col-12">
            <label class="form-label small text-muted mb-1">Contraseña temporal</label>
            <div class="input-group input-group-sm">
              <!-- la mostramos enmascarada por defecto; el botón "Mostrar" del header la alterna -->
              <input type="password" class="form-control font-monospace" value="<?= htmlspecialchars($tc['pwd']) ?>" readonly id="cred-pwd">
              <button type="button" class="btn btn-outline-secondary" data-copy="#cred-pwd">Copiar</button>
            </div>
            <small class="text-muted d-block mt-2">
              Se mostrará una sola vez y no se guarda. Solicita al empleado cambiarla en su primer ingreso.
            </small>
            </div>
          </div>
          </div>
        </div>

        <!-- Texto oculto para "Copiar todo" (respetando formato) -->
        <textarea id="cred-all" class="visually-hidden"><?=
            "Usuario: {$tc['usuario']}\n".
            "Correo:  {$tc['correo']}\n".
            "Código:  {$tc['codigo']}\n".
            "Pass:    {$tc['pwd']}\n"
        ?></textarea>

        <?php unset($_SESSION['temp_creds']); // se borra tras renderizar ?>
        <?php endif; ?>


    <form method="post" action="../Logica/procesarRegistroEmpleado.php" novalidate autocomplete="off" class="mt-4">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Nombre</label>
          <input name="nombre" maxlength="100" class="form-control" placeholder="Ej.: Juan Carlos" required autocomplete="given-name" aria-describedby="ay-nom">
          <small id="ay-nom" class="text-muted">Usa primer nombre real.</small>
        </div>
        <div class="col-md-4">
          <label class="form-label">Primer apellido</label>
          <input name="apellido" maxlength="100" class="form-control" placeholder="Ej.: Pérez" required autocomplete="family-name" aria-describedby="ay-ap1">
          <small id="ay-ap1" class="text-muted">Tal como figura en documento.</small>
        </div>
        <div class="col-md-4">
          <label class="form-label">Segundo apellido</label>
          <input name="apellido2" maxlength="100" class="form-control" placeholder="Ej.: Gómez" required autocomplete="additional-name" aria-describedby="ay-ap2">
          <small id="ay-ap2" class="text-muted">Obligatorio para diferenciar homónimos.</small>
        </div>

        <div class="col-md-6">
          <label class="form-label">Usuario estandarizado</label>
          <input name="usuario" id="usuario" maxlength="100" class="form-control" readonly
                 placeholder="se generará automáticamente" autocapitalize="none" autocomplete="username" aria-describedby="ay-user">
          <small id="ay-user" class="text-muted">Se genera a partir de nombre y apellidos. Si ya existe, se ajustará automáticamente.</small>
        </div>

        <div class="col-md-6">
          <label class="form-label">Correo corporativo</label>
          <input name="correo" id="correo" class="form-control" readonly placeholder="usuario@droca.com" aria-describedby="ay-mail">
          <small id="ay-mail" class="text-muted">Se genera desde el usuario.</small>
        </div>
      </div>

      <div class="row g-3 mt-0">
        <div class="col-md-6">
          <label class="form-label">Teléfono (8 dígitos)</label>
          <input name="telefono" pattern="\d{8}" maxlength="8" class="form-control" placeholder="12345678" required inputmode="numeric" autocomplete="tel">
        </div>
        <div class="col-md-6">
          <label class="form-label">Cargo</label>
          <select name="idCargo" class="form-control" required>
            <option value="">Selecciona un cargo</option>
            <?php foreach ($cargos as $cargo): ?>
              <option value="<?= $cargo['idCargo'] ?>"><?= htmlspecialchars($cargo['NombreCargo']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Rol</label>
          <select name="idRol" class="form-control" required>
            <option value="">Selecciona un rol</option>
            <?php foreach ($roles as $rol): ?>
              <option value="<?= $rol['idRol'] ?>"><?= htmlspecialchars($rol['NombreRol']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12">
          <div class="alert alert-info mt-3" role="status" aria-live="polite">
            La <strong>contraseña temporal</strong> se generará automáticamente y se pedirá <strong>cambiarla</strong> en el primer inicio.
          </div>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary w-100 py-2">Registrar Empleado</button>
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

<!-- Bootstrap Bundle primero (para asegurar bootstrap.Modal disponible) -->
<script src="  https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js  " nonce="<?= $CSP_NONCE ?>"></script>

<!-- Tu script personalizado -->
<script nonce="<?= $CSP_NONCE ?>">
window.addEventListener('load', function(){  // asegura que Bootstrap ya esté cargado
  function toASCII(s){ return s.normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/ñ/gi,'n'); }
  function norm(s){ return toASCII(s).toLowerCase().replace(/[^a-z0-9 ]/g,' ').trim().replace(/\s+/g,' '); }

  const $n = document.querySelector('input[name="nombre"]');
  const $a1 = document.querySelector('input[name="apellido"]');
  const $a2 = document.querySelector('input[name="apellido2"]');
  const $u  = document.getElementById('usuario');
  const $m  = document.getElementById('correo');

  function sugUsuario(){
    const n = (norm($n.value).split(' ')[0]||'').replace(/[^a-z]/g,'');
    const p = (norm($a1.value).split(' ').slice(-1)[0]||'').replace(/[^a-z]/g,'');
    const s = (norm($a2.value).split(' ').slice(-1)[0]||'').replace(/[^a-z]/g,'');
    const user = n && p ? `${n}.${p}` : (n || p || '');
    $u.value = user;
    $m.value = user ? `${user}@droca.com` : ''; // ajusta dominio si aplica
  }
  [$n,$a1,$a2].forEach(e=>e.addEventListener('input', sugUsuario));

  // Copiar al portapapeles
  document.querySelectorAll('[data-copy]').forEach(btn=>{
    btn.addEventListener('click', async ()=>{
      const sel = btn.getAttribute('data-copy');
      const input = document.querySelector(sel);
      if (!input) return;
      try {
        await navigator.clipboard.writeText(input.value);
        btn.textContent = 'Copiado';
        setTimeout(()=>btn.textContent='Copiar', 1500);
      } catch {
        input.select(); document.execCommand('copy');
        btn.textContent = 'Copiado';
        setTimeout(()=>btn.textContent='Copiar', 1500);
      }
    });
  });

  // ---- Confirmación antes del envío (verificación de datos) ----
  const form = document.querySelector('form');
  const fb = document.createElement('div');
  fb.id = 'form-feedback';
  fb.className = 'mt-3';
  form.appendChild(fb);

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    // Campos requeridos
    const campos = {
      nombre: $n.value.trim(),
      apellido: $a1.value.trim(),
      apellido2: $a2.value.trim(),
      usuario: $u.value.trim(),
      correo: $m.value.trim(),
      telefono: document.querySelector('input[name="telefono"]').value.trim(),
      cargo: form.querySelector('select[name="idCargo"]').value.trim(),
      rol: form.querySelector('select[name="idRol"]').value.trim()
    };

    // Validación mínima
    const faltantes = Object.entries(campos).filter(([k,v]) => !v);
    if (faltantes.length > 0) {
      fb.className = 'alert alert-warning mt-3';
      fb.textContent = 'Por favor, completa todos los campos requeridos antes de continuar.';
      return;
    }

    // Obtener texto de selects
    const cargoTxt = form.querySelector('select[name="idCargo"] option:checked').textContent;
    const rolTxt = form.querySelector('select[name="idRol"] option:checked').textContent;

    // Resumen visual
    const resumen = `
      <ul class="list-group text-start">
        <li class="list-group-item"><strong>Nombre completo:</strong> ${campos.nombre} ${campos.apellido} ${campos.apellido2}</li>
        <li class="list-group-item"><strong>Usuario:</strong> ${campos.usuario}</li>
        <li class="list-group-item"><strong>Correo:</strong> ${campos.correo}</li>
        <li class="list-group-item"><strong>Teléfono:</strong> ${campos.telefono}</li>
        <li class="list-group-item"><strong>Cargo:</strong> ${cargoTxt}</li>
        <li class="list-group-item"><strong>Rol:</strong> ${rolTxt}</li>
      </ul>
      <p class="mt-3 small text-muted">
        Verifica que los datos sean correctos antes de registrar al empleado.
      </p>
    `;

    // Crear modal solo una vez
    let modal = document.getElementById('confirmModal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'confirmModal';
      modal.className = 'modal fade';
      modal.tabIndex = -1;
      modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header bg-light">
              <h5 class="modal-title">Confirmar datos del empleado</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="resumenBody"></div>
            <div class="modal-footer">
              <div class="form-check me-auto">
                <input class="form-check-input" type="checkbox" id="chkConfirm" required>
                <label class="form-check-label small" for="chkConfirm">
                  Confirmo que los datos son correctos.
                </label>
              </div>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Corregir</button>
              <button type="button" id="confirmSubmit" class="btn btn-primary" disabled>Confirmar y Registrar</button>
            </div>
          </div>
        </div>`;
      document.body.appendChild(modal);
    }

    document.getElementById('resumenBody').innerHTML = resumen;
    const confirmModal = new bootstrap.Modal(modal);
    confirmModal.show();

    // Habilitar botón solo si marca el checkbox
    const chk = modal.querySelector('#chkConfirm');
    const btn = modal.querySelector('#confirmSubmit');
    chk.onchange = () => btn.disabled = !chk.checked;

    btn.onclick = () => {
      confirmModal.hide();
      // Busca un botón submit; si no hay, no truena
      const submitBtn = form.querySelector('button[type="submit"], button:not([type])');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Registrando…';
      }
      form.submit();
    };
  });

  // Toggle mostrar/ocultar contraseña temporal
  (function(){
    const btn = document.getElementById('togglePwd');
    const pwd = document.getElementById('cred-pwd');
    if (!btn || !pwd) return;
    btn.addEventListener('click', ()=>{
      const isPwd = pwd.type === 'password';
      pwd.type = isPwd ? 'text' : 'password';
      btn.textContent = isPwd ? 'Ocultar' : 'Mostrar';
      // opcional: seleccionar para facilitar copia al mostrar
      if (isPwd) { pwd.focus(); pwd.select(); }
    });
  })();
});


</script>
</body>
</html>
