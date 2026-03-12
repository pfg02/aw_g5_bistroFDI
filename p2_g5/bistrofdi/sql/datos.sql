
-- --------------------------------------------------------
-- POBLAR CATEGORÍAS
-- --------------------------------------------------------
INSERT INTO categorias (nombre, descripcion, imagen) VALUES
	('Entrantes', 'Para compartir y abrir el apetito', 'entrantes.png'),
	('Platos Principales', 'Las especialidades de la casa', 'principales.png'),
	('Postres', 'El toque dulce para terminar', 'postres.png'),
	('Bebidas', 'Refrescos, cervezas y aguas', 'bebidas.png'),
	('Cafés e Infusiones', 'Recién molido', 'cafes.png');

-- --------------------------------------------------------
-- POBLAR PRODUCTOS
-- --------------------------------------------------------
INSERT INTO productos (id_categoria, nombre, descripcion, precio_base, stock, imagen, iva, ofertado) VALUES
	(1, 'Nachos con Queso', 'Nachos crujientes con mezcla de quesos, jalapeños y guacamole', 8.50, 50, 'nachos.png', 10, 1),
	(1, 'Croquetas Caseras', 'Ración de croquetas cremosas de jamón ibérico (6 uds)', 9.00, 40, 'croquetas.png', 10, 1),
	(1, 'Patatas Bravas', 'Patatas rústicas con nuestra salsa brava secreta', 6.50, 60, 'bravas.png', 10, 1),
	(2, 'Hamburguesa FDI', 'Doble carne de ternera, queso cheddar, bacon y salsa BBQ', 12.50, 30, 'burger.png', 10, 1),
	(2, 'Risotto de Setas', 'Risotto cremoso con setas de temporada y parmesano', 14.00, 25, 'risotto.png', 10, 1),
	(2, 'Salmón a la Plancha', 'Lomo de salmón fresco con guarnición de verduritas', 16.00, 20, 'salmon.png', 10, 1),
	(3, 'Tarta de Queso', 'Nuestra famosa tarta de queso horneada', 6.50, 15, 'tarta_queso.png', 10, 1),
	(3, 'Brownie con Helado', 'Brownie de chocolate caliente con helado de vainilla', 6.00, 20, 'brownie.png', 10, 1),
	(4, 'Refresco de Cola', 'Lata 33cl bien fría', 2.50, 100, 'cola.png', 10, 1),
	(4, 'Cerveza Artesanal', 'Pinta de cerveza rubia de barril', 3.50, 80, 'cerveza.png', 21, 1),
	(4, 'Agua Mineral', 'Botella 50cl', 1.50, 150, 'agua.png', 10, 1),
	(5, 'Café Solo', 'Café espresso de especialidad', 1.50, 200, 'cafe_solo.png', 10, 1),
	(5, 'Café con Leche', 'Café espresso con leche espumada', 1.80, 200, 'cafe_leche.png', 10, 1);

