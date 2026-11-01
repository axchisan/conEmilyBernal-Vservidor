-- Esquema sin datos (los datos de prueba se retiraron del repositorio).
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
