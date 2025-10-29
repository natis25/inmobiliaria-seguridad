<?php
require "vendor/autoload.php";
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #98c3ffff;
        }

        .login-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 300px;
            text-align: center;
        }

        .login-box h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .login-box input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .login-box button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .login-box button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .register-link {
            margin-top: 15px;
            font-size: 14px;
        }

        .register-link a {
            color: #007bff;
        }

        /* body {
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
        }*/

        a {
            color: #ffd500ff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <h2>🔒 Iniciar Sesión</h2>
        <?php
        session_start();
        if (isset($_SESSION['error'])) {
            echo '<p class="error">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="Logica/procesarLogin.php" method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required><br>
            <div class="g-recaptcha" data-sitekey="<?= $_ENV['RECAPTCHA_SITE_KEY'] ?>"></div><br>
            <button type="submit">Iniciar Sesión</button>
        </form>
        <br><br>
        <a href="recover.php">¿Olvidaste tu contraseña?</a>
        <br><br>
        <div class="register-link">
            ¿No tienes cuenta? <a href="registroCliente.php">Regístrate aquí</a>
        </div>
    </div>
</body>

</html>