-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-12-2025 a las 03:14:43
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `aprendiendo_chol`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `insignias`
--

CREATE TABLE `insignias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(50) DEFAULT NULL,
  `requisito_tipo` enum('temas_completados','modulo_completo','puntos_totales','racha_dias') NOT NULL,
  `requisito_valor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `insignias`
--

INSERT INTO `insignias` (`id`, `nombre`, `descripcion`, `icono`, `requisito_tipo`, `requisito_valor`) VALUES
(1, 'Explorador', 'Completa tu primer tema', '🔍', 'temas_completados', 1),
(2, 'Amigo', 'Completa 5 temas', '🤝', 'temas_completados', 5),
(3, '¡Ch\'ol!', 'Completa todos los módulos', '🏆', 'modulo_completo', 5),
(4, 'Dedicado', 'Alcanza 50 puntos totales', '⭐', 'puntos_totales', 50),
(5, 'Maestro', 'Completa un módulo completo', '📚', 'modulo_completo', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `insignias_usuario`
--

CREATE TABLE `insignias_usuario` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `insignia_id` int(11) NOT NULL,
  `fecha_obtencion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulos`
--

CREATE TABLE `modulos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `orden` int(11) NOT NULL,
  `total_temas` int(11) NOT NULL,
  `puntos_maximos` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `modulos`
--

INSERT INTO `modulos` (`id`, `nombre`, `descripcion`, `orden`, `total_temas`, `puntos_maximos`) VALUES
(1, 'Familia', 'Aprende sobre saludos, presentación y familia', 1, 3, 18.75),
(2, 'Donde Vivo', 'Descubre lugares, adjetivos, naturaleza y animales', 2, 4, 25.00),
(3, 'Mi Escuela', 'Explora profesiones, objetos y verbos', 3, 3, 18.75),
(4, 'Mis Gustos y Pasatiempos', 'Conoce gustos, pasatiempos y tu mundo', 4, 3, 18.75),
(5, 'Mi Cuerpo', 'Aprende sobre el cuerpo, enfermedades y vestimenta', 5, 3, 18.75);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `progreso_usuario`
--

CREATE TABLE `progreso_usuario` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tema_id` int(11) NOT NULL,
  `completado` tinyint(1) DEFAULT 0,
  `puntos_obtenidos` decimal(5,2) DEFAULT 0.00,
  `intentos` int(11) DEFAULT 0,
  `fecha_completado` timestamp NULL DEFAULT NULL,
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temas`
--

CREATE TABLE `temas` (
  `id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `orden` int(11) NOT NULL,
  `puntos_maximos` decimal(5,2) DEFAULT 6.25
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `temas`
--

INSERT INTO `temas` (`id`, `modulo_id`, `nombre`, `orden`, `puntos_maximos`) VALUES
(1, 1, 'Saludos', 1, 6.25),
(2, 1, 'Presentación', 2, 6.25),
(3, 1, 'Familia', 3, 6.25),
(4, 2, 'Mi Casa', 1, 6.25),
(5, 2, 'Adjetivos', 2, 6.25),
(6, 2, 'Naturaleza', 3, 6.25),
(7, 2, 'Animales', 4, 6.25),
(8, 3, 'Profesiones y Oficios', 1, 6.25),
(9, 3, 'Lugares y Objetos', 2, 6.25),
(10, 3, 'Verbos', 3, 6.25),
(11, 4, 'Gustos', 1, 6.25),
(12, 4, 'Pasatiempos', 2, 6.25),
(13, 4, 'Mi Mundo', 3, 6.25),
(14, 5, 'Mi Cuerpo', 1, 6.25),
(15, 5, 'Enfermedades', 2, 6.25),
(16, 5, 'Vestimenta', 3, 6.25);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `google_id` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `nombre_completo` varchar(255) DEFAULT NULL,
  `alias` varchar(50) DEFAULT 'Aventurero',
  `avatar` varchar(10) DEFAULT '?',
  `rango_edad` enum('5-7','8-10','11-13','14+') DEFAULT '8-10',
  `foto_perfil_url` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_conexion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `insignias`
--
ALTER TABLE `insignias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `insignias_usuario`
--
ALTER TABLE `insignias_usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_insignia` (`usuario_id`,`insignia_id`),
  ADD KEY `insignia_id` (`insignia_id`);

--
-- Indices de la tabla `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orden` (`orden`);

--
-- Indices de la tabla `progreso_usuario`
--
ALTER TABLE `progreso_usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_tema` (`usuario_id`,`tema_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_tema` (`tema_id`);

--
-- Indices de la tabla `temas`
--
ALTER TABLE `temas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_modulo_orden` (`modulo_id`,`orden`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `idx_google_id` (`google_id`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `insignias`
--
ALTER TABLE `insignias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `insignias_usuario`
--
ALTER TABLE `insignias_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `progreso_usuario`
--
ALTER TABLE `progreso_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `temas`
--
ALTER TABLE `temas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `insignias_usuario`
--
ALTER TABLE `insignias_usuario`
  ADD CONSTRAINT `insignias_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `insignias_usuario_ibfk_2` FOREIGN KEY (`insignia_id`) REFERENCES `insignias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `progreso_usuario`
--
ALTER TABLE `progreso_usuario`
  ADD CONSTRAINT `progreso_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progreso_usuario_ibfk_2` FOREIGN KEY (`tema_id`) REFERENCES `temas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `temas`
--
ALTER TABLE `temas`
  ADD CONSTRAINT `temas_ibfk_1` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
