-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-10-2025 a las 21:38:39
-- Versión del servidor: 10.1.19-MariaDB
-- Versión de PHP: 5.5.38

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `droca`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cita`
--

CREATE TABLE `cita` (
  `idCita` int(11) NOT NULL,
  `FechaVisita` date NOT NULL,
  `FechaTrato` date DEFAULT NULL,
  `HoraInicio` time NOT NULL,
  `HoraFin` time NOT NULL,
  `esTrato` tinyint(1) DEFAULT NULL,
  `MontoOfrecido` decimal(50,5) DEFAULT NULL,
  `Trabajador_idTrabajador` int(11) DEFAULT NULL,
  `Estado_idEstado` int(11) NOT NULL,
  `Vivienda_idVivienda` int(11) NOT NULL,
  `Cliente_idCliente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `cita`
--

INSERT INTO `cita` (`idCita`, `FechaVisita`, `FechaTrato`, `HoraInicio`, `HoraFin`, `esTrato`, `MontoOfrecido`, `Trabajador_idTrabajador`, `Estado_idEstado`, `Vivienda_idVivienda`, `Cliente_idCliente`) VALUES
(1, '2024-11-01', '2024-11-05', '09:00:00', '09:30:00', 1, '150000.00000', 2, 1, 1, 1),
(2, '2024-11-02', '2024-11-06', '10:00:00', '10:45:00', 0, '120000.00000', 3, 2, 2, 2),
(3, '2024-11-03', '2024-11-07', '11:00:00', '11:30:00', 1, '200000.00000', 4, 3, 3, 3),
(4, '2024-11-04', '2024-11-08', '08:30:00', '09:15:00', 0, '95000.00000', 5, 1, 4, 4),
(5, '2024-11-05', '2024-11-09', '13:00:00', '13:45:00', 1, '110000.00000', 2, 2, 5, 5),
(6, '2024-11-06', '2024-11-10', '14:30:00', '15:15:00', 0, '180000.00000', 3, 3, 6, 6),
(7, '2024-11-07', '2024-11-11', '16:00:00', '16:30:00', 1, '210000.00000', 4, 1, 7, 7),
(8, '2024-11-08', '2024-11-12', '10:30:00', '11:00:00', 0, '160000.00000', 5, 2, 8, 8),
(9, '2024-11-09', '2024-11-13', '12:00:00', '12:45:00', 1, '95000.00000', 2, 3, 9, 9),
(10, '2024-11-10', '2024-11-14', '09:15:00', '09:45:00', 0, '250000.00000', 3, 1, 10, 10),
(11, '2024-11-11', '2024-11-15', '13:30:00', '14:00:00', 1, '130000.00000', 4, 2, 11, 11),
(12, '2024-11-12', '2024-11-16', '15:00:00', '15:30:00', 0, '90000.00000', 5, 3, 12, 12),
(13, '2024-11-13', '2024-11-17', '08:45:00', '09:30:00', 1, '140000.00000', 2, 1, 13, 13),
(14, '2024-11-14', '2024-11-18', '10:15:00', '10:45:00', 0, '170000.00000', 3, 2, 14, 14),
(15, '2024-11-15', '2024-11-19', '11:45:00', '12:15:00', 1, '200000.00000', 4, 3, 15, 15),
(16, '2024-11-16', '2024-11-20', '14:15:00', '15:00:00', 0, '150000.00000', 5, 1, 1, 16),
(17, '2024-11-17', '2024-11-21', '09:00:00', '09:30:00', 1, '120000.00000', 2, 2, 2, 17),
(18, '2024-11-18', '2024-11-22', '10:00:00', '10:45:00', 0, '200000.00000', 3, 3, 3, 18),
(19, '2024-11-19', '2024-11-23', '11:00:00', '11:30:00', 1, '95000.00000', 4, 1, 4, 19),
(20, '2024-11-20', '2024-11-24', '08:30:00', '09:15:00', 0, '110000.00000', 5, 2, 5, 20),
(21, '2024-11-21', '2024-11-25', '13:00:00', '13:45:00', 1, '180000.00000', 2, 3, 6, 21),
(22, '2024-11-22', '2024-11-26', '14:30:00', '15:15:00', 0, '210000.00000', 3, 1, 7, 22),
(23, '2024-11-23', '2024-11-27', '16:00:00', '16:30:00', 1, '160000.00000', 4, 2, 8, 23),
(24, '2024-11-24', '2024-11-28', '10:30:00', '11:00:00', 0, '95000.00000', 5, 3, 9, 24),
(25, '2024-11-25', '2024-11-29', '12:00:00', '12:45:00', 1, '250000.00000', 2, 1, 10, 25),
(26, '2024-11-26', '2024-11-30', '09:15:00', '09:45:00', 0, '130000.00000', 3, 2, 11, 26),
(27, '2024-11-27', '2024-12-01', '13:30:00', '14:00:00', 1, '90000.00000', 4, 3, 12, 27),
(28, '2024-11-28', '2024-12-02', '15:00:00', '15:30:00', 0, '140000.00000', 5, 1, 13, 28),
(29, '2024-11-29', '2024-12-03', '08:45:00', '09:30:00', 1, '170000.00000', 2, 2, 14, 29),
(30, '2024-11-30', '2024-12-04', '10:15:00', '10:45:00', 0, '200000.00000', 3, 3, 15, 30),
(31, '2024-12-01', '2024-12-05', '11:45:00', '12:15:00', 1, '150000.00000', 4, 1, 1, 31),
(32, '2024-12-02', '2024-12-06', '14:15:00', '15:00:00', 0, '120000.00000', 5, 2, 2, 32),
(33, '2024-12-03', '2024-12-07', '09:00:00', '09:30:00', 1, '200000.00000', 2, 3, 3, 33),
(34, '2024-12-04', '2024-12-08', '10:00:00', '10:45:00', 0, '95000.00000', 3, 1, 4, 34),
(35, '2024-12-05', '2024-12-09', '11:00:00', '11:30:00', 1, '110000.00000', 4, 2, 5, 35),
(36, '2024-12-06', '2024-12-10', '08:30:00', '09:15:00', 0, '180000.00000', 5, 3, 6, 36),
(37, '2024-12-07', '2024-12-11', '13:00:00', '13:45:00', 1, '210000.00000', 2, 1, 7, 37),
(38, '2024-12-08', '2024-12-12', '14:30:00', '15:15:00', 0, '160000.00000', 3, 2, 8, 38),
(39, '2024-12-09', '2024-12-13', '16:00:00', '16:30:00', 1, '95000.00000', 4, 3, 9, 39),
(40, '2024-12-10', '2024-12-14', '10:30:00', '11:00:00', 0, '250000.00000', 5, 1, 10, 40),
(41, '2024-12-11', '2024-12-15', '12:00:00', '12:45:00', 1, '130000.00000', 2, 2, 11, 41),
(42, '2024-12-12', '2024-12-16', '09:15:00', '09:45:00', 0, '90000.00000', 3, 3, 12, 42),
(43, '2024-12-13', '2024-12-17', '13:30:00', '14:00:00', 1, '140000.00000', 4, 1, 13, 43),
(44, '2024-12-14', '2024-12-18', '15:00:00', '15:30:00', 0, '170000.00000', 5, 2, 14, 44),
(45, '2024-12-15', '2024-12-19', '08:45:00', '09:30:00', 1, '200000.00000', 2, 3, 15, 45),
(46, '2024-12-16', '2024-12-20', '10:15:00', '10:45:00', 0, '150000.00000', 3, 1, 1, 46),
(47, '2024-12-17', '2024-12-21', '11:45:00', '12:15:00', 1, '120000.00000', 4, 2, 2, 47),
(48, '2024-12-18', '2024-12-22', '14:15:00', '15:00:00', 0, '200000.00000', 5, 3, 3, 48),
(49, '2024-12-19', '2024-12-23', '09:00:00', '09:30:00', 1, '95000.00000', 2, 1, 4, 49),
(50, '2024-12-20', '2024-12-24', '10:00:00', '10:45:00', 0, '110000.00000', 3, 2, 5, 50),
(51, '2024-12-21', '2024-12-25', '11:00:00', '11:30:00', 1, '180000.00000', 4, 3, 6, 51),
(52, '2024-12-22', '2024-12-26', '08:30:00', '09:15:00', 0, '210000.00000', 5, 1, 7, 52),
(53, '2024-12-23', '2024-12-27', '13:00:00', '13:45:00', 1, '160000.00000', 2, 2, 8, 53),
(54, '2024-12-24', '2024-12-28', '14:30:00', '15:15:00', 0, '95000.00000', 3, 3, 9, 54),
(55, '2024-12-25', '2024-12-29', '16:00:00', '16:30:00', 1, '250000.00000', 4, 1, 10, 55),
(56, '2024-12-26', '2024-12-30', '10:30:00', '11:00:00', 0, '130000.00000', 5, 2, 11, 56),
(57, '2024-12-27', '2024-12-31', '12:00:00', '12:45:00', 1, '90000.00000', 2, 3, 12, 57),
(58, '2024-12-28', '2025-01-01', '09:15:00', '09:45:00', 0, '140000.00000', 3, 1, 13, 58),
(59, '2024-12-29', '2025-01-02', '13:30:00', '14:00:00', 1, '170000.00000', 4, 2, 14, 59),
(60, '2024-12-30', '2025-01-03', '15:00:00', '15:30:00', 0, '200000.00000', 5, 3, 15, 60),
(61, '2025-01-01', '2025-01-05', '08:45:00', '09:30:00', 1, '150000.00000', 2, 1, 1, 61),
(62, '2025-01-02', '2025-01-06', '10:15:00', '10:45:00', 0, '120000.00000', 3, 2, 2, 62),
(63, '2025-01-03', '2025-01-07', '11:45:00', '12:15:00', 1, '200000.00000', 4, 3, 3, 63),
(64, '2025-01-04', '2025-01-08', '14:15:00', '15:00:00', 0, '95000.00000', 5, 1, 4, 64),
(65, '2025-01-05', '2025-01-09', '09:00:00', '09:30:00', 1, '110000.00000', 2, 2, 5, 65),
(66, '2025-01-06', '2025-01-10', '10:00:00', '10:45:00', 0, '180000.00000', 3, 3, 6, 66),
(67, '2025-01-07', '2025-01-11', '11:00:00', '11:30:00', 1, '210000.00000', 4, 1, 7, 67),
(68, '2025-01-08', '2025-01-12', '08:30:00', '09:15:00', 0, '160000.00000', 5, 2, 8, 68),
(69, '2025-01-09', '2025-01-13', '13:00:00', '13:45:00', 1, '95000.00000', 2, 3, 9, 69),
(70, '2025-01-10', '2025-01-14', '14:30:00', '15:15:00', 0, '250000.00000', 3, 1, 10, 70),
(71, '2025-01-11', '2025-01-15', '16:00:00', '16:30:00', 1, '130000.00000', 4, 2, 11, 71),
(72, '2025-01-12', '2025-01-16', '10:30:00', '11:00:00', 0, '90000.00000', 5, 3, 12, 72),
(73, '2025-01-13', '2025-01-17', '12:00:00', '12:45:00', 1, '140000.00000', 2, 1, 13, 73),
(74, '2025-01-14', '2025-01-18', '09:15:00', '09:45:00', 0, '170000.00000', 3, 2, 14, 74),
(75, '2025-01-15', '2025-01-19', '13:30:00', '14:00:00', 1, '200000.00000', 4, 3, 15, 75),
(76, '2025-01-16', '2025-01-20', '15:00:00', '15:30:00', 0, '150000.00000', 5, 1, 1, 76),
(77, '2025-01-17', '2025-01-21', '08:45:00', '09:30:00', 1, '120000.00000', 2, 2, 2, 77),
(78, '2025-01-18', '2025-01-22', '10:15:00', '10:45:00', 0, '200000.00000', 3, 3, 3, 78),
(79, '2025-01-19', '2025-01-23', '11:45:00', '12:15:00', 1, '95000.00000', 4, 1, 4, 79),
(80, '2025-01-20', '2025-01-24', '14:15:00', '15:00:00', 0, '110000.00000', 5, 2, 5, 80);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `idCliente` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido` varchar(100) NOT NULL,
  `Usuario` varchar(100) NOT NULL,
  `Contraseña` varchar(100) NOT NULL,
  `Telefono` char(8) NOT NULL,
  `Correo` varchar(100) NOT NULL,
  `Direccion` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`idCliente`, `Nombre`, `Apellido`, `Usuario`, `Contraseña`, `Telefono`, `Correo`, `Direccion`) VALUES
(1, 'Juan Alberto', 'Pérez Hinojosa', 'JAPerez', '12345678jp', '70000001', 'juan.perez@gmail.com', 'Avenida Arce #564'),
(2, 'María Flores', '', '', '', '70000002', 'maria.flores@outlook.com', ''),
(3, 'Carlos Choque', '', '', '', '70000003', 'carlos.choque@hotmail.com', ''),
(4, 'Ana Quispe', '', '', '', '70000004', 'ana.quispe@yahoo.com', ''),
(5, 'Luis Gutiérrez', '', '', '', '70000005', 'luis.gutierrez@gmail.com', ''),
(6, 'Rosa Mamani', '', '', '', '70000006', 'rosa.mamani@outlook.com', ''),
(7, 'José Condori', '', '', '', '70000007', 'jose.condori@hotmail.com', ''),
(8, 'Carmen Apaza', '', '', '', '70000008', 'carmen.apaza@yahoo.com', ''),
(9, 'Miguel Sánchez', '', '', '', '70000009', 'miguel.sanchez@gmail.com', ''),
(10, 'Laura Cárdenas', '', '', '', '70000010', 'laura.cardenas@outlook.com', ''),
(11, 'Daniel Aguilar', '', '', '', '70000011', 'daniel.aguilar@hotmail.com', ''),
(12, 'Paola Vargas', '', '', '', '70000012', 'paola.vargas@yahoo.com', ''),
(13, 'Fernando López', '', '', '', '70000013', 'fernando.lopez@gmail.com', ''),
(14, 'Claudia Salazar', '', '', '', '70000014', 'claudia.salazar@outlook.com', ''),
(15, 'Ricardo Ramos', '', '', '', '70000015', 'ricardo.ramos@hotmail.com', ''),
(16, 'Nancy Flores', '', '', '', '70000016', 'nancy.flores@yahoo.com', ''),
(17, 'Jorge Fernández', '', '', '', '70000017', 'jorge.fernandez@gmail.com', ''),
(18, 'Gabriela Suárez', '', '', '', '70000018', 'gabriela.suarez@outlook.com', ''),
(19, 'Esteban Soto', '', '', '', '70000019', 'esteban.soto@hotmail.com', ''),
(20, 'Verónica Medina', '', '', '', '70000020', 'veronica.medina@yahoo.com', ''),
(21, 'David Camacho', '', '', '', '70000021', 'david.camacho@gmail.com', ''),
(22, 'Sandra Paredes', '', '', '', '70000022', 'sandra.paredes@outlook.com', ''),
(23, 'Diego Romero', '', '', '', '70000023', 'diego.romero@hotmail.com', ''),
(24, 'Patricia Chávez', '', '', '', '70000024', 'patricia.chavez@yahoo.com', ''),
(25, 'Hugo Valverde', '', '', '', '70000025', 'hugo.valverde@gmail.com', ''),
(26, 'Sofía Villegas', '', '', '', '70000026', 'sofia.villegas@outlook.com', ''),
(27, 'Pedro Arce', '', '', '', '70000027', 'pedro.arce@hotmail.com', ''),
(28, 'Valeria Rivera', '', '', '', '70000028', 'valeria.rivera@yahoo.com', ''),
(29, 'Andrés Palacios', '', '', '', '70000029', 'andres.palacios@gmail.com', ''),
(30, 'Carolina Cáceres', '', '', '', '70000030', 'carolina.caceres@outlook.com', ''),
(31, 'Oscar Callisaya', '', '', '', '70000031', 'oscar.callisaya@hotmail.com', ''),
(32, 'Teresa Espinoza', '', '', '', '70000032', 'teresa.espinoza@yahoo.com', ''),
(33, 'Alejandro Limachi', '', '', '', '70000033', 'alejandro.limachi@gmail.com', ''),
(34, 'Liliana Rojas', '', '', '', '70000034', 'liliana.rojas@outlook.com', ''),
(35, 'Raúl Salinas', '', '', '', '70000035', 'raul.salinas@hotmail.com', ''),
(36, 'Gloria Torrico', '', '', '', '70000036', 'gloria.torrico@yahoo.com', ''),
(37, 'Iván Villarroel', '', '', '', '70000037', 'ivan.villarroel@gmail.com', ''),
(38, 'Mónica Estrada', '', '', '', '70000038', 'monica.estrada@outlook.com', ''),
(39, 'Felipe Alarcón', '', '', '', '70000039', 'felipe.alarcon@hotmail.com', ''),
(40, 'Paula Zeballos', '', '', '', '70000040', 'paula.zeballos@yahoo.com', ''),
(41, 'César Ayala', '', '', '', '70000041', 'cesar.ayala@gmail.com', ''),
(42, 'Lorena Céspedes', '', '', '', '70000042', 'lorena.cespedes@outlook.com', ''),
(43, 'Mario Guzmán', '', '', '', '70000043', 'mario.guzman@hotmail.com', ''),
(44, 'Elena Mercado', '', '', '', '70000044', 'elena.mercado@yahoo.com', ''),
(45, 'Javier Vargas', '', '', '', '70000045', 'javier.vargas@gmail.com', ''),
(46, 'Natalia Ortiz', '', '', '', '70000046', 'natalia.ortiz@outlook.com', ''),
(47, 'Tomás Gálvez', '', '', '', '70000047', 'tomas.galvez@hotmail.com', ''),
(48, 'Carla Nava', '', '', '', '70000048', 'carla.nava@yahoo.com', ''),
(49, 'Sebastián Arias', '', '', '', '70000049', 'sebastian.arias@gmail.com', ''),
(50, 'Florencia Morales', '', '', '', '70000050', 'florencia.morales@outlook.com', ''),
(51, 'Martín Espinoza', '', '', '', '70000051', 'martin.espinoza@hotmail.com', ''),
(52, 'Angélica Gutiérrez', '', '', '', '70000052', 'angelica.gutierrez@yahoo.com', ''),
(53, 'Ramiro Ponce', '', '', '', '70000053', 'ramiro.ponce@gmail.com', ''),
(54, 'Pamela Andrade', '', '', '', '70000054', 'pamela.andrade@outlook.com', ''),
(55, 'Cristian Céspedes', '', '', '', '70000055', 'cristian.cespedes@hotmail.com', ''),
(56, 'Luz Velasco', '', '', '', '70000056', 'luz.velasco@yahoo.com', ''),
(57, 'Álvaro Calderón', '', '', '', '70000057', 'alvaro.calderon@gmail.com', ''),
(58, 'Julia Miranda', '', '', '', '70000058', 'julia.miranda@outlook.com', ''),
(59, 'Arturo Núñez', '', '', '', '70000059', 'arturo.nunez@hotmail.com', ''),
(60, 'Marta Bautista', '', '', '', '70000060', 'marta.bautista@yahoo.com', ''),
(61, 'Gustavo Castro', '', '', '', '70000061', 'gustavo.castro@gmail.com', ''),
(62, 'Rebeca Paz', '', '', '', '70000062', 'rebeca.paz@outlook.com', ''),
(63, 'Efraín Rocha', '', '', '', '70000063', 'efrain.rocha@hotmail.com', ''),
(64, 'Clara Montaño', '', '', '', '70000064', 'clara.montano@yahoo.com', ''),
(65, 'Eduardo Delgado', '', '', '', '70000065', 'eduardo.delgado@gmail.com', ''),
(66, 'Silvia Mendoza', '', '', '', '70000066', 'silvia.mendoza@outlook.com', ''),
(67, 'Víctor Solís', '', '', '', '70000067', 'victor.solis@hotmail.com', ''),
(68, 'Diana Valencia', '', '', '', '70000068', 'diana.valencia@yahoo.com', ''),
(69, 'Rafael Cortés', '', '', '', '70000069', 'rafael.cortes@gmail.com', ''),
(70, 'Cecilia Peredo', '', '', '', '70000070', 'cecilia.peredo@outlook.com', ''),
(71, 'Edgar Villanueva', '', '', '', '70000071', 'edgar.villanueva@hotmail.com', ''),
(72, 'Fabiola Peña', '', '', '', '70000072', 'fabiola.pena@yahoo.com', ''),
(73, 'Sergio Maldonado', '', '', '', '70000073', 'sergio.maldonado@gmail.com', ''),
(74, 'Patricia Quiroga', '', '', '', '70000074', 'patricia.quiroga@outlook.com', ''),
(75, 'Adriana Ruiz', '', '', '', '70000075', 'adriana.ruiz@hotmail.com', ''),
(76, 'Rodolfo Mejía', '', '', '', '70000076', 'rodolfo.mejia@yahoo.com', ''),
(77, 'Gina Terrazas', '', '', '', '70000077', 'gina.terrazas@gmail.com', ''),
(78, 'Diego Salcedo', '', '', '', '70000078', 'diego.salcedo@outlook.com', ''),
(79, 'Melisa Campero', '', '', '', '70000079', 'melisa.campero@hotmail.com', ''),
(80, 'Julio Arias', '', '', '', '70000080', 'julio.arias@yahoo.com', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `idEstado` int(11) NOT NULL,
  `Estado` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`idEstado`, `Estado`) VALUES
(1, 'Vigente'),
(2, 'Cancelado'),
(3, 'Acordado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipooferta`
--

