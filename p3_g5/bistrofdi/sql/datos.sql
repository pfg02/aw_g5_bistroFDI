USE bistrofdi;

SET NAMES utf8mb4;

-- --------------------------------------------------------
-- POBLAR USUARIOS
-- Contraseña para todos: 1234
-- --------------------------------------------------------

INSERT INTO usuarios (id, nombre, apellidos, nombre_usuario, email, password, rol, activo) VALUES
    (1, 'Admin', 'Gerente', 'gerente', 'gerente@bistrofdi.es', '$2y$12$TPGKHCM4PRpAv5hyo3l9we8JFZft/RpMuuxLAfm38ZrnlkuqyTryW', 'gerente', 1),
    (2, 'Carlos', 'Sala', 'camarero', 'camarero@bistrofdi.es', '$2y$12$TPGKHCM4PRpAv5hyo3l9we8JFZft/RpMuuxLAfm38ZrnlkuqyTryW', 'camarero', 1),
    (3, 'Lucía', 'Cocina', 'cocinero', 'cocinero@bistrofdi.es', '$2y$12$TPGKHCM4PRpAv5hyo3l9we8JFZft/RpMuuxLAfm38ZrnlkuqyTryW', 'cocinero', 1),
    (4, 'Pedro', 'Cliente', 'cliente1', 'cliente1@bistrofdi.es', '$2y$12$TPGKHCM4PRpAv5hyo3l9we8JFZft/RpMuuxLAfm38ZrnlkuqyTryW', 'cliente', 1),
    (5, 'María', 'Cliente', 'cliente2', 'cliente2@bistrofdi.es', '$2y$12$TPGKHCM4PRpAv5hyo3l9we8JFZft/RpMuuxLAfm38ZrnlkuqyTryW', 'cliente', 1),
    (6, 'Javier', 'Cliente', 'cliente3', 'cliente3@bistrofdi.es', '$2y$12$TPGKHCM4PRpAv5hyo3l9we8JFZft/RpMuuxLAfm38ZrnlkuqyTryW', 'cliente', 1);

-- --------------------------------------------------------
-- POBLAR CATEGORIAS
-- --------------------------------------------------------

INSERT INTO categorias (id, nombre, descripcion, imagen, activa) VALUES
    (1, 'Entrantes', 'Platos para compartir antes del principal', 'entrantes.png', 1),
    (2, 'Principales', 'Platos principales del restaurante', 'principales.png', 1),
    (3, 'Postres', 'Postres caseros y dulces', 'postres.png', 1),
    (4, 'Bebidas', 'Refrescos, agua y bebidas', 'bebidas.png', 1),
    (5, 'Cafés', 'Cafés e infusiones', 'cafes.png', 1);

-- --------------------------------------------------------
-- POBLAR PRODUCTOS
-- --------------------------------------------------------

INSERT INTO productos (id, id_categoria, nombre, descripcion, precio_base, stock, imagen, iva, ofertado, requiere_cocina, disponible) VALUES
    (1, 1, 'Nachos con Queso', 'Nachos crujientes con mezcla de quesos, jalapeños y guacamole', 8.50, 50, 'nachos.png', 10, 1, 1, 1),
    (2, 1, 'Croquetas Caseras', 'Ración de croquetas cremosas de jamón ibérico (6 uds)', 9.00, 40, 'croquetas.png', 10, 1, 1, 1),
    (3, 1, 'Patatas Bravas', 'Patatas rústicas con nuestra salsa brava secreta', 6.50, 60, 'bravas.png', 10, 1, 1, 1),

    (4, 2, 'Hamburguesa FDI', 'Doble carne de ternera, queso cheddar, bacon y salsa BBQ', 12.50, 30, 'burger.png', 10, 1, 1, 1),
    (5, 2, 'Risotto de Setas', 'Risotto cremoso con setas de temporada y parmesano', 14.00, 25, 'risotto.png', 10, 0, 1, 1),
    (6, 2, 'Salmón a la Plancha', 'Lomo de salmón fresco con guarnición de verduritas', 16.00, 20, 'salmon.png', 10, 0, 1, 1),

    (7, 3, 'Tarta de Queso', 'Tarta de queso cremosa con coulis de frutos rojos', 5.50, 35, 'tarta_queso.png', 10, 1, 0, 1),
    (8, 3, 'Brownie con Helado', 'Brownie de chocolate con bola de helado de vainilla', 6.00, 30, 'brownie.png', 10, 0, 0, 1),

    (9, 4, 'Agua Mineral', 'Botella de agua mineral 50cl', 2.00, 100, 'agua.png', 10, 0, 0, 1),
    (10, 4, 'Refresco', 'Refresco frío 33cl', 2.50, 100, 'refresco.png', 10, 0, 0, 1),

    (11, 5, 'Café Solo', 'Café espresso recién hecho', 1.60, 100, 'cafe_solo.png', 10, 0, 0, 1),
    (12, 5, 'Café con Leche', 'Café con leche caliente', 1.90, 100, 'cafe_leche.png', 10, 0, 0, 1);

