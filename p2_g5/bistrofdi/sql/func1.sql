-- phpMyAdmin SQL Dump actualizado para Múltiples Fotos
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `categorias`
-- --------------------------------------------------------

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` TEXT DEFAULT NULL  -- Cambiado a TEXT para soportar múltiples fotos
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcado de datos inicial
INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `imagen`) VALUES
(1, 'Entrantes', 'Para abrir el apetito', 'default_cat.png'),
(2, 'Platos Principales', 'Nuestras mejores carnes y pescados', 'default_cat.png'),
(3, 'Postres', 'El toque dulce final', 'default_cat.png');

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `productos`
-- --------------------------------------------------------

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `imagen` varchar(255) DEFAULT 'default_prod.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `usuarios`
-- --------------------------------------------------------

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre_completo` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `rol` enum('admin','gerente','empleado','cliente') DEFAULT 'cliente',
  `avatar` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar usuario admin por defecto (pass: admin123)
INSERT INTO `usuarios` (`username`, `password`, `nombre_completo`, `rol`) VALUES
('admin', '$2y$10$8K.pUshU6L6L4n.gI9lBbeYvH8z6R7oGzW0zW9zW9zW9zW9zW9zW9', 'Admin Gerente', 'gerente');

-- Índices y AUTO_INCREMENT
ALTER TABLE `categorias` ADD PRIMARY KEY (`id`);
ALTER TABLE `productos` ADD PRIMARY KEY (`id`), ADD KEY `id_categoria` (`id_categoria`);
ALTER TABLE `usuarios` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `username` (`username`), ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `categorias` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `productos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `usuarios` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- Restricciones
ALTER TABLE `productos` ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;

COMMIT;