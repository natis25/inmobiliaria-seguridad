<?php
// ---- Sesión segura + CSRF ----
ini_set('session.cookie_httponly','1');
ini_set('session.cookie_samesite','Lax');
session_start();
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
session_regenerate_id(true);

// ---- CSP con NONCE (economía del software: una sola etiqueta <script> controlada) ----
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
  "script-src 'self' https://cdn.jsdelivr.net 'nonce-{$CSP_NONCE}';"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Crear Cuenta de Cliente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root{--brand:#4b41d9}
    *{font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif}
    .card-lite{border:1px solid #eee;border-radius:16px}
    .pw-meter{height:6px;border-radius:6px;background:#eee;overflow:hidden}
    .pw-meter>span{display:block;height:100%;width:0;background:var(--brand);transition:width .2s}
    .req-list li{margin:.15rem 0}.req-bad{color:#b42318}.req-ok{color:#16794f}
    details.tip{border:1px dashed #d0d0ff;border-radius:10px;padding:.75rem 1rem;background:#f8f9ff}
    details.tip summary{cursor:pointer;font-weight:600}
  </style>
</head>
<body class="bg-light">

<header class="navbar navbar-expand-lg bg-white border-bottom">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">
      <img src="images/Logo.png" height="28" class="me-2" alt="Logo">InmobiliariaModerna
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Menú">
      <span class="navbar-toggler-icon"></span>
    </button>
    <nav id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="inmuebles.php">Propiedades</a></li>
        <li class="nav-item"><a class="btn btn-outline-primary" href="login.php">Iniciar Sesión</a></li>
      </ul>
    </nav>
  </div>
</header>

<main class="container my-5" style="max-width:880px">
  <div class="card card-lite p-4 p-md-5 mx-auto">
    <h1 class="h3 fw-bold text-center mb-2">Crear Cuenta de Cliente</h1>
    <p class="text-center text-muted mb-4">Regístrate para explorar propiedades y gestionar tus citas.</p>

    <?php if (!empty($_SESSION['flash_error'])): ?>
      <div class="alert alert-danger"><strong>Ups:</strong> <?= htmlspecialchars($_SESSION['flash_error']) ?></div>
      <?php unset($_SESSION['flash_error']); ?>
    <?php elseif (!empty($_SESSION['flash_success'])): ?>
      <div class="alert alert-success" id="msg-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
      <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <!-- Economía del software: mensajes breves y sin modales intrusivos -->
    <div class="alert alert-info d-flex align-items-center gap-2" role="status" aria-live="polite">
      <div>
        Registra tus datos tal como aparecen en tu documento de identidad. 
        El <strong>usuario</strong> se genera de forma estándar y <strong>no es editable</strong>.
      </div>
    </div>

    <form method="post" action="Logica/procesarRegistroCliente.php" novalidate autocomplete="off">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

      <!-- Nombres y apellidos -->
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Nombre</label>
          <input name="nombre" maxlength="100" class="form-control" placeholder="Ej.: Juan Carlos" required 
                 autocomplete="given-name" aria-describedby="ayuda-nombre">
          <small id="ayuda-nombre" class="text-muted">Usa tu primer nombre. Evita apodos.</small>
        </div>
        <div class="col-md-4">
          <label class="form-label">Primer apellido</label>
          <input name="apellido" maxlength="100" class="form-control" placeholder="Ej.: Pérez" required 
                 autocomplete="family-name" aria-describedby="ayuda-ap1">
          <small id="ayuda-ap1" class="text-muted">Tal como figura en tu documento.</small>
        </div>
        <div class="col-md-4">
          <label class="form-label">Segundo apellido</label>
          <input name="apellido2" maxlength="100" class="form-control" placeholder="Ej.: Gómez" required 
                 autocomplete="additional-name" aria-describedby="ayuda-ap2">
          <small id="ayuda-ap2" class="text-muted">Obligatorio para diferenciar homónimos.</small>
        </div>

        <div class="col-12">
          <label class="form-label">Usuario estandarizado</label>
          <input name="usuario" id="usuario" maxlength="100" class="form-control" readonly
                 placeholder="se generará automáticamente" autocapitalize="none" autocomplete="username"
                 aria-describedby="ayuda-usuario">
          <small id="ayuda-usuario" class="text-muted">
            Se genera a partir de tu nombre y apellidos; si hay choque, el backend ajusta de forma mínima.
          </small>
        </div>

        <div class="col-md-6">
          <label class="form-label">Correo Electrónico</label>
          <input name="correo" type="email" maxlength="100" class="form-control" placeholder="tucorreo@ejemplo.com" required 
                 autocomplete="email" inputmode="email" aria-describedby="ayuda-correo">
          <small id="ayuda-correo" class="text-muted">Usa un correo al que tengas acceso ahora.</small>
        </div>
        <div class="col-md-6">
          <label class="form-label">Teléfono (8 dígitos)</label>
          <input name="telefono" pattern="\d{8}" maxlength="8" class="form-control" placeholder="12345678" required 
                 inputmode="numeric" autocomplete="tel" aria-describedby="ayuda-tel" title="Debe tener 8 dígitos">
          <small id="ayuda-tel" class="text-muted">Solo números, 8 dígitos.</small>
        </div>

        <div class="col-12">
          <label class="form-label">Dirección</label>
          <input name="direccion" maxlength="150" class="form-control" placeholder="Calle, número, ciudad" required 
                 autocomplete="street-address" aria-describedby="ayuda-dir">
          <small id="ayuda-dir" class="text-muted">Incluye calle y ciudad para ubicar la propiedad.</small>
        </div>

        <!-- Contraseña con barra + checklist + tip de frase (sin JS extra) -->
        <div class="col-12">
          <label class="form-label">Contraseña</label>
          <input name="password" id="password" type="password" class="form-control" placeholder="Crea una contraseña segura" required 
                 autocomplete="new-password" aria-describedby="ayuda-pass">
          <div class="d-flex align-items-center mt-2 gap-2">
            <div class="pw-meter flex-grow-1" aria-hidden="true"><span id="pwBar"></span></div>
            <small id="pwLabel" class="text-muted">Muy débil</small>
          </div>
          <ul class="req-list small mt-2 text-muted" aria-live="polite">
            <li id="req_len" class="req-bad">Al menos 12 caracteres</li>
            <li id="req_may" class="req-bad">Una letra mayúscula</li>
            <li id="req_min" class="req-bad">Una letra minúscula</li>
            <li id="req_num" class="req-bad">Un número</li>
            <li id="req_sym" class="req-bad">Un símbolo</li>
          </ul>

          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="togglePass" aria-controls="password password2">
            <label class="form-check-label" for="togglePass">Mostrar contraseñas</label>
          </div>

          <small id="ayuda-pass" class="text-muted">Mínimo 12 caracteres con mayúscula, minúscula, número y símbolo.</small>

          <!-- Tip: método de frase -> iniciales (economía: <details> nativo, sin librerías) -->
          <details class="tip mt-3">
            <summary>Cómo crear una contraseña fuerte con una frase</summary>
            <div class="mt-2 small">
              1) Elige una frase/canción: <em>“Nunca pares de aprender cada día”</em><br>
              2) Toma iniciales: <strong>npdacd</strong><br>
              3) Añade números/símbolos (año, separadores): <strong>npdacd-2025!</strong><br>
              4) Mezcla mayúsculas para mayor entropía: <strong>NpdaCd-2025!</strong>
            </div>
          </details>
        </div>

        <div class="col-12">
          <label class="form-label">Confirmar Contraseña</label>
          <input name="password2" id="password2" type="password" class="form-control" placeholder="Confirma tu contraseña" required 
                 autocomplete="new-password" aria-describedby="ayuda-pass2" title="Debe coincidir con la contraseña">
          <small id="ayuda-pass2" class="text-muted">Debe coincidir exactamente.</small>
        </div>

        <div class="col-12">
          <button class="btn btn-primary w-100 py-2" type="submit">Registrarse</button>
        </div>
      </div>

      <!-- Feedback discreto y reutilizable -->
      <div id="form-feedback" class="mt-3" role="status" aria-live="polite"></div>
    </form>

    <p class="text-center mt-3 mb-0">¿Ya tienes una cuenta? <a href="login.php">Iniciar Sesión</a></p>
  </div>
</main>

<footer class="border-top py-4">
  <div class="container d-flex justify-content-between">
    <span class="text-muted">© <?= date('Y') ?> InmobiliariaModerna</span>
    <span class="text-muted">Recursos · Compañía</span>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" nonce="<?= $CSP_NONCE ?>"></script>
<script nonce="<?= $CSP_NONCE ?>">
// Utilidades (normalización de nombre -> usuario)
function toASCII(s){ return s.normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/ñ/gi,'n'); }
function norm(s){ return toASCII(s).toLowerCase().replace(/[^a-z0-9 ]/g,' ').trim().replace(/\s+/g,' '); }