-- --------------------------------------------------------
-- POBLAR OFERTAS
-- --------------------------------------------------------

INSERT INTO ofertas (id, nombre, descripcion, descuento_porcentaje, fecha_inicio, fecha_fin, activa) VALUES
    (1, 'Menú Burger', 'Hamburguesa FDI con refresco y postre', 10.00, '2026-01-01', '2026-12-31', 1),
    (2, 'Picoteo FDI', 'Entrantes para compartir con descuento', 15.00, '2026-01-01', '2026-12-31', 1),
    (3, 'Café y Postre', 'Oferta de café con postre', 12.00, '2026-01-01', '2026-12-31', 1);

-- --------------------------------------------------------
-- POBLAR OFERTAS_PRODUCTOS
-- --------------------------------------------------------

INSERT INTO ofertas_productos (oferta_id, producto_id, cantidad) VALUES
    (1, 4, 1),
    (1, 10, 1),
    (1, 7, 1),

    (2, 1, 1),
    (2, 2, 1),
    (2, 3, 1),

    (3, 7, 1),
    (3, 11, 1);

-- --------------------------------------------------------
-- POBLAR PEDIDOS
-- numero_pedido se puede repetir en días distintos.
-- servido_sala = 1 significa que sala ya lo ha servido.
-- --------------------------------------------------------

INSERT INTO pedidos (
    id,
    cliente_id,
    cocinero_id,
    camarero_id,
    numero_pedido,
    tipo,
    estado,
    servido_sala,
    fecha,
    total,
    total_sin_descuento,
    descuento_total
) VALUES
    (1, 4, 3, 2, 1, 'Local', 'Entregado', 1, '2026-03-01 13:30:00', 36.85, 36.85, 0.00),
    (2, 5, 3, 2, 2, 'Llevar', 'Terminado', 1, '2026-03-01 14:15:00', 21.34, 21.34, 0.00),
    (3, 6, NULL, NULL, 3, 'Local', 'Cancelado', 0, '2026-03-01 14:30:00', 0.00, 0.00, 0.00),

    (4, 4, 3, NULL, 1, 'Local', 'Listo cocina', 0, '2026-03-02 13:45:00', 44.55, 44.55, 0.00),
    (5, 5, 3, NULL, 2, 'Llevar', 'Cocinando', 0, '2026-03-02 14:00:00', 18.70, 18.70, 0.00),
    (6, 6, NULL, NULL, 3, 'Local', 'En preparación', 0, '2026-03-02 14:20:00', 31.35, 31.35, 0.00),
    (7, 4, NULL, NULL, 4, 'Llevar', 'Recibido', 0, '2026-03-02 14:40:00', 13.75, 13.75, 0.00),
    (8, 5, NULL, NULL, 5, 'Local', 'Nuevo', 0, '2026-03-02 15:00:00', 0.00, 0.00, 0.00);

-- --------------------------------------------------------
-- POBLAR PEDIDOS_PRODUCTOS
-- --------------------------------------------------------

INSERT INTO pedidos_productos (pedido_id, producto_id, cantidad, precio_unitario, subtotal) VALUES
    -- Pedido 1
    (1, 1, 1, 8.50, 8.50),
    (1, 4, 2, 12.50, 25.00),
    (1, 11, 2, 1.60, 3.20),

    -- Pedido 2
    (2, 2, 1, 9.00, 9.00),
    (2, 7, 1, 5.50, 5.50),
    (2, 10, 2, 2.50, 5.00),

    -- Pedido 4
    (4, 4, 2, 12.50, 25.00),
    (4, 6, 1, 16.00, 16.00),
    (4, 10, 1, 2.50, 2.50),

    -- Pedido 5
    (5, 3, 1, 6.50, 6.50),
    (5, 8, 1, 6.00, 6.00),
    (5, 9, 2, 2.00, 4.00),

    -- Pedido 6
    (6, 5, 1, 14.00, 14.00),
    (6, 2, 1, 9.00, 9.00),
    (6, 12, 2, 1.90, 3.80),

    -- Pedido 7
    (7, 1, 1, 8.50, 8.50),
    (7, 10, 2, 2.50, 5.00);

-- --------------------------------------------------------
-- POBLAR PEDIDOS_OFERTAS
-- --------------------------------------------------------

INSERT INTO pedidos_ofertas (pedido_id, oferta_id, descuento_aplicado) VALUES
    (1, 1, 2.00),
    (4, 1, 3.50),
    (7, 2, 1.25);