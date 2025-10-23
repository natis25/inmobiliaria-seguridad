<?php
session_start();
require_once __DIR__ . '/../Logica/sql.php';
require_once __DIR__ . '/../Logica/helpers.php';

// Verificar si el usuario está logueado y es OSI
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'trabajador') {
    header('Location: ../login.php');
    exit();
}

$conn = Conectarse();
if (!$conn) {
    die("Error de conexión");
}

// Verificar si el usuario tiene rol OSI
if (!esUsuarioOSI($conn, $_SESSION['user_id'])) {
    header('Location: ../panelControl.php');
    exit();
}

// Obtener lista de trabajadores con JOIN para Posición y Rol
$sql = "
    SELECT t.idTrabajador, t.Nombre, t.Apellido, t.Usuario, t.Telefono, t.Correo, t.EstadoCuenta,
           c.NombreCargo AS Posicion, r.NombreRol AS Rol
    FROM trabajador t 
    LEFT JOIN cargo c ON t.idCargo = c.idCargo 
    LEFT JOIN rol r ON t.idRol = r.idRol 
    WHERE t.is_deleted = 0
";
$result = mysqli_query($conn, $sql);
$trabajadores = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Trabajadores</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 120px; /* Espacio para el header */
        }
        .table-container {
            margin: 20px;
        }
        .new-property-btn {
            margin-bottom: 20px;
        }
        .header-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .btn-osi {
            background-color: #fd7e14;
            border-color: #fd7e14;
            color: white;
        }
        .btn-osi:hover {
            background-color: #e96b00;
            border-color: #e96b00;
            color: white;
        }
    </style>
</head>

<body>

    <?php include('../header.php'); ?>

    <div class="table-container">
        <div class="header-info">
            <h3>🛡️ Gestión de Empleados - Panel OSI</h3>
            <p class="mb-0">Acceso exclusivo para administración de trabajadores</p>
        </div>
        
        <!-- Mostrar mensajes flash -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['flash_success']; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['flash_error']; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center new-property-btn">
            <div>
                <a href="registrarEmpleado.php" class="btn btn-success">➕ Agregar Trabajador</a>
                <a href="gestionarRolesyPermisos.php" class="btn btn-osi">🔐 Gestionar Roles</a>
            </div>
            <a href="panelControlOSI.php" class="btn btn-primary">📊 Volver al Panel</a>
        </div>
        
        <h3 class="text-center mb-4">Lista de Trabajadores</h3>
        
        <?php if (empty($trabajadores)): ?>
            <div class="alert alert-info text-center">
                No hay trabajadores registrados en el sistema.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Usuario</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Posición</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trabajadores as $trabajador): 
                            $estado_badge = ($trabajador['EstadoCuenta'] === 'Activo') ? 
                                '<span class="badge badge-success">Activo</span>' : 
                                '<span class="badge badge-danger">Bloqueado</span>';
                            $id_formatted = 'EMP' . str_pad($trabajador['idTrabajador'], 3, '0', STR_PAD_LEFT);
                        ?>
                            <tr>
                                <td><strong><?php echo $id_formatted; ?></strong></td>
                                <td><?php echo htmlspecialchars($trabajador['Nombre'] . ' ' . $trabajador['Apellido']); ?></td>
                                <td><code><?php echo htmlspecialchars($trabajador['Usuario']); ?></code></td>
                                <td><?php echo htmlspecialchars($trabajador['Telefono']); ?></td>
                                <td><?php echo htmlspecialchars($trabajador['Correo']); ?></td>
                                <td><?php echo htmlspecialchars($trabajador['Posicion'] ?? 'No asignado'); ?></td>
                                <td><?php echo htmlspecialchars($trabajador['Rol'] ?? 'No asignado'); ?></td>
                                <td><?php echo $estado_badge; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href='modificarEmpleado.php?id=<?php echo $trabajador['idTrabajador']; ?>' 
                                           class='btn btn-warning' title='Modificar'>
                                            ✏️ Editar
                                        </a>
                                        <?php if ($trabajador['EstadoCuenta'] === 'Activo'): ?>
                                            <button class='btn btn-danger' 
                                                    onclick='bloquearTrabajador(<?php echo $trabajador['idTrabajador']; ?>)'
                                                    title='Bloquear'>
                                                🔒 Bloquear
                                            </button>
                                        <?php else: ?>
                                            <button class='btn btn-success' 
                                                    onclick='activarTrabajador(<?php echo $trabajador['idTrabajador']; ?>)'
                                                    title='Activar'>
                                                🔓 Activar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para confirmar acciones -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmar Acción</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- El contenido se llena con JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmAction">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        let trabajadorId = null;
        let accionActual = '';
        
        function bloquearTrabajador(id) {
            trabajadorId = id;
            accionActual = 'bloquear';
            document.getElementById('modalBody').innerHTML = 
                '¿Estás seguro de que deseas <strong>bloquear</strong> este trabajador?<br><br>' +
                'El trabajador no podrá iniciar sesión hasta que sea activado nuevamente.';
            document.getElementById('confirmAction').className = 'btn btn-danger';
            document.getElementById('confirmAction').textContent = 'Sí, Bloquear';
            $('#confirmModal').modal('show');
        }
        
        function activarTrabajador(id) {
            trabajadorId = id;
            accionActual = 'activar';
            document.getElementById('modalBody').innerHTML = 
                '¿Estás seguro de que deseas <strong>activar</strong> este trabajador?<br><br>' +
                'El trabajador podrá iniciar sesión nuevamente.';
            document.getElementById('confirmAction').className = 'btn btn-success';
            document.getElementById('confirmAction').textContent = 'Sí, Activar';
            $('#confirmModal').modal('show');
        }
        
        document.getElementById('confirmAction').addEventListener('click', function() {
            if (trabajadorId && accionActual) {
                window.location.href = `../Logica/${accionActual}Trabajador.php?id=${trabajadorId}`;
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>