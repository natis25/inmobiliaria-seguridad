<?php
require "vendor/autoload.php";
require "connection.php";
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $usuario = trim($_POST["usuario"]);
    $password = trim($_POST["password"]);
    $captcha = $_POST["g-recaptcha-response"];

    // Verificar CAPTCHA
    $secretKey = $_ENV["RECAPTCHA_SECRET_KEY"];
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$captcha}");
    $responseKeys = json_decode($response, true);

    if (empty($responseKeys["success"]) || !$responseKeys["success"]) {
        echo "<script>alert('⚠️ Verificación CAPTCHA fallida');</script>";
    } else {
        // Buscar usuario en la tabla cliente
        $stmt = $conn->prepare("SELECT idCliente, Usuario, Contraseña, Correo FROM cliente WHERE Usuario=?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Si las contraseñas no están encriptadas (por ejemplo '12345678jp'), usa comparación directa
            if ($password === $user['Contraseña'] || password_verify($password, $user['Contraseña'])) {
                $_SESSION['usuario'] = $user['Usuario'];
                $_SESSION['correo'] = $user['Correo'];
                header("Location: index.php"); // página principal tras login
                exit();
            } else {
                echo "<script>alert('❌ Contraseña incorrecta');</script>";
            }
        } else {
            echo "<script>alert('❌ Usuario no encontrado');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #3a0ca3, #7209b7);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
        }
        .login-box {
            background: rgba(255,255,255,0.1);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            width: 320px;
            text-align: center;
        }
        input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
        }
        button {
            background-color: #4361ee;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
        }
        .register-link {
            margin-top: 15px;
            font-size: 14px;
        }
        a {
            color: #ffd60a;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔒 Iniciar Sesión</h2>
        <form action="login.php" method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required><br>
            <input type="password" name="password" placeholder="Contraseña" required><br>
            <div class="g-recaptcha" data-sitekey="<?= $_ENV['RECAPTCHA_SITE_KEY'] ?>"></div><br>
            <button type="submit">Iniciar Sesión</button>
        </form>
        <br>
        <a href="recuperar.php">¿Olvidaste tu contraseña?</a>
        <br><br>
        <div class="register-link">
            ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
        </div>
    </div>
</body>
</html>