-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-03-2026 a las 11:01:04
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
-- Base de datos: `bistrofdi`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT 'default_cat.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `imagen`) VALUES
(1, 'Entrantes', 'Para abrir el apetito con opciones frías y calientes.', 'default_cat.png'),
(2, 'Platos Principales', 'Carnes, pescados y opciones contundentes.', 'default_cat.png'),
(3, 'Postres', 'El toque dulce final.', 'default_cat.png'),
(4, 'Bebidas', 'Refrescos, agua, cerveza y otras bebidas.', 'default_cat.png'),
(5, 'Cafés e Infusiones', 'Cafés, tés e infusiones para acompañar.', 'default_cat.png'),
(6, 'Ensaladas y Bowls', 'Opciones frescas y saludables para cuidarse.', 'ensaladas.png'),
(7, 'Para Compartir', 'Raciones pensadas para disfrutar en grupo.', 'compartir.png'),
(8, 'Especialidades del Chef', 'Nuestros platos más exclusivos y elaborados.', 'especialidades.png'),
(9, 'Vinos y Cavas', 'Nuestra selección de tintos, blancos y espumosos.', 'vinos.png'),
(10, 'Opciones Veganas', 'Platos 100% vegetales llenos de sabor.', 'vegan.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `cliente_id`, `tipo`, `estado`, `fecha`, `total`) VALUES
(1, 5, 'local', 'entregado', '2026-03-01 14:10:00', 21.40),
(2, 6, 'llevar', 'terminado', '2026-03-01 14:25:00', 14.10),
(3, 7, 'local', 'cocinando', '2026-03-01 14:35:00', 19.00),
(4, 8, 'llevar', 'recibido', '2026-03-01 14:45:00', 8.30),
(5, 5, 'local', 'en_preparacion', '2026-03-02 13:05:00', 17.70),
(6, 6, 'llevar', 'cancelado', '2026-03-02 13:15:00', 11.50),
(7, 7, 'local', 'listo_cocina', '2026-03-02 13:25:00', 7.60),
(8, 8, 'llevar', 'entregado', '2026-03-02 13:40:00', 24.20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_productos`
--

CREATE TABLE `pedido_productos` (
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_productos`
--

INSERT INTO `pedido_productos` (`pedido_id`, `producto_id`, `cantidad`) VALUES
(1, 4, 1),
(1, 9, 1),
(1, 13, 1),
(2, 8, 1),
(2, 12, 1),
(3, 6, 1),
(3, 10, 1),
(3, 15, 1),
(4, 2, 1),
(4, 14, 1),
(5, 1, 1),
(5, 4, 1),
(5, 16, 1),
(6, 4, 1),
(7, 9, 1),
(7, 17, 1),
(8, 5, 1),
(8, 10, 1),
(8, 13, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `imagen` text DEFAULT NULL,
  `iva` int(11) NOT NULL DEFAULT 10,
  `ofertado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `id_categoria`, `nombre`, `descripcion`, `precio_base`, `stock`, `imagen`, `iva`, `ofertado`) VALUES
(1, 1, 'Croquetas de jamón', 'Croquetas caseras cremosas de jamón ibérico.', 7.50, 25, 'croquetas1.jpg,croquetas2.jpg', 10, 1),
(2, 1, 'Patatas bravas', 'Patatas crujientes con salsa brava y alioli.', 6.20, 30, 'bravas1.jpg', 10, 1),
(3, 1, 'Calamares a la romana', 'Ración de calamares fritos con limón.', 9.80, 18, 'calamares1.jpg,calamares2.jpg', 10, 1),
(4, 2, 'Hamburguesa Bistro', 'Hamburguesa completa con queso, bacon y patatas.', 11.50, 20, 'hamburguesa1.jpg,hamburguesa2.jpg', 10, 1),
(5, 2, 'Solomillo al punto', 'Solomillo de ternera con guarnición.', 18.90, 8, 'solomillo1.jpg', 10, 1),
(6, 2, 'Salmón a la plancha', 'Lomo de salmón con verduras salteadas.', 15.40, 12, 'salmon1.jpg', 10, 1),
(7, 2, 'Paella de marisco', 'Arroz con marisco para compartir.', 16.75, 6, 'paella1.jpg,paella2.jpg', 10, 1),
(8, 2, 'Menú del día', 'Primer plato, segundo, bebida y postre.', 12.00, 40, 'menu1.jpg', 10, 1),
(9, 3, 'Tarta de queso', 'Tarta de queso cremosa con base de galleta.', 5.20, 15, 'tartaqueso1.jpg', 10, 1),
(10, 3, 'Brownie con helado', 'Brownie templado con bola de vainilla.', 5.80, 10, 'brownie1.jpg', 10, 1),
(11, 3, 'Fruta de temporada', 'Selección de fruta fresca.', 4.10, 20, 'fruta1.jpg', 4, 1),
(12, 4, 'Agua mineral', 'Botella de agua mineral 50 cl.', 1.20, 100, 'agua1.jpg', 10, 1),
(13, 4, 'Refresco cola', 'Lata de refresco de cola.', 2.10, 80, 'cola1.jpg', 21, 1),
(14, 4, 'Cerveza', 'Caña de cerveza bien fría.', 2.40, 60, 'cerveza1.jpg', 21, 1),
(15, 4, 'Zumo natural', 'Zumo de naranja recién exprimido.', 3.20, 25, 'zumo1.jpg', 10, 1),
(16, 5, 'Café espresso', 'Café solo intenso.', 1.30, 100, 'cafe1.jpg', 10, 1),
(17, 5, 'Café con leche', 'Café con leche entera o desnatada.', 1.60, 90, 'cafeleche1.jpg', 10, 1),
(18, 5, 'Infusión', 'Manzanilla, té verde o té negro.', 1.50, 50, 'infusion1.jpg', 10, 1),
(19, 2, 'Entrecot premium', 'Producto retirado temporalmente de la carta.', 22.00, 0, 'entrecot1.jpg', 10, 0),
(20, 3, 'Helado artesano', 'Producto actualmente no disponible.', 4.50, 0, 'helado1.jpg', 10, 0);

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
(1, 'admin', 'admin@bistrofdi.es', 'Administrador', 'Bistro FDI', '$2y$10$Bvjd6rMK3Cw5mHhm1xIloOX4PW7pIcZT5d5eJWmmEFL.Epf7PI2rm', 'gerente', 'img/avatares/default.jpg', '2026-03-12 00:13:43'),
(2, 'sergio', 'sergio@bistrofdi.es', 'Sergio', 'Celma', '$2y$10$Bvjd6rMK3Cw5mHhm1xIloOX4PW7pIcZT5d5eJWmmEFL.Epf7PI2rm', 'camarero', 'img/avatares/avatar1.jpg', '2026-03-12 00:13:43'),
(3, 'cocina1', 'cocina1@bistrofdi.es', 'Lucía', 'Martín', '$2y$10$Bvjd6rMK3Cw5mHhm1xIloOX4PW7pIcZT5d5eJWmmEFL.Epf7PI2rm', 'cocinero', 'img/avatares/avatar2.jpg', '2026-03-12 00:13:43'),
(4, 'cocina2', 'cocina2@bistrofdi.es', 'David', 'Ruiz', '$2y$10$Bvjd6rMK3Cw5mHhm1xIloOX4PW7pIcZT5d5eJWmmEFL.Epf7PI2rm', 'cocinero', 'img/avatares/avatar3.jpg', '2026-03-12 00:13:43'),
(5, 'cliente1', 'ana@correo.es', 'Ana', 'López', '$2y$10$Bvjd6rMK3Cw5mHhm1xIloOX4PW7pIcZT5d5eJWmmEFL.Epf7PI2rm', 'cliente', 'img/avatares/default.jpg', '2026-03-12 00:13:43'),
(6, 'cliente2', 'carlos@correo.es', 'Carlos', 'Santos', '$2y$10$Bvjd6rMK3Cw5mHhm1xIloOX4PW7pIcZT5d5eJWmmEFL.Epf7PI2rm', 'cliente', 'img/avatares/default.jpg', '2026-03-12 00:13:43'),
(7, 'cliente3', 'maria@correo.es', 'María', 'García', '$2y$10$Bvjd6rMK3Cw5mHhm1xIloOX4PW7pIcZT5d5eJWmmEFL.Epf7PI2rm', 'cliente', 'img/avatares/default.jpg', '2026-03-12 00:13:43'),
(8, 'cliente4', 'jorge@correo.es', 'Jorge', 'Pérez', '$2y$10$Bvjd6rMK3Cw5mHhm1xIloOX4PW7pIcZT5d5eJWmmEFL.Epf7PI2rm', 'cliente', 'img/avatares/default.jpg', '2026-03-12 00:13:43'),
(9, 'Chicote', 'chicote@la6.com', 'Alberto', 'Chic', '$2y$10$Bvjd6rMK3Cw5mHhm1xIloOX4PW7pIcZT5d5eJWmmEFL.Epf7PI2rm', 'cliente', 'img/avatares/default.png', '2026-03-12 00:36:15');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_categorias_nombre` (`nombre`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pedidos_cliente` (`cliente_id`);

--
-- Indices de la tabla `pedido_productos`
--
ALTER TABLE `pedido_productos`
  ADD PRIMARY KEY (`pedido_id`,`producto_id`),
  ADD KEY `fk_pedido_productos_producto` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_productos_categoria` (`id_categoria`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuarios_nombre_usuario` (`nombre_usuario`),
  ADD UNIQUE KEY `uq_usuarios_email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedido_productos`
--
ALTER TABLE `pedido_productos`
  ADD CONSTRAINT `fk_pedido_productos_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedido_productos_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
