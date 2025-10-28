<?php
function Conectarse()
{
    if (!($link = mysqli_connect("localhost", "root"))) {
        return 0;
    }
    if (!mysqli_select_db($link, "droca")) {
        return 0;
    }
    return $link;
}

function obtenerZonas()
{
    $conexion = Conectarse();
    if (!$conexion) {
        return [];
    }

    $consulta = "SELECT * FROM zonas;";
    $resultado = mysqli_query($conexion, $consulta);

    if ($resultado) {
        $zonas = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    } else {
        $zonas = [];
    }

    mysqli_close($conexion);
    return $zonas;
}

function obtenerTiposVivienda()
{
    $conexion = Conectarse();
    if (!$conexion) {
        return [];
    }

    $consulta = "SELECT * FROM tipovivienda;";
    $resultado = mysqli_query($conexion, $consulta);

    if ($resultado) {
        $tiposVivienda = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    } else {
        $tiposVivienda = [];
    }

    mysqli_close($conexion);
    return $tiposVivienda;
}

function obtenerTiposOferta()
{
    $conexion = Conectarse();
    if (!$conexion) {
        return [];
    }

    $consulta = "SELECT * FROM tipooferta;";
    $resultado = mysqli_query($conexion, $consulta);

    if ($resultado) {
        $tiposOferta = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    } else {
        $tiposOferta = [];
    }

    mysqli_close($conexion);
    return $tiposOferta;
}

function insertarVivienda($direccion, $montoPedido, $zona, $tipoVivienda, $tipoOferta)
{
    $conexion = Conectarse();
    if (!$conexion) {
        die("Error de conexión a la base de datos");
    }

    $sql = "INSERT INTO Vivienda (
                Direccion, MontoPedido, Vendido, Zonas_idZona, TipoVivienda_idTipoV, TipoOferta_idTipoO
            ) 
            VALUES (?, ?, FALSE, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param("siiii", $direccion, $montoPedido, $zona, $tipoVivienda, $tipoOferta);

    if ($stmt->execute()) {
        echo "Registro insertado correctamente";
    } else {
        echo "Error al insertar el registro: " . $stmt->error;
    }

    $stmt->close();
    mysqli_close($conexion);
}

function insertarTrabajador($nombre, $telefono, $correo)
{
    $conexion = Conectarse();
    if (!$conexion) {
        die("Error de conexión a la base de datos");
    }

    $sql = "INSERT INTO Trabajador (
                nombre, telefono, correo
            ) 
            VALUES (?, ?, ?)";

    $stmt = $conexion->prepare($sql);

    // Cambia la cadena de tipos a 'sss' si todos son strings
    $stmt->bind_param('sss', $nombre, $telefono, $correo);

    if ($stmt->execute()) {
        echo "Registro insertado correctamente";
    } else {
        echo "Error al insertar el registro: " . $stmt->error;
    }

    $stmt->close();
    mysqli_close($conexion);
}


function obtenerViviendasDisponibles() {
    $conexion = Conectarse();
    if (!$conexion) {
        return [];
    }

    $consulta = "
        SELECT 
            V.idVivienda,
            V.Direccion,
            V.MontoPedido,
            V.Vendido,
            Z.Zona,
            TV.Vivienda AS TipoVivienda,
            TOF.Oferta AS TipoOferta
        FROM 
            Vivienda V
        JOIN 
            Zonas Z ON V.Zonas_idZona = Z.idZona
        JOIN 
            TipoVivienda TV ON V.TipoVivienda_idTipoV = TV.idTipoV
        JOIN 
            TipoOferta TOF ON V.TipoOferta_idTipoO = TOF.idTipoO
        WHERE 
            V.Vendido = FALSE;
    ";

    $resultado = mysqli_query($conexion, $consulta);

    if ($resultado) {
        $viviendas = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    } else {
        $viviendas = [];
    }

    mysqli_close($conexion);
    return $viviendas;
}

function obtenerTabajadores() {
    $conexion = Conectarse();
    if (!$conexion) {
        return [];
    }

    $consulta = "SELECT * FROM trabajador;";
    $resultado = mysqli_query($conexion, $consulta);


    if ($resultado) {
        $trabajadores = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    } else {
        $trabajadores = [];
    }

    mysqli_close($conexion);
    return $trabajadores;
}

function insertarCliente($nombre, $telefono, $correo) {
    $conexion = Conectarse();
    if (!$conexion) {
        die("Error de conexión a la base de datos");
    }

    $sql = "INSERT INTO Cliente (Nombre, Telefono, Correo) VALUES (?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sss", $nombre, $telefono, $correo);

    if ($stmt->execute()) {
        $idCliente = $stmt->insert_id;
        $stmt->close();
        mysqli_close($conexion);
        return $idCliente;
    } else {
        echo "Error al insertar el cliente: " . $stmt->error;
        $stmt->close();
        mysqli_close($conexion);
        return null;
    }
}

