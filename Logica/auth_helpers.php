
<?php
// Helpers comunes para registro de identidades

function cap($s,$n){ return mb_substr(trim((string)$s),0,$n,'UTF-8'); }

function strip_accents($s){
  if (function_exists('transliterator_transliterate')){
    $t = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $s);
    return str_replace(['ñ','Ñ'],'n', $t);
  }
  if (function_exists('iconv')){
    $t = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);
    return str_replace(['ñ','Ñ'],'n', strtolower($t));
  }
  $map=['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','ñ'=>'n','Ñ'=>'n'];
  return strtr(mb_strtolower($s,'UTF-8'), $map);
}
function norm_words($s){
  $s = strip_accents($s);
  $s = preg_replace('/[^a-z0-9 ]/',' ', $s);
  return trim(preg_replace('/\s+/',' ', $s));
}

function first_word($s){ $w = explode(' ', norm_words($s)); return preg_replace('/[^a-z]/','', $w[0] ?? ''); }
function last_word($s){ $w = explode(' ', norm_words($s)); $x = end($w) ?: ''; return preg_replace('/[^a-z]/','', $x); }
function initial($s){ $x = last_word($s); return $x !== '' ? $x[0] : ''; }

// Genera cadena candidata de usuario según reglas
function username_candidates($nombre,$ap1,$ap2){
  $n  = first_word($nombre);
  $p  = last_word($ap1);
  $s  = last_word($ap2);
  $ni = $n !== '' ? $n[0] : '';
  $si = initial($ap2);

  $cands = [];
  if ($n && $p)           $cands[] = "{$n}.{$p}";              // juan.perez
  if ($n && $s)           $cands[] = "{$n}.{$s}";              // juan.gomez
  if ($n && $p && $si)    $cands[] = "{$n}.{$p}{$si}";         // juan.perezg
  if ($ni && $p && $si)   $cands[] = "{$ni}.{$p}{$si}";        // j.perezg
  if ($n)                 $cands[] = $n;                       // juan (fallback)
  return array_values(array_unique($cands));
}

// Busca el primer username disponible de la lista; si ninguno, agrega sufijo -i
function username_unico_from_candidates($db, array $cands, $table){
  // prueba candidatos
  foreach ($cands as $u) {
    $stmt = $db->prepare("SELECT 1 FROM {$table} WHERE Usuario=? LIMIT 1");
    $stmt->bind_param('s',$u); $stmt->execute(); $stmt->store_result();
    $ok = ($stmt->num_rows===0);
    $stmt->close();
    if ($ok) return $u;
  }
  // si todos ocupados, usa el primero con sufijo incremental
  $base = $cands[0] ?? 'usuario';
  return username_unico($db, $base, $table);
}

// API pública para el proceso
function generar_usuario_estandarizado($db, $nombre, $ap1, $ap2, $table){
  $list = username_candidates($nombre,$ap1,$ap2);
  // limpieza final por si acaso
  $list = array_map(function($u){
    $u = strip_accents($u);
    $u = preg_replace(['/[^a-z0-9\.]/','/\.+/'],['','.' ],$u);
    return trim($u,'.');
  }, $list);
  // garantía de formato
  $list = array_values(array_filter($list, fn($u)=>preg_match('/^[a-z]+(\.[a-z0-9]+)*$/',$u)));
  if (!$list) $list = ['usuario'];
  return username_unico_from_candidates($db, $list, $table);
}

// Código estándar del cliente: CLI-YYYYMMDD-###### (id acolchado)
function build_codigo_cliente($id, ?DateTime $ts=null){
  $ts = $ts ?: new DateTime('now');
  $fecha = $ts->format('Ymd');
  $seq = str_pad((string)$id, 6, '0', STR_PAD_LEFT);
  return "CLI-{$fecha}-{$seq}";
}

// Código estándar del empleado: EMP-YYYYMMDD-######
function build_codigo_empleado($id, ?DateTime $ts=null){
  $ts = $ts ?: new DateTime('now');
  $fecha = $ts->format('Ymd');
  $seq = str_pad((string)$id, 6, '0', STR_PAD_LEFT);
  return "EMP-{$fecha}-{$seq}";
}

