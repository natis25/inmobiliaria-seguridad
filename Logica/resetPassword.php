<!-- Este archivo recibe el token del correo y permite al usuario definir una nueva contraseña. -->

<?php
include('sql.php');

$conn = Conectarse();

if (!$conn) {
    echo "Error interno: Fallo al Conectar con la Base de Datos.";
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $conn->prepare("SELECT idCliente FROM cliente WHERE rcvPass_token=? AND rcvPass_token_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '
        <!DOCTYPE html>
        <html lang="es">
        <head>
        <meta charset="UTF-8">
        <title>Restablecer Contraseña</title>
        <style>
        body {
            margin: 0;
            padding: 0;
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
            margin:0;
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
        </style>
        </head>
        <body>
        <form action="resetPassword.php" method="POST">
            <input type="hidden" name="token" value="' . htmlspecialchars($token) . '">
            <h2>Restablecer contraseña</h2>
            <input type="password" name="password" placeholder="Nueva contraseña" required><br><br>
            <button type="submit">Actualizar</button>
        </form>
        </body>
        </html>';
    } else {
        echo "<script>alert('⚠️ Enlace inválido o expirado.'); window.location='../recover.php';</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $token = $_POST['token'];
        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Obtener el idCliente a partir del token
        $getCliente = $conn->prepare("
            SELECT idCliente 
            FROM cliente 
            WHERE rcvPass_token = ?
        ");
        $getCliente->bind_param("s", $token);
        $getCliente->execute();
        $result = $getCliente->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Token inválido o expirado.");
        }

        $cliente = $result->fetch_assoc();
        $idCliente = $cliente['idCliente'];

        // Insertar nueva contraseña en el historial
        $insertHistory = $conn->prepare("
            INSERT INTO password_history (user_type, user_id, PasswordHash)
            VALUES ('cliente', ?, ?)
        ");
        $insertHistory->bind_param("is", $idCliente, $hashedPassword);
        $insertHistory->execute();

        // Anular el token de recuperación en la tabla cliente
        $clearToken = $conn->prepare("
            UPDATE cliente 
            SET rcvPass_token = NULL, 
                rcvPass_token_expires = NULL 
            WHERE idCliente = ?
        ");
        $clearToken->bind_param("i", $idCliente);
        $clearToken->execute();

        $conn->commit();

        echo "<script>alert('🔐 Contraseña actualizada correctamente.'); window.location='../login.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}
?>