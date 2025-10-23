<?php
session_start();
require_once '../Logica/sql.php';
require_once '../Logica/helpers.php';

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

// Obtener trabajadores con su rol y cargo
$sql_trabajadores = "
    SELECT t.idTrabajador, t.Nombre, t.Apellido, t.idRol, r.NombreRol, c.NombreCargo
    FROM trabajador t 
    LEFT JOIN rol r ON t.idRol = r.idRol
    LEFT JOIN cargo c ON t.idCargo = c.idCargo
    WHERE t.is_deleted = 0
    ORDER BY t.Nombre, t.Apellido
";
$result_trabajadores = mysqli_query($conn, $sql_trabajadores);
$trabajadores = $result_trabajadores->fetch_all(MYSQLI_ASSOC);

// Obtener todos los permisos agrupados por módulo
$result = $conn->query("
    SELECT Modulo, Accion, idPermiso 
    FROM permiso 
    ORDER BY Modulo, FIELD(Accion, 'ver', 'crear', 'editar', 'eliminar', 'bloquear')
");
$todos_permisos = $result->fetch_all(MYSQLI_ASSOC);

// Organizar permisos por módulo
$permisos_por_modulo = [];
foreach ($todos_permisos as $permiso) {
    $permisos_por_modulo[$permiso['Modulo']][] = $permiso;
}

// Obtener permisos por trabajador (overrides)
$sql_user_permisos = "SELECT tp.idTrabajador, tp.idPermiso FROM trabajador_permiso tp";
$result_user_permisos = mysqli_query($conn, $sql_user_permisos);
$user_permisos = [];
while ($row = mysqli_fetch_assoc($result_user_permisos)) {
    $user_permisos[$row['idTrabajador']][$row['idPermiso']] = true;
}

// Obtener permisos por rol (fallback)
$sql_rol_permisos = "SELECT rp.idRol, rp.idPermiso FROM rol_permiso rp";
$result_rol_permisos = mysqli_query($conn, $sql_rol_permisos);
$rol_permisos = [];
while ($row = mysqli_fetch_assoc($result_rol_permisos)) {
    $rol_permisos[$row['idRol']][$row['idPermiso']] = true;
}

// Procesar actualización de permisos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_permisos'])) {
    $trabajador_id = $_POST['trabajador_id'];
    
    // Limpiar permisos existentes para el trabajador
    $stmt = $conn->prepare("DELETE FROM trabajador_permiso WHERE idTrabajador = ?");
    $stmt->bind_param("i", $trabajador_id);
    $stmt->execute();
    
    // Insertar nuevos permisos seleccionados para el trabajador
    if (isset($_POST['permisos']) && is_array($_POST['permisos'])) {
        $stmt = $conn->prepare("INSERT INTO trabajador_permiso (idTrabajador, idPermiso) VALUES (?, ?)");
        foreach ($_POST['permisos'] as $permiso_id) {
            $stmt->bind_param("ii", $trabajador_id, $permiso_id);
            $stmt->execute();
        }
        $stmt->close();
    }
    
    $_SESSION['mensaje'] = "✅ Permisos actualizados correctamente para el usuario seleccionado";
    $_SESSION['tipo_mensaje'] = "success";
    
    // Recargar la página para ver los cambios
    header("Location: gestionarRolesyPermisos.php?trabajador_id=" . $trabajador_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles y Permisos</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .matrix-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .matrix-header {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .permiso-cell {
            text-align: center;
            vertical-align: middle;
        }
        .module-header {
            background: #e9ecef;
            font-weight: bold;
        }
        .btn-select-all {
            margin-bottom: 10px;
        }
        .module-section {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .permiso-checkbox:disabled {
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="matrix-header">
            <h2>🔐 Matriz de Roles y Permisos</h2>
            <p class="mb-0">Sistema OSI - Gestión completa de permisos por trabajador</p>
            <small class="text-info">Los permisos marcados reflejan los del rol por defecto, con overrides personalizados en azul.</small>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-<?php echo $_SESSION['tipo_mensaje']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['mensaje']; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
        <?php endif; ?>

        <form method="POST" id="formPermisos">
            <div class="form-group">
                <label for="trabajadorSelect"><strong>Seleccionar Trabajador:</strong></label>
                <select class="form-control" id="trabajadorSelect" name="trabajador_id" required onchange="cargarPermisos()">
                    <option value="">-- Selecciona un trabajador --</option>
                    <?php foreach ($trabajadores as $trabajador): ?>
                        <option value="<?php echo $trabajador['idTrabajador']; ?>" 
                                data-rol="<?php echo $trabajador['idRol']; ?>"
                                <?php echo (isset($_GET['trabajador_id']) && $_GET['trabajador_id'] == $trabajador['idTrabajador']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($trabajador['Nombre'] . ' ' . $trabajador['Apellido'] . ' (' . $trabajador['NombreRol'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="matrix-container" id="matrizPermisos" style="display: none;">
                <h4>Permisos para: <span id="nombreTrabajadorSeleccionado"></span></h4>
                
                <!-- Botones para selección masiva -->
                <div class="btn-group btn-select-all" role="group">
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="seleccionarTodo()">✓ Seleccionar Todo</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deseleccionarTodo()">✗ Deseleccionar Todo</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Módulo</th>
                                <th>Ver</th>
                                <th>Crear</th>
                                <th>Editar</th>
                                <th>Eliminar</th>
                                <th>Bloquear</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($permisos_por_modulo as $modulo => $permisos_modulo): ?>
                                <tr class="module-header">
                                    <td><strong><?php echo ucfirst($modulo); ?></strong></td>
                                    <?php 
                                    $acciones = ['ver', 'crear', 'editar', 'eliminar', 'bloquear'];
                                    foreach ($acciones as $accion): 
                                        $permiso_encontrado = null;
                                        foreach ($permisos_modulo as $permiso) {
                                            if ($permiso['Accion'] === $accion) {
                                                $permiso_encontrado = $permiso;
                                                break;
                                            }
                                        }
                                    ?>
                                        <td class="permiso-cell">
                                            <?php if ($permiso_encontrado): ?>
                                                <input type="checkbox" 
                                                       name="permisos[]" 
                                                       value="<?php echo $permiso_encontrado['idPermiso']; ?>" 
                                                       class="permiso-checkbox"
                                                       data-modulo="<?php echo $modulo; ?>"
                                                       data-accion="<?php echo $accion; ?>"
                                                       id="perm_<?php echo $permiso_encontrado['idPermiso']; ?>">
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="seleccionarModulo('<?php echo $modulo; ?>')">
                                            Todo
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <button type="submit" name="actualizar_permisos" class="btn btn-success btn-lg btn-block">
                    💾 Guardar Cambios de Permisos
                </button>
            </div>
        </form>

        <button class="btn btn-secondary mt-3" onclick="location.href='panelControlOSI.php'">
            ← Volver al Panel OSI
        </button>
    </div>

    <script>
        // Parsear datos de PHP a JS
        const userPermisos = JSON.parse('<?php echo json_encode($user_permisos); ?>');
        const rolPermisos = JSON.parse('<?php echo json_encode($rol_permisos); ?>');

        function cargarPermisos() {
            const trabajadorSelect = document.getElementById('trabajadorSelect');
            const matrizDiv = document.getElementById('matrizPermisos');
            const nombreTrabajadorSpan = document.getElementById('nombreTrabajadorSeleccionado');
            
            if (trabajadorSelect.value) {
                matrizDiv.style.display = 'block';
                nombreTrabajadorSpan.textContent = trabajadorSelect.options[trabajadorSelect.selectedIndex].text;
                
                // Limpiar checkboxes
                document.querySelectorAll('.permiso-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.style.backgroundColor = ''; // Resetear estilo
                });
                
                // Obtener el trabajador_id y rol_id
                const trabajadorId = trabajadorSelect.value;
                const rolId = trabajadorSelect.selectedOptions[0].dataset.rol;
                
                // Marcar checkboxes según permisos (user override o fallback a rol)
                document.querySelectorAll('.permiso-checkbox').forEach(checkbox => {
                    const permisoId = checkbox.value;
                    if (userPermisos[trabajadorId] && userPermisos[trabajadorId][permisoId]) {
                        checkbox.checked = true; // Override por usuario
                        checkbox.style.backgroundColor = '#add8e6'; // Azul claro para overrides
                    } else if (rolPermisos[rolId] && rolPermisos[rolId][permisoId]) {
                        checkbox.checked = true; // Fallback a rol
                        checkbox.style.backgroundColor = '#e0e0e0'; // Gris para permisos por rol
                    }
                });
            } else {
                matrizDiv.style.display = 'none';
            }
        }

        function seleccionarTodo() {
            document.querySelectorAll('.permiso-checkbox').forEach(checkbox => {
                checkbox.checked = true;
                checkbox.style.backgroundColor = '#add8e6'; // Marcar como override
            });
        }

        function deseleccionarTodo() {
            document.querySelectorAll('.permiso-checkbox').forEach(checkbox => {
                checkbox.checked = false;
                checkbox.style.backgroundColor = ''; // Resetear estilo
            });
        }

        function seleccionarModulo(modulo) {
            document.querySelectorAll('.permiso-checkbox').forEach(checkbox => {
                if (checkbox.dataset.modulo === modulo) {
                    checkbox.checked = true;
                    checkbox.style.backgroundColor = '#add8e6'; // Marcar como override
                }
            });
        }

        // Cargar permisos al iniciar si hay trabajador en la URL
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const trabajadorId = urlParams.get('trabajador_id');
            
            if (trabajadorId) {
                document.getElementById('trabajadorSelect').value = trabajadorId;
                cargarPermisos();
            }
        });
    </script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>