// Sugerencia de usuario (no editable; el backend garantiza unicidad)
const $n = document.querySelector('input[name="nombre"]');
const $a1 = document.querySelector('input[name="apellido"]');
const $a2 = document.querySelector('input[name="apellido2"]');
const $u  = document.getElementById('usuario');
function sugUsuario(){
  const n = (norm($n.value).split(' ')[0]||'').replace(/[^a-z]/g,'');
  const p = (norm($a1.value).split(' ').slice(-1)[0]||'').replace(/[^a-z]/g,'');
  $u.value = n && p ? `${n}.${p}` : (n || p || '');
}
[$n,$a1,$a2].forEach(e=>e.addEventListener('input', sugUsuario));

// ---- Barra + checklist de contraseña (restaurado) ----
const pwd    = document.getElementById('password');
const pwBar  = document.getElementById('pwBar');
const pwLabel= document.getElementById('pwLabel');
function rq(id){ return document.getElementById(id); }
const reqs   = {len:rq('req_len'),may:rq('req_may'),min:rq('req_min'),num:rq('req_num'),sym:rq('req_sym')};

function evalPwd(v){
  const r = {
    len: v.length>=12,
    may: /[A-Z]/.test(v),
    min: /[a-z]/.test(v),
    num: /\d/.test(v),
    sym: /[^A-Za-z0-9]/.test(v)
  };
  const score = Object.values(r).filter(Boolean).length;
  pwBar.style.width = (score*20)+'%';
  pwLabel.textContent = ['Muy débil','Débil','Media','Buena','Fuerte','Excelente'][score];
  for(const k in r){ reqs[k].className = r[k] ? 'req-ok' : 'req-bad'; }
}
pwd.addEventListener('input', e=>evalPwd(e.target.value));