// Genera contraseña temporal que cumple reglas (12+; may/min/num/símbolo)
// basada en frase corta para memorización temporal
function generar_password_temporal(): string {
  // lista mínima de palabras “memorizables”; puedes cambiar por vocabulario interno
  $palabras = ['Valle','Roca','Andes','Sol','Luna','Puma','Quilla','Condor','Killa','Inti'];
  $p = $palabras[random_int(0, count($palabras)-1)];
  $num = (string)random_int(100, 999);         // 3 dígitos
  $sym = str_split('@#$%&*?')[random_int(0,6)]; // 1 símbolo
  // Ensamble: Palabra con mayúscula + minúsculas + dígitos + símbolo + sufijo de letras minúsculas
  $suf = substr(bin2hex(random_bytes(3)), 0, 3); // 3 min chars
  $pwd = $p . strtolower($p[0]) . $num . $sym . $suf; 
  // Garantiza reglas:
  // - mayúscula: $p tiene mayúscula inicial
  // - minúscula: strtolower($p[0]) y $suf
  // - número: $num
  // - símbolo: $sym
  // - 12+: típico queda 1ªPalabra(>=4) + 1 + 3 + 1 + 3 >= 12
  if (strlen($pwd) < 12) { // por si acaso, relleno
    $pwd .= substr(bin2hex(random_bytes(2)),0,2);
  }
  return $pwd;
}

// Busca un username disponible agregando sufijo -i si hace falta (sin tocar esquema)
function username_unico($db,$user,$table){
  $base=$user; $i=0;
  $stmt = $db->prepare("SELECT 1 FROM {$table} WHERE Usuario=? LIMIT 1");
  while(true){
    $try = $i ? ($base.'-'.$i) : $base;
    $stmt->bind_param('s',$try); $stmt->execute(); $stmt->store_result();
    if ($stmt->num_rows===0){ $stmt->close(); return $try; }
    $stmt->free_result();
    $i++;
  }
}

// Política de contraseñas
function validar_password($pwd){
  $r=['len'=>strlen($pwd)>=12,'may'=>preg_match('/[A-Z]/',$pwd),'min'=>preg_match('/[a-z]/',$pwd),'num'=>preg_match('/\d/',$pwd),'sym'=>preg_match('/[^A-Za-z0-9]/',$pwd)];
  return [array_product(array_map(fn($v)=>$v?1:0,$r))===1,$r];
}

// ¿Correo existe? (consulta simple, sin índices únicos)
function correo_existe($db,$correo,$table){
  $s=$db->prepare("SELECT 1 FROM {$table} WHERE Correo=? LIMIT 1");
  $s->bind_param('s',$correo); $s->execute(); $s->store_result();
  $x=$s->num_rows>0; $s->close(); return $x;
}

// Correo corporativo empleado
function generar_correo_empleado($usuario, $dominio='droca.local'){
  return $usuario.'@'.$dominio;
}

// Rate limit por sesión (clave reutilizable)
function hit_rate_limit($key, $seconds=10){
  $now = time();
  if (!empty($_SESSION[$key]) && $now - $_SESSION[$key] < $seconds) return true;
  $_SESSION[$key] = $now; return false;
}

/**
 * Bloqueo lógico con GET_LOCK para evitar colisiones de Usuario/Correo
 * No requiere cambios de esquema.
 * Devuelve true si tomó el lock, false si no.
 */
function get_named_lock($db, $name, $timeout=5){
  $sql="SELECT GET_LOCK(?, ?)";
  $s=$db->prepare($sql); $s->bind_param('si',$name,$timeout); $s->execute(); $s->bind_result($ok);
  $s->fetch(); $s->close(); return (int)$ok===1;
}
function release_named_lock($db, $name){
  $sql="SELECT RELEASE_LOCK(?)";
  $s=$db->prepare($sql); $s->bind_param('s',$name); $s->execute(); $s->close();
}