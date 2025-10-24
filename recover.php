<!-- Este archivo genera el token, lo guarda en la tabla cliente, -->
<!-- y envía un correo con el enlace de restablecimiento. -->

<?php
require 'vendor/autoload.php';
require 'connection.php';

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

        $update = $conn->prepare("UPDATE cliente SET token=?, token_expira=? WHERE Correo=?");
        $update->bind_param("sss", $token, $expira, $email);
        $update->execute();

        // Enlace para restablecer
        $link = "http://localhost/inmobiliaria-seguridad/reset_password.php?token=$token";

        // Configurar y enviar correo
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = 'tls';
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
    background: linear-gradient(135deg, #7209b7, #560bad);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    font-family: Arial, sans-serif;
}
form {
    background: rgba(255,255,255,0.1);
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
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
</style>
</head>
<body>
<form action="recover.php" method="POST">
    <h2>🔑 Recuperar Contraseña</h2>
    <input type="email" name="correo" placeholder="Tu correo registrado" required>
    <button type="submit">Enviar enlace</button><br><br>
    <a href="login.php" style="color: #ffd60a;">Volver a Inicio de Sesión</a>
</form>
</body>
</html>