// Mostrar/ocultar contraseñas
const toggle = document.getElementById('togglePass');
const pass2  = document.getElementById('password2');
toggle?.addEventListener('change', ()=>{
  const t = toggle.checked ? 'text' : 'password';
  pwd.type = t; pass2.type = t;
});

// Validaciones ligeras (economía: una sola función por caso)
const fb   = document.getElementById('form-feedback');
const form = document.querySelector('form');

function setHelp(el, msg, ok=false){
  let id = el.getAttribute('aria-describedby'); if(!id) return;
  const small = document.getElementById(id); if(!small) return;
  small.textContent = msg;
  small.classList.toggle('text-danger', !ok);
  small.classList.toggle('text-muted',  ok);
  el.classList.toggle('is-invalid', !ok);
  el.classList.toggle('is-valid',   ok);
}
function vEmail(){
  const el = document.querySelector('input[name="correo"]');
  const ok = el.value.trim()!=='' && el.checkValidity();
  setHelp(el, ok ? 'Formato válido.' : 'Ingresa un correo válido (ej.: nombre@dominio.com).', ok);
  return ok;
}
function vTel(){
  const el = document.querySelector('input[name="telefono"]');
  const ok = /^\d{8}$/.test(el.value.trim());
  setHelp(el, ok ? 'Correcto: 8 dígitos.' : 'Deben ser 8 dígitos, sin espacios ni guiones.', ok);
  return ok;
}
function vPass(){
  const ok = (
    pwd.value.length>=12 &&
    /[A-Z]/.test(pwd.value) &&
    /[a-z]/.test(pwd.value) &&
    /\d/.test(pwd.value)   &&
    /[^A-Za-z0-9]/.test(pwd.value)
  );
  setHelp(pwd, ok ? 'Contraseña fuerte.' : 'Debe cumplir las 5 reglas: 12+, may/min/num/símbolo.', ok);
  return ok;
}
function vMatch(){
  const ok = pwd.value!=='' && pwd.value===pass2.value;
  setHelp(pass2, ok ? 'Coinciden.' : 'No coincide con la contraseña.', ok);
  return ok;
}
function vRequ(name, okMsg='Dato correcto.', errMsg='Este campo es obligatorio.'){
  const el = document.querySelector(`input[name="${name}"]`);
  const ok = el.value.trim()!=='';
  setHelp(el, ok ? okMsg : errMsg, ok);
  return ok;
}

