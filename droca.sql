-- Crear base (si no existe) y usarla
CREATE DATABASE IF NOT EXISTS droca
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE droca;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS password_history;
DROP TABLE IF EXISTS cita;
DROP TABLE IF EXISTS trabajador;
DROP TABLE IF EXISTS cliente;
DROP TABLE IF EXISTS vivienda;
DROP TABLE IF EXISTS tipooferta;
DROP TABLE IF EXISTS tipovivienda;
DROP TABLE IF EXISTS zonas;
DROP TABLE IF EXISTS estado;
DROP TABLE IF EXISTS rol_permiso;
DROP TABLE IF EXISTS permiso;
DROP TABLE IF EXISTS rol;
DROP TABLE IF EXISTS cargo;
DROP TABLE IF EXISTS area;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================
-- Núcleo de seguridad
-- =========================
CREATE TABLE area (
  idArea INT AUTO_INCREMENT PRIMARY KEY,
  NombreArea VARCHAR(100) NOT NULL,
  Descripcion TEXT,
  UNIQUE KEY uq_area_nombre (NombreArea)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cargo (
  idCargo INT AUTO_INCREMENT PRIMARY KEY,
  NombreCargo VARCHAR(100) NOT NULL,
  Descripcion TEXT,
  idArea INT NULL,
  UNIQUE KEY uq_cargo_nombre (NombreCargo),
  CONSTRAINT fk_cargo_area
    FOREIGN KEY (idArea) REFERENCES area(idArea)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rol (
  idRol INT AUTO_INCREMENT PRIMARY KEY,
  NombreRol VARCHAR(50) NOT NULL,
  Descripcion TEXT,
  UNIQUE KEY uq_rol_nombre (NombreRol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE permiso (
  idPermiso INT AUTO_INCREMENT PRIMARY KEY,
  Modulo VARCHAR(100) NOT NULL,
  Accion ENUM('ver','crear','editar','eliminar','bloquear') NOT NULL,
  UNIQUE KEY uq_permiso (Modulo, Accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rol_permiso (
  idRol INT NOT NULL,
  idPermiso INT NOT NULL,
  PRIMARY KEY (idRol, idPermiso),
  CONSTRAINT fk_rp_rol
    FOREIGN KEY (idRol) REFERENCES rol(idRol)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_rp_permiso
    FOREIGN KEY (idPermiso) REFERENCES permiso(idPermiso)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- Tablas maestras del negocio
-- =========================
CREATE TABLE estado (
  idEstado INT(11) NOT NULL AUTO_INCREMENT,
  Estado VARCHAR(100) NOT NULL,
  PRIMARY KEY (idEstado),
  UNIQUE KEY uq_estado (Estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE zonas (
  idZona INT(11) NOT NULL AUTO_INCREMENT,
  Zona VARCHAR(100) NOT NULL,
  PRIMARY KEY (idZona),
  UNIQUE KEY uq_zona (Zona)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tipovivienda (
  idTipoV INT(11) NOT NULL AUTO_INCREMENT,
  Vivienda VARCHAR(100) NOT NULL,
  PRIMARY KEY (idTipoV),
  UNIQUE KEY uq_tipovivienda (Vivienda)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tipooferta (
  idTipoO INT(11) NOT NULL AUTO_INCREMENT,
  Oferta VARCHAR(100) NOT NULL,
  PRIMARY KEY (idTipoO),
  UNIQUE KEY uq_tipooferta (Oferta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vivienda (
  idVivienda INT(11) NOT NULL AUTO_INCREMENT,
  Direccion VARCHAR(150) NOT NULL,
  MontoPedido INT(11) NOT NULL,
  Vendido TINYINT(1) NOT NULL DEFAULT 0,
  Zonas_idZona INT(11) NOT NULL,
  TipoVivienda_idTipoV INT(11) NOT NULL,
  TipoOferta_idTipoO INT(11) NOT NULL,
  is_deleted TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (idVivienda),
  KEY idx_vivienda_zona (Zonas_idZona),
  KEY idx_vivienda_tipov (TipoVivienda_idTipoV),
  KEY idx_vivienda_tipoo (TipoOferta_idTipoO),
  CONSTRAINT fk_vivienda_zona
    FOREIGN KEY (Zonas_idZona) REFERENCES zonas(idZona)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_vivienda_tipovivienda
    FOREIGN KEY (TipoVivienda_idTipoV) REFERENCES tipovivienda(idTipoV)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_vivienda_tipooferta
    FOREIGN KEY (TipoOferta_idTipoO) REFERENCES tipooferta(idTipoO)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zonas`
--

CREATE TABLE `zonas` (
  `idZona` int(11) NOT NULL,
  `Zona` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `zonas`
--

INSERT INTO `zonas` (`idZona`, `Zona`) VALUES
(1, 'Achumani'),
(2, 'Calacoto'),
(3, 'Sopocachi'),
(4, 'Irpavi'),
(5, 'Centro'),
(6, 'Miraflores'),
(7, 'Cota Cota'),
(8, 'Los Pinos'),
(9, 'Obrajes'),
(10, 'La Florida'),
(11, 'Río Abajo'),
(12, 'Mallasilla'),
(13, 'Alto Irpavi'),
(14, 'Ovejuyo'),
(15, 'San Jorge'),
(16, 'Aranjuez'),
(17, 'Umamanta'),
(18, 'Auquisamaña'),
(19, 'Pura Pura'),
(20, 'Villa Copacabana'),
(21, 'San Miguel'),
(22, 'Pasankeri'),
(23, 'Villa El Carmen'),
(24, 'Urb. Autopista'),
(25, 'Bella Vista'),
(26, 'El Pedregal'),
(27, 'San Alberto'),
(28, 'Pampahasi'),
(29, 'Achocalla'),
(30, 'Alto Obrajes'),
(31, 'Bolognia'),
(32, 'Chasquipampa'),
(33, 'Chuquiaguillo'),
(34, 'El Tejar'),
(35, 'Koani'),
(36, 'Llojeta'),
(37, 'Mecapaca'),
(38, 'San Pedro'),
(39, 'Seguencoma'),
(40, 'Tembladerani'),
(41, 'Achachicala'),
(42, 'Villa Fatima'),
(43, 'Villa San Antonio'),
(44, 'Mallasa');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cita`
--
ALTER TABLE `cita`
  ADD PRIMARY KEY (`idCita`),
  ADD KEY `Cita_Cliente` (`Cliente_idCliente`),
  ADD KEY `Cita_Estado` (`Estado_idEstado`),
  ADD KEY `Cita_Trabajador` (`Trabajador_idTrabajador`),
  ADD KEY `Cita_Vivienda` (`Vivienda_idVivienda`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idCliente`);

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`idEstado`);

--
-- Indices de la tabla `tipooferta`
--
ALTER TABLE `tipooferta`
  ADD PRIMARY KEY (`idTipoO`);

--
-- Indices de la tabla `tipovivienda`
--
ALTER TABLE `tipovivienda`
  ADD PRIMARY KEY (`idTipoV`);

--
-- Indices de la tabla `trabajador`
--
ALTER TABLE `trabajador`
  ADD PRIMARY KEY (`idTrabajador`);

--
-- Indices de la tabla `vivienda`
--
ALTER TABLE `vivienda`
  ADD PRIMARY KEY (`idVivienda`),
  ADD KEY `Vivienda_TipoOferta` (`TipoOferta_idTipoO`),
  ADD KEY `Vivienda_TipoVivienda` (`TipoVivienda_idTipoV`),
  ADD KEY `Vivienda_Zonas` (`Zonas_idZona`);

--
-- Indices de la tabla `zonas`
--
ALTER TABLE `zonas`
  ADD PRIMARY KEY (`idZona`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cita`
--
ALTER TABLE `cita`
  MODIFY `idCita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;
--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `idCliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;
--
ALTER TABLE `cliente`
ADD COLUMN `token` VARCHAR(255) DEFAULT NULL,
ADD COLUMN `token_expira` DATETIME DEFAULT NULL;
--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `idEstado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT de la tabla `tipooferta`
--
ALTER TABLE `tipooferta`
  MODIFY `idTipoO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT de la tabla `tipovivienda`
--
ALTER TABLE `tipovivienda`
  MODIFY `idTipoV` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT de la tabla `trabajador`
--
ALTER TABLE `trabajador`
  MODIFY `idTrabajador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT de la tabla `vivienda`
--
ALTER TABLE `vivienda`
  MODIFY `idVivienda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT de la tabla `zonas`
--
ALTER TABLE `zonas`
  MODIFY `idZona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cita`
--
ALTER TABLE `cita`
  ADD CONSTRAINT `Cita_Cliente` FOREIGN KEY (`Cliente_idCliente`) REFERENCES `cliente` (`idCliente`),
  ADD CONSTRAINT `Cita_Estado` FOREIGN KEY (`Estado_idEstado`) REFERENCES `estado` (`idEstado`),
  ADD CONSTRAINT `Cita_Trabajador` FOREIGN KEY (`Trabajador_idTrabajador`) REFERENCES `trabajador` (`idTrabajador`),
  ADD CONSTRAINT `Cita_Vivienda` FOREIGN KEY (`Vivienda_idVivienda`) REFERENCES `vivienda` (`idVivienda`);

--
-- Filtros para la tabla `vivienda`
--
ALTER TABLE `vivienda`
  ADD CONSTRAINT `Vivienda_TipoOferta` FOREIGN KEY (`TipoOferta_idTipoO`) REFERENCES `tipooferta` (`idTipoO`),
  ADD CONSTRAINT `Vivienda_TipoVivienda` FOREIGN KEY (`TipoVivienda_idTipoV`) REFERENCES `tipovivienda` (`idTipoV`),
  ADD CONSTRAINT `Vivienda_Zonas` FOREIGN KEY (`Zonas_idZona`) REFERENCES `zonas` (`idZona`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
