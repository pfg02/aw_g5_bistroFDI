-- --------------------------------------------------------
-- Base de datos: 'bistrofdi'
-- --------------------------------------------------------

DROP DATABASE IF EXISTS bistrofdi;
CREATE DATABASE bistrofdi
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE bistrofdi;

-- --------------------------------------------------------
-- Borrado de tablas
-- --------------------------------------------------------

DROP TABLE IF EXISTS pedidos_ofertas;
DROP TABLE IF EXISTS ofertas_productos;
DROP TABLE IF EXISTS pedido_productos;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS ofertas;

-- --------------------------------------------------------
-- Creación de usuario con contraseña
-- --------------------------------------------------------

CREATE USER IF NOT EXISTS 'bistro_user'@'localhost' IDENTIFIED BY 'bistro_pass';
GRANT ALL PRIVILEGES ON bistrofdi.* TO 'bistro_user'@'localhost';
FLUSH PRIVILEGES;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla 'categorias'
-- --------------------------------------------------------

CREATE TABLE categorias (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT DEFAULT NULL,
    imagen TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla 'productos'
-- --------------------------------------------------------

CREATE TABLE productos (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT(11) DEFAULT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    precio_base DECIMAL(10,2) DEFAULT NULL,
    stock INT(11) DEFAULT 0,
    imagen VARCHAR(255) DEFAULT 'default_prod.png',
    iva INT(11) DEFAULT 10,
    ofertado TINYINT(1) DEFAULT 1,
    requiere_cocina TINYINT(1) NOT NULL DEFAULT 1,
    KEY `id_categoria` (`id_categoria`),
    CONSTRAINT `productos_categoria_fk`
        FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla 'usuarios'
-- --------------------------------------------------------

CREATE TABLE usuarios (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('cliente','camarero','cocinero','gerente') NOT NULL DEFAULT 'cliente',
    avatar VARCHAR(255) DEFAULT 'img/avatares/default.jpg',
    fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla 'pedidos'
-- --------------------------------------------------------

CREATE TABLE pedidos (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT(11) NOT NULL,
    cocinero_id INT(11) DEFAULT NULL,
    numero_pedido INT NOT NULL,
    tipo VARCHAR(20) NOT NULL,
    estado VARCHAR(20) NOT NULL,
    fecha DATETIME NOT NULL,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    descuento_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_sin_descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    UNIQUE KEY `numero_pedido` (`numero_pedido`),
    KEY `cliente_id` (`cliente_id`),
    KEY `cocinero_id` (`cocinero_id`),
    CONSTRAINT `pedidos_cliente_fk`
        FOREIGN KEY (`cliente_id`) REFERENCES `usuarios`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `pedidos_cocinero_fk`
        FOREIGN KEY (`cocinero_id`) REFERENCES `usuarios`(`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla 'pedido_productos'
-- --------------------------------------------------------

CREATE TABLE pedido_productos (
    pedido_id INT(11) NOT NULL,
    producto_id INT(11) NOT NULL,
    cantidad INT(11) NOT NULL,
    preparado TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (pedido_id, producto_id),
    KEY `producto_id` (`producto_id`),
    CONSTRAINT `pedido_productos_pedido_fk`
        FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `pedido_productos_producto_fk`
        FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla 'ofertas'
-- --------------------------------------------------------

CREATE TABLE ofertas (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    descuento_porcentaje DECIMAL(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla 'ofertas_productos'
-- --------------------------------------------------------

CREATE TABLE ofertas_productos (
    oferta_id INT(11) NOT NULL,
    producto_id INT(11) NOT NULL,
    cantidad INT(11) NOT NULL,
    PRIMARY KEY (oferta_id, producto_id),
    KEY `producto_id` (`producto_id`),
    CONSTRAINT `ofertas_productos_oferta_fk`
        FOREIGN KEY (`oferta_id`) REFERENCES `ofertas`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `ofertas_productos_producto_fk`
        FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla 'pedidos_ofertas'
-- --------------------------------------------------------

CREATE TABLE pedidos_ofertas (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT(11) NOT NULL,
    oferta_id INT(11) NOT NULL,
    veces_aplicada INT(11) NOT NULL DEFAULT 1,
    orden_aplicacion INT(11) NOT NULL,
    descuento_aplicado DECIMAL(10,2) NOT NULL,
    KEY `pedido_id` (`pedido_id`),
    KEY `oferta_id` (`oferta_id`),
    CONSTRAINT `pedidos_ofertas_pedido_fk`
        FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `pedidos_ofertas_oferta_fk`
        FOREIGN KEY (`oferta_id`) REFERENCES `ofertas`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;