// Enlaces a eventos
document.querySelector('input[name="correo"]').addEventListener('input', vEmail);
document.querySelector('input[name="telefono"]').addEventListener('input', vTel);
pwd.addEventListener('input', ()=>{ vPass(); vMatch(); });
pass2.addEventListener('input', vMatch);
['nombre','apellido','apellido2','direccion'].forEach(n=>{
  document.querySelector(`input[name="${n}"]`).addEventListener('input', ()=>vRequ(n));
});

// ---- Confirmación antes del envío (seguridad + economía del software) ----
form.addEventListener('submit', (e)=>{
  e.preventDefault();

  const ok = [
    vRequ('nombre'), vRequ('apellido'), vRequ('apellido2'), vRequ('direccion'),
    vEmail(), vTel(), vPass(), vMatch()
  ].every(Boolean);

  if (!ok){
    fb.className = 'mt-3 alert alert-warning';
    fb.textContent = 'Revisa los campos marcados en rojo.';
    form.querySelector('.is-invalid')?.focus();
    return;
  }

  // Construir resumen de datos (sin duplicar campos ni contraseñas)
  const resumen = `
    <ul class="list-group text-start">
      <li class="list-group-item"><strong>Nombre:</strong> ${$n.value.trim()} ${$a1.value.trim()} ${$a2.value.trim()}</li>
      <li class="list-group-item"><strong>Usuario:</strong> ${$u.value}</li>
      <li class="list-group-item"><strong>Correo:</strong> ${document.querySelector('[name="correo"]').value.trim()}</li>
      <li class="list-group-item"><strong>Teléfono:</strong> ${document.querySelector('[name="telefono"]').value.trim()}</li>
      <li class="list-group-item"><strong>Dirección:</strong> ${document.querySelector('[name="direccion"]').value.trim()}</li>
    </ul>
    <p class="mt-3 small text-muted">
      Verifica que tus datos sean correctos antes de continuar.
      Tu contraseña no se mostrará por motivos de seguridad.
    </p>
  `;

  // Reutiliza un modal único (creado solo una vez)
  let modal = document.getElementById('confirmModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'confirmModal';
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    modal.innerHTML = `
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-light">
            <h5 class="modal-title">Verifica tus datos</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body" id="resumenBody"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Corregir</button>
            <button type="button" id="confirmSubmit" class="btn btn-primary">Confirmar y Registrar</button>
          </div>
        </div>
      </div>`;
    document.body.appendChild(modal);
  }

  document.getElementById('resumenBody').innerHTML = resumen;
  const confirmModal = new bootstrap.Modal(modal);
  confirmModal.show();

  // Manejar confirmación (solo se añade una vez)
  const confirmBtn = document.getElementById('confirmSubmit');
  confirmBtn.onclick = ()=>{
    confirmModal.hide();
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Registrando…';
    form.submit(); // Envío real
  };
});

</script>

</body>
</html>
