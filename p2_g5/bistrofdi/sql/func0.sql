-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-03-2026 a las 18:18:19
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bistrofdi`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('cliente','camarero','cocinero','gerente') NOT NULL DEFAULT 'cliente',
  `avatar` varchar(255) DEFAULT 'img/avatares/default.jpg',
  `fecha_alta` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `email`, `nombre`, `apellidos`, `password_hash`, `rol`, `avatar`, `fecha_alta`) VALUES
(1, 'admin', 'admin@bistrofdi.es', 'Admin', 'Bistro FDI', '$2y$10$dQIYUskupVUKinCqeaDPWuI7W2oEUausA02XYxO3J5LjDSWnNm52C', 'gerente', 'img/avatares/default.jpg', '2026-02-25 14:57:54'),
(2, 'sergio', 'scelma@ucm.es', 'Sergio', 'Celma', '$2y$10$BvJjHBKwZdy9xZIld9GSJ.4EcUk/sLPjop3q9nQJmyIl30OGzFRwW', 'camarero', 'img/avatares/avatar1.jpg', '2026-02-25 19:16:36');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
