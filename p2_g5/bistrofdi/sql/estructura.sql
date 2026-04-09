-- --------------------------------------------------------
-- Base de datos: 'bistrofdi'
-- --------------------------------------------------------

DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS pedido_productos;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS usuarios;


-- --------------------------------------------------------
-- Creación de usuario con contaseña
-- --------------------------------------------------------

CREATE USER IF NOT EXISTS 'bistro_user'@'localhost' IDENTIFIED BY 'bistro_pass';
GRANT ALL PRIVILEGES ON bistrofdi.* TO 'bistro_user'@'localhost';

-- --------------------------------------------------------
-- Estructura de tabla para la tabla 'categorias'
-- --------------------------------------------------------

CREATE TABLE categorias (
	id int(11) AUTO_INCREMENT PRIMARY KEY,
	nombre varchar(100) NOT NULL UNIQUE KEY,
	descripcion text DEFAULT NULL,
	imagen text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla 'productos'
-- --------------------------------------------------------

CREATE TABLE productos (
	id int(11) AUTO_INCREMENT PRIMARY KEY,
	id_categoria int(11) DEFAULT NULL,
	nombre varchar(100) NOT NULL,
	descripcion text DEFAULT NULL,
	precio_base decimal(10,2) DEFAULT NULL,
	stock int(11) DEFAULT 0,
	imagen varchar(255) DEFAULT 'default_prod.png',
	iva int(11) DEFAULT 10,
	ofertado tinyint(1) DEFAULT 1,
	KEY `id_categoria` (`id_categoria`),
	CONSTRAINT `productos_categoria_fk` FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla 'usuarios'
-- --------------------------------------------------------

CREATE TABLE usuarios (
	id int(11) AUTO_INCREMENT PRIMARY KEY,
	nombre_usuario varchar(50) NOT NULL UNIQUE KEY,
	email varchar(100) NOT NULL UNIQUE KEY,
	nombre varchar(50) NOT NULL,
	apellidos varchar(100) NOT NULL,
	password_hash varchar(255) NOT NULL,
	rol enum('cliente','camarero','cocinero','gerente') NOT NULL DEFAULT 'cliente',
	avatar varchar(255) DEFAULT 'img/avatares/default.jpg',
	fecha_alta datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla 'pedidos'
-- --------------------------------------------------------

CREATE TABLE pedidos (
	id int(11) AUTO_INCREMENT PRIMARY KEY,
	cliente_id int(11) NOT NULL,
	cocinero_id int(11) DEFAULT NULL,
	numero_pedido INT NOT NULL,
	tipo varchar(20) NOT NULL,
	estado varchar(20) NOT NULL,
	fecha datetime NOT NULL,
	total decimal(10,2) NOT NULL,
	KEY `cliente_id` (`cliente_id`),
	KEY `cocinero_id` (`cocinero_id`),
	CONSTRAINT `pedidos_cliente_fk` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `pedidos_cocinero_fk` FOREIGN KEY (`cocinero_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla 'pedido_productos'
-- --------------------------------------------------------

CREATE TABLE pedido_productos (
	pedido_id int(11) NOT NULL,
	producto_id int(11) NOT NULL,
	cantidad int(11) NOT NULL,
	PRIMARY KEY (pedido_id, producto_id),
	CONSTRAINT `pedido_productos_pedido_fk` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `pedido_productos_producto_fk` FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;