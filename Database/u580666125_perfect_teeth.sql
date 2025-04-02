-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 02-04-2025 a las 22:36:20
-- Versión del servidor: 10.11.10-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u580666125_perfect_teeth`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_cita` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_doctor` int(11) NOT NULL,
  `fecha_cita` date NOT NULL,
  `hora_cita` varchar(20) DEFAULT NULL,
  `id_consultas` int(11) NOT NULL,
  `estado` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_cita`, `id_paciente`, `id_doctor`, `fecha_cita`, `hora_cita`, `id_consultas`, `estado`) VALUES
(1, 2, 1, '2025-03-28', '02:00 PM - 03:00 PM', 6, 'A'),
(2, 2, 1, '2025-04-01', '02:00 PM - 03:00 PM', 6, 'A'),
(3, 2, 1, '2025-04-02', '02:00 PM - 03:00 PM', 6, 'A'),
(4, 3, 1, '2025-03-28', '05:00 PM - 06:00 PM', 6, 'A'),
(5, 4, 1, '2025-03-29', '09:00 AM - 10:00 AM', 3, 'A'),
(6, 5, 1, '2025-03-31', '10:00 AM - 11:00 AM', 6, 'A'),
(7, 6, 1, '2025-03-31', '02:00 PM - 03:00 PM', 3, 'A'),
(8, 7, 1, '2025-03-31', '05:00 PM - 06:00 PM', 4, 'A'),
(9, 8, 1, '2025-04-01', '05:00 PM - 06:00 PM', 3, 'A'),
(10, 9, 1, '2025-04-02', '04:00 PM - 05:00 PM', 3, 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultas`
--

CREATE TABLE `consultas` (
  `id_consultas` int(11) NOT NULL,
  `tipo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Volcado de datos para la tabla `consultas`
--

INSERT INTO `consultas` (`id_consultas`, `tipo`) VALUES
(1, 'Valoración y diagnóstico '),
(2, 'Limpieza o profilaxis dental'),
(3, 'Resinas (calzas)'),
(4, 'Endodoncia (tratamiento de conducto)'),
(5, 'Ortodoncia'),
(6, 'Prótesis total, fija o removible '),
(7, 'Cirugía oral'),
(8, 'Implantes'),
(9, 'Estética dental');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doctor`
--

CREATE TABLE `doctor` (
  `id_doctor` int(11) NOT NULL,
  `nombreD` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `sexo` varchar(255) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `telefono` varchar(255) NOT NULL,
  `correo_eletronico` varchar(255) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `id_especialidad` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Volcado de datos para la tabla `doctor`
--

INSERT INTO `doctor` (`id_doctor`, `nombreD`, `apellido`, `sexo`, `fecha_nacimiento`, `telefono`, `correo_eletronico`, `clave`, `id_especialidad`) VALUES
(1, 'Emily Valeria', 'Bernal Jaimes', 'Femenino', '2001-07-05', '3105547320', 'emilybernal902@gmail.com', '$2y$12$Qc6mbuTsSoslxk3JNGkljeIexJW1.6qIpXz3WiMZf6W3aq5tfSsrO', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidad`
--

CREATE TABLE `especialidad` (
  `id_especialidad` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `especialidad`
--

INSERT INTO `especialidad` (`id_especialidad`, `tipo`) VALUES
(1, 'Odontólogo general'),
(2, 'Odontopediatra'),
(3, 'Ortodoncista'),
(4, 'Patólogo oral'),
(5, 'Endodoncista');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `informe_medico`
--

CREATE TABLE `informe_medico` (
  `id_informe` int(11) NOT NULL,
  `id_cita` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `examen_intraoral` text DEFAULT NULL,
  `examen_extraoral` text DEFAULT NULL,
  `examen_atm` text DEFAULT NULL,
  `observacion_intraoral` text DEFAULT NULL,
  `observacion_extraoral_atm` text DEFAULT NULL,
  `descripcion_radiografica` text DEFAULT NULL,
  `diagnostico_periodontal` text DEFAULT NULL,
  `pronostico` text DEFAULT NULL,
  `evolucion` text DEFAULT NULL,
  `diagnostico` text DEFAULT NULL,
  `radiografia` varchar(255) DEFAULT NULL,
  `foto_boca` varchar(255) DEFAULT NULL,
  `costo` text DEFAULT NULL,
  `plan_tratamiento` text DEFAULT NULL,
  `odontogram_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`odontogram_data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `informe_medico`
--

INSERT INTO `informe_medico` (`id_informe`, `id_cita`, `id_paciente`, `examen_intraoral`, `examen_extraoral`, `examen_atm`, `observacion_intraoral`, `observacion_extraoral_atm`, `descripcion_radiografica`, `diagnostico_periodontal`, `pronostico`, `evolucion`, `diagnostico`, `radiografia`, `foto_boca`, `costo`, `plan_tratamiento`, `odontogram_data`) VALUES
(1, 1, 2, '', '', 'Aparentemente normal', 'normal', 'Aparentemente sin alteración', '', 'Gingivitis generalizada', 'Bueno', '', '', '', '', '', '', '[{\"tooth\":12,\"condition\":\"Corona desadaptada\"}]'),
(2, 3, 2, 'Encía enrojecida,lengua movil y papilada', 'Simetría facial', '', 'Aparentemente normal', 'Crepitacion disco articular', 'Perdida osea, lesiones apicales coronas desadaptadas', 'Gingivitis generalizada,', 'Bueno', 'Se realiza', 'Fija metalporcelana desadaptada', '2_radiografia_1743192890.jpg', '', 'Protesis transicional (500.000), 2 coronas 12,26(1.800.000),removible superior(3.000.000) -15%: 4.500.000', 'Exodoncia 21-24, protesis transicional, coronas individuales 12-26, removible superior diente duraton', '[{\"tooth\":12,\"condition\":\"Pilar de fija desadaptado\"},{\"tooth\":26,\"condition\":\"Pilar de fija desaptado\"},{\"tooth\":24,\"condition\":\"Movilidad 3. Pilar de fija\"},{\"tooth\":21,\"condition\":\"Movilidad 3- pilar de fija\"}]'),
(3, 6, 5, '', '', '', '', '', '', 'Salud periodontal', 'bueno', '', 'Desgaste anterior por bruxismo', '', '', '3.200.000 4 coronas anteriores', '4 coronas anteriores', '[{\"tooth\":12,\"condition\":\"desgaste\"},{\"tooth\":11,\"condition\":\"desgaste\"},{\"tooth\":21,\"condition\":\"desgaste\"},{\"tooth\":22,\"condition\":\"Desgaste\"},{\"tooth\":13,\"condition\":\"Ausente\"},{\"tooth\":23,\"condition\":\"AUSENTE\"}]'),
(4, 7, 6, '', '', '', '', '', '', 'Salud periodontal', '', 'Se realiza resina diente 13 por cervical, resina el diente 36 oclusolingual', '', '', '', '150.000 por resina 13 y 36', 'Resina cervical diente 13, resina oclusodistolingual 46', '[{\"tooth\":13,\"condition\":\"Caries cervical\"},{\"tooth\":36,\"condition\":\"Amalgama desadaptada\"}]'),
(5, 8, 7, '', '', '', '', '', '', '', 'bueno', '', '', '', '', '350.000 endodoncia multiradicular 36, alargamiento coronas y resina 150.000', '', '[{\"tooth\":36,\"condition\":\"Caries icdas 5\"}]'),
(6, 9, 8, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[{\"tooth\":17,\"condition\":\"Caries oclusal\"}]'),
(7, 10, 9, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[{\"tooth\":47,\"condition\":\"Resina desadaptada\"}]');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_doctor` int(11) NOT NULL,
  `tipo` enum('nueva_cita','cancelacion') NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','vista','eliminada') NOT NULL DEFAULT 'pendiente',
  `fecha` datetime DEFAULT current_timestamp(),
  `leida` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `id_paciente` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `telefono` varchar(50) NOT NULL,
  `sexo` varchar(60) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `correo_electronico` varchar(255) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `tipo_imagen` varchar(20) DEFAULT 'perfil',
  `eps` varchar(255) DEFAULT NULL,
  `ocupacion` varchar(255) DEFAULT NULL,
  `estado_civil` varchar(50) DEFAULT NULL,
  `cedula` varchar(50) DEFAULT NULL,
  `emergencia_nombre` varchar(255) DEFAULT NULL,
  `emergencia_telefono` varchar(50) DEFAULT NULL,
  `menor_acompanante` varchar(255) DEFAULT NULL,
  `menor_parentesco` varchar(50) DEFAULT NULL,
  `menor_telefono` varchar(50) DEFAULT NULL,
  `tipo_sangre` varchar(10) DEFAULT NULL,
  `alertas_medicas` text DEFAULT NULL,
  `lugar_direccion_residencia` varchar(255) DEFAULT NULL,
  `numero_documento` varchar(20) DEFAULT NULL,
  `historia_cardiovasculares` enum('Sí','No') DEFAULT 'No',
  `historia_hemorragicas` enum('Sí','No') DEFAULT 'No',
  `historia_dermatologicas` enum('Sí','No') DEFAULT 'No',
  `historia_mentales` enum('Sí','No') DEFAULT 'No',
  `historia_diabetes` enum('Sí','No') DEFAULT 'No',
  `historia_cancer` enum('Sí','No') DEFAULT 'No',
  `historia_artritis` enum('Sí','No') DEFAULT 'No',
  `historia_alergias` enum('Sí','No') DEFAULT 'No',
  `historia_cirugias` enum('Sí','No') DEFAULT 'No',
  `historia_otros` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id_paciente`, `nombre`, `apellido`, `telefono`, `sexo`, `fecha_nacimiento`, `correo_electronico`, `clave`, `foto_perfil`, `tipo_imagen`, `eps`, `ocupacion`, `estado_civil`, `cedula`, `emergencia_nombre`, `emergencia_telefono`, `menor_acompanante`, `menor_parentesco`, `menor_telefono`, `tipo_sangre`, `alertas_medicas`, `lugar_direccion_residencia`, `numero_documento`, `historia_cardiovasculares`, `historia_hemorragicas`, `historia_dermatologicas`, `historia_mentales`, `historia_diabetes`, `historia_cancer`, `historia_artritis`, `historia_alergias`, `historia_cirugias`, `historia_otros`) VALUES
(1, 'Lefty ran', 'Cuenta bot', '3183038190', 'Masculino', '2207-03-20', 'leftyrancuentabot@gmail.com', '$2y$10$g7FPmUgSppT4VQqisEAiH.T1tLAN4f5JrEvvOodi./wE573yvHj/i', NULL, 'perfil', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'No', NULL),
(2, 'Horacio', 'Rivera pardo', '31578133426', 'Masculino', '1969-03-13', 'horacio.riverapardo@consultorioemilybernal.com', '$2y$10$kDMSvvr1CteDmYPUV386Nuz23yaAXUhzSYUxb/B.X0JhymKGtuKna', NULL, 'perfil', 'coosalud', 'independiente', 'soltero', '', 'Leticia rivera', '', '', '', '', 'A positivo', 'Asa I', 'Vereda amarillo', NULL, 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'ninguno'),
(3, 'hugo hernandez', 'bohorquez Rodríguez', '3103169496', 'Masculino', '1969-10-03', 'hugohernandez.bohorquezrodrguez@consultorioemilybernal.com', '$2y$10$3x1/0MoJSdrNISGDEljCU.KnkvnzSH47rNcaWU0vSLBBLfLeScmfC', NULL, 'perfil', 'jersalud', 'profesor', 'soltero', '91274294', 'Diego Nicolás bohorquez', '3124237416', '', '', '', 'A negativo', 'ASAI', 'Cra 9·13-25', NULL, 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'ninguno'),
(4, 'Martin Camilo', 'Delgado Beltran', '3115698667', 'Masculino', '2005-11-01', 'martincamilo.delgadobeltran@consultorioemilybernal.com', '$2y$10$87YyJpu/.O7fi6h/b7YzV.PhuD1fTiM2sThzKoXCKyQTslGm5wgpS', NULL, 'perfil', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'No', NULL),
(5, 'Nidia soledad', 'Muran alfonso', '3212129236', 'Femenino', '1966-04-18', 'nidiasoledad.muranalfonso@consultorioemilybernal.com', '$2y$10$WTatUcJC.4B.v7mmlDe6Ju4yafhlTFfMxyVY96BwLWJJk1L4KBQA2', NULL, 'perfil', 'Nueva eps', 'independiente', 'casada', '51829338', 'Elver bernal', '3115081387', '', '', '', 'O positivo', 'Hipertensa 9. 3 cesarías, tinectomia,hernia umbelicar, vesicula biliar\\r\\nMedicamento Losartan ', 'Vereda pajales', NULL, 'Sí', 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'Sí', ''),
(6, 'Jose Luis', 'Paez caballero', '3212754853', 'Masculino', '1994-04-23', 'joseluis.paezcaballero@consultorioemilybernal.com', '$2y$10$WBVc8hFDRZeeCvr4KRqdV.IPUUyPaQZhgXlAWxwU9OdNByQpY67ki', NULL, 'perfil', 'sura', 'ecopetrol', 'Unión libre', '1064115232', 'Maira Alejandra perez', '3016884893', '', '', '', 'O positivo', 'Infeccion en el hueso\\r\\nMedicamento levetiracetan de 500mg', 'Cesar ', NULL, 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'Sí', ''),
(7, 'Ramon alexis', 'Castellanos contreras', '3232437193', 'Masculino', '1991-11-06', 'ramonalexis.castellanoscontreras@consultorioemilybernal.com', '$2y$10$jp/EE8aH3SLVENEEp1N2q./4.4d.E752rY1lMb2kBhEMVU6J1Dv3u', NULL, 'perfil', 'Nueva eps', 'Empleado grafica marvel', 'soltero', '1127353094', 'María Angélica castellanos contreras', '3204847046', '', '', '', 'O positvo', 'ASAI', 'Villa luz barbosa', NULL, 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'No', ''),
(8, 'Stefany', 'Segura ariza', '3217478588', 'Femenino', '1996-10-18', 'stefany.seguraariza@consultorioemilybernal.com', '$2y$10$Kx70tP2HscB4KddDFLub9OgfMDvFkEgCf/qQpPjEc21/qYy1Bibf2', NULL, 'perfil', 'Nueva eps', 'Empleado panadería gusto', 'soltera', '1099215015', 'Cecilia ariza', '3115156404', '', '', '', 'O positivo', 'ASA I', 'José Antonio galan', NULL, 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'No', ''),
(9, 'Carlos danilo', 'Casteblanco carrasco', '3104843947', 'Masculino', '1974-12-16', 'carlosdanilo.casteblancocarrasco@consultorioemilybernal.com', '$2y$10$1GCKO7PiyXzKaC0Wtgzfg.HRxNJxEX.c8JjnQsdAkExtpcr/fx8f.', NULL, 'perfil', 'No aplica', 'independiente', 'soltero', '13956960', 'Adriana amado gonzalez', '3142385032', '', '', '', 'O positivo', 'ASAI', 'Vereda el limón velez', NULL, 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'No', 'ninguno');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paciente_diagnostico`
--

CREATE TABLE `paciente_diagnostico` (
  `id_diagnostico` int(11) NOT NULL,
  `id_cita` int(11) NOT NULL,
  `diagnostico` text NOT NULL,
  `descripcion` text NOT NULL,
  `medicina` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Volcado de datos para la tabla `paciente_diagnostico`
--

INSERT INTO `paciente_diagnostico` (`id_diagnostico`, `id_cita`, `diagnostico`, `descripcion`, `medicina`) VALUES
(1, 1, 'Se realiza prueba de coronas 12-26', 'Agregado corona 12', ''),
(2, 4, 'Se realizo revisión de cicatrizacion,y se volvio a provisionalizar', 'Falta de cicatrizacion cita en 8 días para toma de impresión definitiva', 'K trix enjuague de cicatrizacion'),
(3, 5, 'Caries oclusal diente 36\r\nCaries vestibular diente 47', 'Se realiza resina oclusal,recubrimiento pulpar diente 36\r\nResina  vestibular diente 47', 'Naproxeno capsulas de 500 MG'),
(4, 6, 'Desgaste severo en 11,12,21,22', 'Valoración y plan de tratamiento, se realiza preparación para coronas anteriores', ''),
(5, 7, 'Caries cervical diente 13\r\nAmalgama desadaptada diente  36', 'Se retira la caries, se hace protocolo de resina se aplica resina a3 diente 13\r\nDiente 36 se retira la amalgama , se aplica protector pulpar se hace protocolo de adhesión y se aplica resina a3.5 dentina y a 1 esmalte tétrico cerma', ''),
(6, 8, 'Pulpitis irreversible sintomática diente 36', 'Se realiza anestesia lidocaína 2% y se realiza pulpectamia', 'Naproxeno tabletas de 500mg'),
(7, 2, 'Cementacion de coronas 12,26', 'Se entregan las coronas 12-26 se dan recomendaciones', ''),
(8, 9, 'Caries o diente 17-27', 'Resina en diente 17-27', ''),
(9, 3, 'Toma de impresión para removible', 'Toma de impresión para removible', ''),
(10, 10, 'Resina desadaptada 47 y amalgama desadaptada 48', 'Resina en 47-48', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unavailable_dates`
--

CREATE TABLE `unavailable_dates` (
  `id_unavailable` int(11) NOT NULL,
  `id_doctor` int(11) NOT NULL,
  `unavailable_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_cita`),
  ADD KEY `consultas-pacientes` (`id_consultas`),
  ADD KEY `doctor-cita` (`id_doctor`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `idx_citas_doctor_fecha_hora` (`id_doctor`,`fecha_cita`,`hora_cita`);

--
-- Indices de la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD PRIMARY KEY (`id_consultas`);

--
-- Indices de la tabla `doctor`
--
ALTER TABLE `doctor`
  ADD PRIMARY KEY (`id_doctor`),
  ADD KEY `id_especialidad` (`id_especialidad`);

--
-- Indices de la tabla `especialidad`
--
ALTER TABLE `especialidad`
  ADD PRIMARY KEY (`id_especialidad`);

--
-- Indices de la tabla `informe_medico`
--
ALTER TABLE `informe_medico`
  ADD PRIMARY KEY (`id_informe`),
  ADD KEY `id_cita` (`id_cita`),
  ADD KEY `id_paciente` (`id_paciente`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `id_doctor` (`id_doctor`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id_paciente`);

--
-- Indices de la tabla `paciente_diagnostico`
--
ALTER TABLE `paciente_diagnostico`
  ADD PRIMARY KEY (`id_diagnostico`),
  ADD KEY `id_cita_fk` (`id_cita`);

--
-- Indices de la tabla `unavailable_dates`
--
ALTER TABLE `unavailable_dates`
  ADD PRIMARY KEY (`id_unavailable`),
  ADD UNIQUE KEY `id_doctor` (`id_doctor`,`unavailable_date`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `consultas`
--
ALTER TABLE `consultas`
  MODIFY `id_consultas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `doctor`
--
ALTER TABLE `doctor`
  MODIFY `id_doctor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `informe_medico`
--
ALTER TABLE `informe_medico`
  MODIFY `id_informe` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id_paciente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `paciente_diagnostico`
--
ALTER TABLE `paciente_diagnostico`
  MODIFY `id_diagnostico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `unavailable_dates`
--
ALTER TABLE `unavailable_dates`
  MODIFY `id_unavailable` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `consultas-pacientes` FOREIGN KEY (`id_consultas`) REFERENCES `consultas` (`id_consultas`),
  ADD CONSTRAINT `doctor-cita` FOREIGN KEY (`id_doctor`) REFERENCES `doctor` (`id_doctor`),
  ADD CONSTRAINT `id_paciente` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`);

--
-- Filtros para la tabla `doctor`
--
ALTER TABLE `doctor`
  ADD CONSTRAINT `id_especialidad` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidad` (`id_especialidad`);

--
-- Filtros para la tabla `informe_medico`
--
ALTER TABLE `informe_medico`
  ADD CONSTRAINT `informe_medico_ibfk_1` FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`),
  ADD CONSTRAINT `informe_medico_ibfk_2` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_doctor`) REFERENCES `doctor` (`id_doctor`);

--
-- Filtros para la tabla `paciente_diagnostico`
--
ALTER TABLE `paciente_diagnostico`
  ADD CONSTRAINT `id_cita_fk` FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`);

--
-- Filtros para la tabla `unavailable_dates`
--
ALTER TABLE `unavailable_dates`
  ADD CONSTRAINT `unavailable_dates_ibfk_1` FOREIGN KEY (`id_doctor`) REFERENCES `doctor` (`id_doctor`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
