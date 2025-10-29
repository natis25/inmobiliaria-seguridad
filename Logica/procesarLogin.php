<?php
include('sql.php');
include('CAPTCHA.php');

session_start();

$conn = Conectarse();

if (!$conn) {
    $_SESSION['error'] = "Error interno: Fallo al Conectar con la Base de Datos.";
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $conn->close(); // Cerrar conexión antes de salir
    header("Location: ../login.php");
    exit();
}

$usuario = trim($_POST["usuario"]);
$password = trim($_POST["password"]);

if (empty($usuario) || empty($password)) {
    $_SESSION['error'] = "Debes ingresar tu Usuario y Contraseña.";
    $conn->close();
    header("Location: ../login.php");
    exit();
}

$user_found = null;
$user_type = null;

if (empty($captcha_result["success"]) || !$captcha_result["success"]) {
    echo "<script>alert('⚠️ Verificación CAPTCHA fallida');</script>";
    var_dump($captcha_result); // para depuración
} else if ($captcha_result && isset($captcha_result['success']) && $captcha_result['success'] === true) {
    echo "¡CAPTCHA válido! <br>";

    // Buscar en trabajador con JOIN a password_history (hash más reciente)
    $sql_trabajador = "
        SELECT t.idTrabajador AS id, t.Nombre, t.Apellido, t.Usuario, ph.PasswordHash, t.idRol, t.EstadoCuenta 
        FROM trabajador t
        LEFT JOIN password_history ph ON ph.user_type = 'trabajador' AND ph.user_id = t.idTrabajador
        WHERE t.Usuario = ? AND t.is_deleted = 0
        ORDER BY ph.FechaCambio DESC LIMIT 1
    ";
    $stmt_trabajador = $conn->prepare($sql_trabajador);

    if ($stmt_trabajador) {
        $stmt_trabajador->bind_param("s", $usuario);
        $stmt_trabajador->execute();
        $result_trabajador = $stmt_trabajador->get_result();

        if ($result_trabajador->num_rows > 0) {
            $user_found = $result_trabajador->fetch_assoc();
            $user_type = 'trabajador';
        }
        $stmt_trabajador->close();
    } else {
        // Depuración: Si prepare falla, loguea el error
        error_log("Error en prepare trabajador: " . $conn->error);
        $_SESSION['error'] = "Error interno en la consulta.";
        $conn->close();
        header("Location: ../login.php");
        exit();
    }

    // Buscar en cliente con JOIN a password_history (hash más reciente)
    if (!$user_found) {
        $sql_cliente = "
            SELECT c.idCliente AS id, c.Nombre, c.Apellido, c.Usuario, ph.PasswordHash, c.EstadoCuenta 
            FROM cliente c
            LEFT JOIN password_history ph ON ph.user_type = 'cliente' AND ph.user_id = c.idCliente
            WHERE c.Usuario = ? AND c.is_deleted = 0
            ORDER BY ph.FechaCambio DESC LIMIT 1
        ";
        $stmt_cliente = $conn->prepare($sql_cliente);

        if ($stmt_cliente) {
            $stmt_cliente->bind_param("s", $usuario);
            $stmt_cliente->execute();
            $result_cliente = $stmt_cliente->get_result();

            if ($result_cliente->num_rows > 0) {
                $user_found = $result_cliente->fetch_assoc();
                $user_type = 'cliente';
            }
            $stmt_cliente->close();
        } else {
            // Depuración: Si prepare falla, loguea el error
            error_log("Error en prepare cliente: " . $conn->error);
            $_SESSION['error'] = "Error interno en la consulta.";
            $conn->close();
            header("Location: ../login.php");
            exit();
        }
    }

    // Bloquear cuenta (el resto del código permanece igual)
    if ($user_found) {
        if ($user_found['EstadoCuenta'] === 'Bloqueado') {
            $_SESSION['error'] = "Tu cuenta está bloqueada. Contacta al administrador.";
            $conn->close();
            header("Location: ../login.php");
            exit();
        }

        if (password_verify($password, $user_found['PasswordHash'])) {
            // Iniciar sesion
            $_SESSION['user_id'] = $user_found['id'];
            $_SESSION['username'] = $user_found['Usuario'];
            $_SESSION['nombre_completo'] = $user_found['Nombre'] . ' ' . $user_found['Apellido'];
            $_SESSION['user_type'] = $user_type;

            // Desbloquear usuario
            $update_login = "UPDATE {$user_type} SET last_login_at = NOW(), IntentosFallidos = 0 WHERE id" . ucfirst($user_type) . " = ?";
            if ($stmt_update = $conn->prepare($update_login)) {
                $stmt_update->bind_param("i", $user_found['id']);
                $stmt_update->execute();
                $stmt_update->close();
            }

            $conn->close();
            header("Location: ../OSI/panelControl.php");
            exit();
        } else {
            // Aumentar intentos fallidos
            $update_intentos = "UPDATE {$user_type} SET IntentosFallidos = IntentosFallidos + 1 WHERE id" . ucfirst($user_type) . " = ?";
            if ($stmt_update = $conn->prepare($update_intentos)) {
                $stmt_update->bind_param("i", $user_found['id']);
                $stmt_update->execute();
                $stmt_update->close();
            }

            $_SESSION['error'] = "❌ Datos ingresados incorrectos. Intente nuevamente por favor.";
            $conn->close();
            header("Location: ../login.php");
            exit();
        }
    } else {
        // Usuario no encontrado
        $_SESSION['error'] = "❌ El Usuario no esta registrado. Registrelo o intente con otras credenciales por favor.";
        $conn->close();
        header("Location: ../login.php");
        exit();
    }
}
?>