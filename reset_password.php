<!-- Este archivo recibe el token del correo y permite al usuario definir una nueva contraseña. -->

<?php
require 'connection.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $conn->prepare("SELECT idCliente FROM cliente WHERE token=? AND token_expira > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '
        <form method="POST" action="reset_password.php" style="text-align:center;margin-top:10%;">
            <input type="hidden" name="token" value="' . htmlspecialchars($token) . '">
            <h2>Restablecer contraseña</h2>
            <input type="password" name="password" placeholder="Nueva contraseña" required><br><br>
            <button type="submit">Actualizar</button>
        </form>';
    } else {
        echo "<script>alert('⚠️ Enlace inválido o expirado.'); window.location='recover.php';</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $update = $conn->prepare("UPDATE cliente SET Contraseña=?, token=NULL, token_expira=NULL WHERE token=?");
    $update->bind_param("ss", $password, $token);
    $update->execute();

    echo "<script>alert('🔐 Contraseña actualizada correctamente.'); window.location='login.php';</script>";
}
?>