function insertarCita($fechaVisita, $horaInicio, $horaFin, $idVivienda, $idCliente, $estado) {
    $conexion = Conectarse();
    if (!$conexion) {
        die("Error de conexión a la base de datos");
    }

    $sql = "INSERT INTO Cita (
                FechaVisita, 
                HoraInicio, 
                HoraFin, 
                esTrato, 
                Vivienda_idVivienda, 
                Cliente_idCliente, 
                Estado_idEstado,
                Trabajador_idTrabajador,
                MontoOfrecido,
                FechaTrato
            ) 
            VALUES (?, ?, ?, FALSE, ?, ?, ?, 1, -1, NULL)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssiii", $fechaVisita, $horaInicio, $horaFin, $idVivienda, $idCliente, $estado);

    if ($stmt->execute()) {
        echo "Cita registrada correctamente.";
    } else {
        echo "Error al registrar la cita: " . $stmt->error;
    }

    $stmt->close();
    mysqli_close($conexion);
}

function obtenerCitasPorTelefono($telefono) {
    $conexion = Conectarse();
    if (!$conexion) {
        die("Error de conexión a la base de datos");
    }

    $consulta = "
        SELECT 
            C.FechaVisita,
            C.HoraInicio,
            C.HoraFin,
            E.Estado,
            TV.Vivienda AS TipoVivienda,
            Z.Zona,
            V.MontoPedido AS Monto,
            TOF.Oferta AS TipoOferta,
            C.idCita
        FROM 
            Cita C
        JOIN 
            Cliente CL ON C.Cliente_idCliente = CL.idCliente
        JOIN 
            Estado E ON C.Estado_idEstado = E.idEstado
        JOIN 
            Vivienda V ON C.Vivienda_idVivienda = V.idVivienda
        JOIN 
            TipoVivienda TV ON V.TipoVivienda_idTipoV = TV.idTipoV
        JOIN 
            Zonas Z ON V.Zonas_idZona = Z.idZona
        JOIN 
            TipoOferta TOF ON V.TipoOferta_idTipoO = TOF.idTipoO
        WHERE 
            CL.Telefono = ?
        AND 
            C.Estado_idEstado != 2;
    ";

    $stmt = $conexion->prepare($consulta);
    if (!$stmt) {
        die("Error al preparar la consulta: " . $conexion->error);
    }

    $stmt->bind_param("s", $telefono);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado) {
        $citas = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    } else {
        echo "Error en la consulta: " . $stmt->error;
        $citas = [];
    }

    $stmt->close();
    mysqli_close($conexion);
    return $citas;
}

function cancelarCita($idCita) {
    $conexion = Conectarse();
    if (!$conexion) {
        die("Error de conexión a la base de datos");
    }

    $sql = "UPDATE Cita SET Estado_idEstado = 2 WHERE idCita = ?;";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idCita);
    
    if ($stmt->execute()) {
        echo "Cita cancelada correctamente.";
    } else {
        echo "Error al cancelar la cita: " . $stmt->error;
    }

    $stmt->close();
    mysqli_close($conexion);
}

function obtenerInmueblePorId($idInmueble) {
    $conexion = Conectarse();
    if (!$conexion) {
        return null;
    }

    $consulta = "
        SELECT 
            V.idVivienda, 
            V.Direccion, 
            V.MontoPedido, 
            V.Zonas_idZona, 
            V.TipoVivienda_idTipoV, 
            V.TipoOferta_idTipoO 
        FROM 
            Vivienda V
        WHERE 
            V.idVivienda = ?";

    $stmt = $conexion->prepare($consulta);
    $stmt->bind_param("i", $idInmueble);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $inmueble = $resultado->fetch_assoc();
    } else {
        $inmueble = null;
    }

    $stmt->close();
    mysqli_close($conexion);
    
    return $inmueble;
}

function obtenerTabajadoresPorId($idTrabajador) {
    $conexion = Conectarse();
    if (!$conexion) return null;

    $consulta = "SELECT idTrabajador, Nombre, Apellido, Usuario, Telefono, Correo, idCargo, idRol 
                 FROM trabajador WHERE idTrabajador = ? AND is_deleted = 0";
    $stmt = $conexion->prepare($consulta);
    if (!$stmt) {
        mysqli_close($conexion);
        return null;
    }
    $stmt->bind_param("i", $idTrabajador);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $trabajador = $resultado->num_rows > 0 ? $resultado->fetch_assoc() : null;
    $stmt->close();
    mysqli_close($conexion);
    return $trabajador;
}

function obtenerCitasVigentes() {
    $conexion = Conectarse();
    if (!$conexion) {
        return [];
    }

    $consulta = "
        SELECT 
            C.idCita,
            C.FechaVisita,
            C.HoraInicio,
            C.HoraFin,
            CL.Nombre AS NombreCliente,
            CL.Telefono AS TelefonoCliente,
            CL.Correo AS CorreoCliente,
            V.Direccion AS DireccionVivienda,
            E.Estado
        FROM 
            Cita C
        JOIN 
            Cliente CL ON C.Cliente_idCliente = CL.idCliente
        JOIN 
            Vivienda V ON C.Vivienda_idVivienda = V.idVivienda
        JOIN 
            Estado E ON C.Estado_idEstado = E.idEstado
    ";

    $resultado = mysqli_query($conexion, $consulta);

    if ($resultado) {
        $citas = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    } else {
        $citas = [];
    }

    mysqli_close($conexion);
    return $citas;
}


?>
