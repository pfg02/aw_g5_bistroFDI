USE bistrofdi;

SET NAMES utf8mb4;

-- --------------------------------------------------------
-- POBLAR CATEGORIAS
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

-- Bebidas y cafés no pasan por cocina.
UPDATE productos
SET requiere_cocina = 0
WHERE id_categoria IN (4, 5);

-- --------------------------------------------------------
-- POBLAR USUARIOS
-- Contraseña para todos: 123456
-- --------------------------------------------------------

INSERT INTO usuarios (nombre_usuario, email, nombre, apellidos, password_hash, rol, avatar) VALUES
    ('admin', 'gerente@bistrofdi.es', 'Carlos', 'Gerente', '$2y$12$Lc2PDu06QRwCV1.1aRdlSOKfweDE6w6.PZDXLlzSX0RRDta4JSuHm', 'gerente', 'img/avatares/gerente.png'),
    ('chef_chicote', 'chicote@bistrofdi.es', 'Alberto', 'Chicote', '$2y$12$Lc2PDu06QRwCV1.1aRdlSOKfweDE6w6.PZDXLlzSX0RRDta4JSuHm', 'cocinero', 'img/avatares/cocinero.png'),
    ('camarero_juan', 'juan@bistrofdi.es', 'Juan', 'Pérez', '$2y$12$Lc2PDu06QRwCV1.1aRdlSOKfweDE6w6.PZDXLlzSX0RRDta4JSuHm', 'camarero', 'img/avatares/camarero.png'),
    ('ana_cliente', 'ana@gmail.com', 'Ana', 'Martínez', '$2y$12$Lc2PDu06QRwCV1.1aRdlSOKfweDE6w6.PZDXLlzSX0RRDta4JSuHm', 'cliente', 'img/avatares/cliente.png'),
    ('luis_cliente', 'luis@hotmail.com', 'Luis', 'Fernández', '$2y$12$Lc2PDu06QRwCV1.1aRdlSOKfweDE6w6.PZDXLlzSX0RRDta4JSuHm', 'cliente', 'img/avatares/default.png'),
    ('maria_cliente', 'maria@yahoo.es', 'María', 'Sánchez', '$2y$12$Lc2PDu06QRwCV1.1aRdlSOKfweDE6w6.PZDXLlzSX0RRDta4JSuHm', 'cliente', 'img/avatares/default.png');

-- --------------------------------------------------------
-- POBLAR PEDIDOS
-- servido_sala no se inserta porque tiene DEFAULT 0 en estructura.sql.
-- --------------------------------------------------------

INSERT INTO pedidos (
    cliente_id, cocinero_id, numero_pedido, tipo, estado, fecha, total, descuento_total, total_sin_descuento
) VALUES
    (4, 2, 1, 'Local',  'Entregado',        '2026-03-01 13:30:00', 36.85, 0.00, 36.85),
    (5, 2, 2, 'Llevar', 'Terminado',        '2026-03-01 14:15:00', 21.34, 0.00, 21.34),
    (6, NULL, 3, 'Local',  'Cancelado',     '2026-03-01 14:30:00',  0.00, 0.00,  0.00),
    (4, 2, 4, 'Local',  'Listo cocina',     '2026-03-02 13:45:00', 44.55, 0.00, 44.55),
    (5, 2, 5, 'Llevar', 'Cocinando',        '2026-03-02 14:00:00', 18.70, 0.00, 18.70),
    (6, NULL, 6, 'Local',  'En preparación','2026-03-02 14:20:00', 31.35, 0.00, 31.35),
    (4, NULL, 7, 'Llevar', 'Recibido',      '2026-03-02 14:40:00', 13.75, 0.00, 13.75),
    (5, NULL, 8, 'Local',  'Nuevo',         '2026-03-02 15:00:00',  0.00, 0.00,  0.00);

-- Los pedidos ya entregados se consideran servidos en sala.
UPDATE pedidos
SET servido_sala = 1
WHERE estado = 'Entregado';

-- --------------------------------------------------------
-- POBLAR PEDIDO_PRODUCTOS
-- --------------------------------------------------------

INSERT INTO pedido_productos (pedido_id, producto_id, cantidad, preparado) VALUES
    (1, 1, 1, 1),
    (1, 4, 1, 1),
    (1, 9, 2, 1),
    (1, 7, 1, 1),

    (2, 5, 1, 1),
    (2, 10, 2, 1),

    (4, 2, 2, 1),
    (4, 6, 1, 1),
    (4, 9, 2, 1),

    (5, 4, 1, 1),
    (5, 8, 1, 0),

    (6, 1, 1, 0),
    (6, 5, 1, 0);

-- En pedidos activos, las bebidas y cafés deben poder marcarlas sala una a una.
UPDATE pedido_productos pp
INNER JOIN productos p ON pp.producto_id = p.id
INNER JOIN pedidos ped ON pp.pedido_id = ped.id
SET pp.preparado = 0
WHERE p.requiere_cocina = 0
  AND ped.estado NOT IN ('Entregado', 'Terminado', 'Cancelado');