-- --------------------------------------------------------
-- POBLAR USUARIOS
-- Contraseña para TODOS: 123456
-- --------------------------------------------------------
INSERT INTO usuarios (nombre_usuario, email, nombre, apellidos, password_hash, rol, avatar) VALUES
	('admin', 'gerente@bistrofdi.es', 'Carlos', 'Gerente', '$2y$10$dQIYU$2y$10$YT8tBN84e4OP6RMEjbehcO9TKTGSm.rzOo1jFr1nkdLfSQEDbE2cCskupVUKinCqeaDPWuI7W2oEUausA02XYxO3J5LjDSWnNm52C', 'gerente', 'gerente.png'),
	('chef_chicote', 'chicote@bistrofdi.es', 'Alberto', 'Chicote', '$2y$10$dQIYU$2y$10$YT8tBN84e4OP6RMEjbehcO9TKTGSm.rzOo1jFr1nkdLfSQEDbE2cCskupVUKinCqeaDPWuI7W2oEUausA02XYxO3J5LjDSWnNm52C', 'cocinero', 'cocinero.png'),
	('camarero_juan', 'juan@bistrofdi.es', 'Juan', 'Pérez', '$2y$10$dQIYU$2y$10$YT8tBN84e4OP6RMEjbehcO9TKTGSm.rzOo1jFr1nkdLfSQEDbE2cCskupVUKinCqeaDPWuI7W2oEUausA02XYxO3J5LjDSWnNm52C', 'camarero', 'camarero.png'),
	('ana_cliente', 'ana@gmail.com', 'Ana', 'Martínez', '$2y$10$dQIYU$2y$10$YT8tBN84e4OP6RMEjbehcO9TKTGSm.rzOo1jFr1nkdLfSQEDbE2cCskupVUKinCqeaDPWuI7W2oEUausA02XYxO3J5LjDSWnNm52C', 'cliente', 'cliente.png'),
	('luis_cliente', 'luis@hotmail.com', 'Luis', 'Fernández', '$2y$10$dQIYU$2y$10$YT8tBN84e4OP6RMEjbehcO9TKTGSm.rzOo1jFr1nkdLfSQEDbE2cCskupVUKinCqeaDPWuI7W2oEUausA02XYxO3J5LjDSWnNm52C', 'cliente', 'default.png'),
	('maria_cliente', 'maria@yahoo.es', 'María', 'Sánchez', '$2y$10$dQIYU$2y$10$YT8tBN84e4OP6RMEjbehcO9TKTGSm.rzOo1jFr1nkdLfSQEDbE2cCskupVUKinCqeaDPWuI7W2oEUausA02XYxO3J5LjDSWnNm52C', 'cliente', 'default.png');

-- --------------------------------------------------------
-- POBLAR PEDIDOS
-- --------------------------------------------------------
INSERT INTO pedidos (cliente_id, numero_pedido, tipo, estado, fecha, total) VALUES
	(4, 1, 'Local', 'Entregado', '2026-03-01 13:30:00', 36.85),
	(5, 2, 'Llevar', 'Terminado', '2026-03-01 14:15:00', 21.34),
	(6, 3, 'Local', 'Cancelado', '2026-03-01 14:30:00', 0.00),
	(4, 1, 'Local', 'Listo cocina', '2026-03-02 13:45:00', 44.55),
	(5, 2, 'Llevar', 'Cocinando', '2026-03-02 14:00:00', 18.70),
	(6, 3, 'Local', 'En preparación', '2026-03-02 14:20:00', 31.35),
	(4, 4, 'Llevar', 'Recibido', '2026-03-02 14:40:00', 13.75),
	(5, 5, 'Local', 'Nuevo', '2026-03-02 15:00:00', 0.00);

-- --------------------------------------------------------
-- POBLAR LÍNEAS DE PEDIDO
-- --------------------------------------------------------
-- Pedido 1 (Ana)
INSERT INTO pedido_productos (pedido_id, producto_id, cantidad) VALUES
	(1, 1, 1), -- Nachos
	(1, 4, 1), -- Burger
	(1, 9, 2), -- Refrescos
	(1, 7, 1); -- Tarta de Queso

-- Pedido 2 (Luis)
INSERT INTO pedido_productos (pedido_id, producto_id, cantidad) VALUES
	(2, 5, 1), -- Risotto
	(2, 10, 2); -- Cerveza Artesanal

-- Pedido 4 (Ana)
INSERT INTO pedido_productos (pedido_id, producto_id, cantidad) VALUES
	(4, 2, 2), -- Croquetas (x2)
	(4, 6, 1), -- Salmón
	(4, 9, 2); -- Refrescos

-- Pedido 5 (Luis)
INSERT INTO pedido_productos (pedido_id, producto_id, cantidad) VALUES
	(5, 4, 1), -- Burger
	(5, 8, 1); -- Brownie

-- Pedido 6 (Maria)
INSERT INTO pedido_productos (pedido_id, producto_id, cantidad) VALUES
	(6, 1, 1), -- Nachos
	(6, 5, 1); -- Risotto