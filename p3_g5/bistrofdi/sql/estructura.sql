USE bistrofdi;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS pedidos_ofertas;
DROP TABLE IF EXISTS ofertas_productos;
DROP TABLE IF EXISTS pedidos_productos;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS ofertas;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS usuarios;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- TABLA USUARIOS
-- --------------------------------------------------------

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) DEFAULT NULL,
    nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('cliente', 'camarero', 'cocinero', 'gerente') NOT NULL DEFAULT 'cliente',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA CATEGORIAS
-- --------------------------------------------------------

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    imagen VARCHAR(255) DEFAULT NULL,
    activa TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA PRODUCTOS
-- IMPORTANTE: coincide con datos.sql
-- --------------------------------------------------------

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    precio_base DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    imagen VARCHAR(255) DEFAULT NULL,
    iva DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    ofertado TINYINT(1) NOT NULL DEFAULT 0,
    requiere_cocina TINYINT(1) NOT NULL DEFAULT 1,
    disponible TINYINT(1) NOT NULL DEFAULT 1,

    CONSTRAINT fk_productos_categoria
        FOREIGN KEY (id_categoria) REFERENCES categorias(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_productos_categoria (id_categoria),
    INDEX idx_productos_disponible (disponible),
    INDEX idx_productos_ofertado (ofertado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA OFERTAS
-- --------------------------------------------------------

CREATE TABLE ofertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    descuento_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    fecha_inicio DATE DEFAULT NULL,
    fecha_fin DATE DEFAULT NULL,
    activa TINYINT(1) NOT NULL DEFAULT 1,

    INDEX idx_ofertas_activa (activa),
    INDEX idx_ofertas_fechas (fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA OFERTAS_PRODUCTOS
-- --------------------------------------------------------

CREATE TABLE ofertas_productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    oferta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,

    CONSTRAINT fk_ofertas_productos_oferta
        FOREIGN KEY (oferta_id) REFERENCES ofertas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_ofertas_productos_producto
        FOREIGN KEY (producto_id) REFERENCES productos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    UNIQUE KEY uk_oferta_producto (oferta_id, producto_id),
    INDEX idx_ofertas_productos_oferta (oferta_id),
    INDEX idx_ofertas_productos_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA PEDIDOS
-- --------------------------------------------------------

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    cocinero_id INT DEFAULT NULL,
    camarero_id INT DEFAULT NULL,

    -- No es UNIQUE porque el número de pedido puede repetirse en días distintos.
    numero_pedido INT NOT NULL,

    tipo ENUM('Local', 'Llevar') NOT NULL,
    estado ENUM(
        'Nuevo',
        'Recibido',
        'En preparación',
        'Cocinando',
        'Listo cocina',
        'Terminado',
        'Entregado',
        'Cancelado'
    ) NOT NULL DEFAULT 'Nuevo',

    -- Sirve para impedir cancelar cuando sala ya lo ha servido.
    servido_sala TINYINT(1) NOT NULL DEFAULT 0,

    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_sin_descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    descuento_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    CONSTRAINT fk_pedidos_cliente
        FOREIGN KEY (cliente_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_pedidos_cocinero
        FOREIGN KEY (cocinero_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_pedidos_camarero
        FOREIGN KEY (camarero_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    INDEX idx_pedidos_cliente (cliente_id),
    INDEX idx_pedidos_cocinero (cocinero_id),
    INDEX idx_pedidos_camarero (camarero_id),
    INDEX idx_pedidos_estado (estado),
    INDEX idx_pedidos_fecha (fecha),
    INDEX idx_pedidos_numero_fecha (numero_pedido, fecha),
    INDEX idx_pedidos_servido_sala (servido_sala)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA PEDIDOS_PRODUCTOS
-- --------------------------------------------------------

CREATE TABLE pedidos_productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    CONSTRAINT fk_pedidos_productos_pedido
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_pedidos_productos_producto
        FOREIGN KEY (producto_id) REFERENCES productos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_pedidos_productos_pedido (pedido_id),
    INDEX idx_pedidos_productos_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLA PEDIDOS_OFERTAS
-- --------------------------------------------------------

CREATE TABLE pedidos_ofertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    oferta_id INT NOT NULL,
    descuento_aplicado DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    CONSTRAINT fk_pedidos_ofertas_pedido
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_pedidos_ofertas_oferta
        FOREIGN KEY (oferta_id) REFERENCES ofertas(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    UNIQUE KEY uk_pedido_oferta (pedido_id, oferta_id),
    INDEX idx_pedidos_ofertas_pedido (pedido_id),
    INDEX idx_pedidos_ofertas_oferta (oferta_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;