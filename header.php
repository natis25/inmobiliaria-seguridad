<?php
// header.php - Header universal para todo el sistema
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Detectar si estamos en el área OSI (carpeta OSI)
$is_osi_area = (strpos($_SERVER['PHP_SELF'], '/OSI/') !== false);

// Determinar la ruta correcta para las imágenes
$logo_path = $is_osi_area ? '../images/Logo.png' : 'images/Logo.png';
$home_path = $is_osi_area ? '../index.php' : 'index.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .navbar {
            height: 100px;
            background-color: <?php echo $is_osi_area ? '#667eea' : 'wheat'; ?>;
        }

        .navbar .navbar-brand {
            display: flex;
            align-items: center;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            height: 100%;
        }

        .navbar .navbar-brand img {
            max-height: 100%;
            height: auto;
            width: auto;
        }

        .navbar-nav .nav-link {
            font-size: 20px;
            padding: 20px;
            margin-right: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="<?php echo $home_path; ?>">
            <img src="<?php echo $logo_path; ?>" class="d-inline-block align-top" alt="Logo">
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $home_path; ?>">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $is_osi_area ? '../inmuebles.php' : 'inmuebles.php'; ?>">Inmuebles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $is_osi_area ? '../citas.php' : 'citas.php'; ?>">Cancelar Cita</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $is_osi_area ? '../contacto.php' : 'contacto.php'; ?>">Contacto</a>
                </li>
            </ul>
            <?php if (!$is_osi_area): ?>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">¿Eres empleado?</a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Incluir Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>