CREATE TABLE `tipooferta` (
  `idTipoO` int(11) NOT NULL,
  `Oferta` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `tipooferta`
--

INSERT INTO `tipooferta` (`idTipoO`, `Oferta`) VALUES
(1, 'Alquiler'),
(2, 'Anticrético'),
(3, 'Venta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipovivienda`
--

CREATE TABLE `tipovivienda` (
  `idTipoV` int(11) NOT NULL,
  `Vivienda` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `tipovivienda`
--

INSERT INTO `tipovivienda` (`idTipoV`, `Vivienda`) VALUES
(1, 'Casa'),
(2, 'Departamento'),
(3, 'Dúplex'),
(4, 'Condominio'),
(5, 'Cabaña'),
(6, 'Chalet'),
(7, 'Habitación');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajador`
--

CREATE TABLE `trabajador` (
  `idTrabajador` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido` varchar(100) NOT NULL,
  `Usuario` varchar(100) NOT NULL,
  `Contraseña` varchar(100) NOT NULL,
  `Telefono` char(8) NOT NULL,
  `Correo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `trabajador`
--

INSERT INTO `trabajador` (`idTrabajador`, `Nombre`, `Apellido`, `Usuario`, `Contraseña`, `Telefono`, `Correo`) VALUES
(1, 'Mónica Maritza', 'Muller Soliz', '', 'Monica12345678', '12345678', 'monica@droca.com'),
(2, 'Juan Pérez', '', '', '', '76543210', 'juan.perez@droca.com'),
(3, 'María Flores', '', '', '', '78901234', 'maria.flores@droca.com'),
(4, 'Carlos Choque', '', '', '', '71234567', 'carlos.choque@droca.com'),
(5, 'Ana Quispe', '', '', '', '75432109', 'ana.quispe@droca.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vivienda`
--

CREATE TABLE `vivienda` (
  `idVivienda` int(11) NOT NULL,
  `Direccion` varchar(100) NOT NULL,
  `MontoPedido` int(11) NOT NULL,
  `Vendido` tinyint(1) NOT NULL,
  `Zonas_idZona` int(11) NOT NULL,
  `TipoVivienda_idTipoV` int(11) NOT NULL,
  `TipoOferta_idTipoO` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `vivienda`
--

INSERT INTO `vivienda` (`idVivienda`, `Direccion`, `MontoPedido`, `Vendido`, `Zonas_idZona`, `TipoVivienda_idTipoV`, `TipoOferta_idTipoO`) VALUES
(1, 'Calle 10 de Achumani, Nro. 123', 120000, 0, 1, 1, 1),
(2, 'Av. Ballivián, Calacoto, Nro. 456', 200000, 0, 2, 2, 3),
(3, 'Calle Montenegro, Sopocachi, Nro. 789', 95000, 1, 3, 3, 2),
(4, 'Av. Strongest, Irpavi, Nro. 321', 150000, 0, 4, 4, 1),
(5, 'Calle Comercio, Centro, Nro. 654', 110000, 1, 5, 1, 3),
(6, 'Av. Busch, Miraflores, Nro. 987', 180000, 0, 6, 2, 2),
(7, 'Calle 23, Cota Cota, Nro. 147', 210000, 0, 7, 5, 1),
(8, 'Av. Los Pinos, Nro. 258', 160000, 1, 8, 6, 2),
(9, 'Calle Murillo, Obrajes, Nro. 369', 95000, 0, 9, 7, 3),
(10, 'Av. La Florida, Nro. 753', 250000, 1, 10, 7, 1),
(11, 'Calle 7, Río Abajo, Nro. 951', 130000, 0, 11, 3, 3),
(12, 'Calle El Retiro, Mallasilla, Nro. 456', 90000, 0, 12, 2, 1),
(13, 'Calle Principal, Alto Irpavi, Nro. 789', 140000, 1, 13, 1, 2),
(14, 'Av. Ovejuyo, Nro. 123', 170000, 0, 14, 4, 3),
(15, 'Calle Jaimes Freyre, San Jorge, Nro. 456', 200000, 1, 15, 5, 1);

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
