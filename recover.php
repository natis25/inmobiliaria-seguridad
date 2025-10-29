<!-- Este archivo genera el token, lo guarda en la tabla cliente, -->
<!-- y envía un correo con el enlace de restablecimiento. -->

<?php
require 'vendor/autoload.php';
include 'logica/sql.php';

$conn = Conectarse();

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['correo']);

    // Buscar si el correo existe
    $stmt = $conn->prepare("SELECT idCliente FROM cliente WHERE Correo=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generar token
        $token = bin2hex(random_bytes(50));
        $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $conn->prepare("UPDATE cliente SET rcvPass_token=?, rcvPass_token_expires=? WHERE Correo=?");
        $update->bind_param("sss", $token, $expira, $email);
        $update->execute();

        // Enlace para restablecer
        $link = "http://localhost/logica/resetPassword.php?token=$token";
        // * IF THE PROJECT IS RUNNING ON PHP SERVER: localhost:3000
        // $link = "http://localhost:3000/logica/resetPassword.php?token=$token";

        // Configurar y enviar correo
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            // ! DESACTIVAR ANTIVIRUS EN DESARROLLO, Ó PONER "*verify" EN FALSE (NO SEGURO):
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => true
                )
            );
            $mail->SMTPDebug = 0;
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['MAIL_PORT'];
            $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "🔐 Recuperación de contraseña - DRoca Inmobiliaria";
            $mail->Body = "
                <h2>Solicitud de recuperación de contraseña</h2>
                <p>Haga clic en el siguiente enlace para restablecer su contraseña:</p>
                <p><a href='$link'>$link</a></p>
                <p>Este enlace expirará en 1 hora.</p>
            ";

            $mail->send();
            echo "<script>alert('✅ Se envió un enlace de recuperación a tu correo.'); window.location='login.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('❌ Error al enviar correo: {$mail->ErrorInfo}');</script>";
        }
    } else {
        echo "<script>alert('⚠️ El correo no está registrado.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <style>
        body {
            background: linear-gradient(135deg, #40baf3ff, #560bad);
            color: black;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
        }

        form {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.6);
            text-align: center;
        }

        input {
            width: 90%;
            padding: 10px;
            margin: 1em auto 2em;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

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
    <form action="recover.php" method="POST">
        <h2>🔑 Recuperar Contraseña</h2>
        <input type="email" name="correo" placeholder="Tu correo registrado" required>
        <button type="submit">Enviar enlace</button><br><br>
        <a href="login.php">Volver a Inicio de Sesión</a>
    </form>
</body>

</html>