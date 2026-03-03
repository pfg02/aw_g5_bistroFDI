-- 1. CREACIÓN DE TABLAS --

-- Tabla de Usuarios (Funcionalidad 0)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Almacenará el hash de la contraseña
    nombre VARCHAR(100),
    apellidos VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    rol ENUM('cliente', 'camarero', 'gerente') DEFAULT 'cliente',
    avatar VARCHAR(255) DEFAULT 'default.png'
);

-- Tabla de Categorías (Funcionalidad 1)
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255) -- Ruta de la imagen en la carpeta /img
);

-- Tabla de Productos (Funcionalidad 1)
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio_base DECIMAL(10,2) NOT NULL, -- Precio sin IVA
    iva INT NOT NULL, -- Valores permitidos: 4, 10, 21
    stock INT DEFAULT 0,
    ofertado TINYINT(1) DEFAULT 1, -- Borrado lógico: 1=En carta, 0=Retirado
    imagen VARCHAR(255),
    FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE SET NULL
);

-- 2. DATOS DE PRUEBA (Para la evaluación en el VPS) --

-- Insertar Categorías iniciales
INSERT INTO categorias (nombre, descripcion, imagen) VALUES 
('Entrantes', 'Aperitivos y platos para compartir', 'entrantes.jpg'),
('Platos Principales', 'Nuestras famosas hamburguesas y carnes', 'principales.jpg'),
('Postres', 'Dulces artesanales hechos a diario', 'postres.jpg');

-- Insertar un Gerente de prueba (Password: admin123)
-- Nota: La contraseña debe estar hasheada con password_hash() en PHP
INSERT INTO usuarios (username, password, nombre, rol) 
VALUES ('admin', '$2y$10$8K.pUshU6L6L4n.gI9lBbeYvH8z6R7oGzW0zW9zW9zW9zW9zW9zW9', 'Admin Gerente', 'gerente');

-- Insertar Productos de ejemplo
INSERT INTO productos (id_categoria, nombre, descripcion, precio_base, iva, stock, ofertado) VALUES 
(2, 'Hamburguesa Especial', 'Jugosa hamburguesa con queso y bacon', 8.80, 10, 50, 1),
(1, 'Patatas Bravas', 'Clásicas patatas con salsa picante casera', 4.50, 10, 100, 1),
(3, 'Tarta de Queso', 'Tarta cremosa con mermelada de arándanos', 5.00, 10, 20, 1);