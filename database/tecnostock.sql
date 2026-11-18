-- =====================================================================
-- TecnoStock - Sistema de Inventario
-- Script de creación de base de datos
-- Motor: MySQL 8.x (compatible con MariaDB 10.x de XAMPP)
-- Ejecutar desde phpMyAdmin o consola MySQL
-- =====================================================================

DROP DATABASE IF EXISTS tecnostock;
CREATE DATABASE tecnostock
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_spanish2_ci;

USE tecnostock;

-- ---------------------------------------------------------------------
-- Tabla: usuario
-- ---------------------------------------------------------------------
CREATE TABLE usuario (
    id_usuario  INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    correo      VARCHAR(150) NOT NULL UNIQUE,
    clave       VARCHAR(255) NOT NULL,
    estado      TINYINT(1) NOT NULL DEFAULT 1
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: categoria
-- ---------------------------------------------------------------------
CREATE TABLE categoria (
    id_categoria  INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(80) NOT NULL,
    descripcion   VARCHAR(255) DEFAULT NULL,
    estado        TINYINT(1) NOT NULL DEFAULT 1
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: producto
-- ---------------------------------------------------------------------
CREATE TABLE producto (
    id_producto   INT AUTO_INCREMENT PRIMARY KEY,
    codigo        VARCHAR(40) NOT NULL UNIQUE,
    nombre        VARCHAR(120) NOT NULL,
    descripcion   TEXT DEFAULT NULL,
    precio        DECIMAL(10, 2) NOT NULL DEFAULT 0,
    stock_actual  INT NOT NULL DEFAULT 0,
    stock_minimo  INT NOT NULL DEFAULT 0,
    estado        TINYINT(1) NOT NULL DEFAULT 1,
    id_categoria  INT NOT NULL,
    CONSTRAINT fk_producto_categoria
        FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria),
    CONSTRAINT chk_precio_positivo
        CHECK (precio >= 0),
    CONSTRAINT chk_stock_actual_positivo
        CHECK (stock_actual >= 0),
    CONSTRAINT chk_stock_minimo_positivo
        CHECK (stock_minimo >= 0)
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: movimiento
-- ---------------------------------------------------------------------
CREATE TABLE movimiento (
    id_movimiento  INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo           ENUM('entrada', 'salida') NOT NULL,
    cantidad        INT NOT NULL,
    observacion     VARCHAR(255) DEFAULT NULL,
    id_producto    INT NOT NULL,
    id_usuario     INT NOT NULL,
    CONSTRAINT fk_mov_producto
        FOREIGN KEY (id_producto) REFERENCES producto(id_producto),
    CONSTRAINT fk_mov_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    CONSTRAINT chk_cantidad_positiva
        CHECK (cantidad > 0)
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- Índices
-- ---------------------------------------------------------------------
CREATE INDEX idx_producto_categoria ON producto(id_categoria);
CREATE INDEX idx_producto_estado    ON producto(estado);
CREATE INDEX idx_mov_producto       ON movimiento(id_producto);
CREATE INDEX idx_mov_usuario        ON movimiento(id_usuario);
CREATE INDEX idx_mov_fecha          ON movimiento(fecha_hora);

-- =====================================================================
-- Datos iniciales (seed)
-- =====================================================================

-- IMPORTANTE: El usuario administrador se crea con el script database/seed.php
-- que genera el hash bcrypt correcto. Ejecutar ese script una vez tras importar este SQL.

-- Categorías
INSERT INTO categoria (nombre, descripcion, estado) VALUES
('Cables y Adaptadores', 'Cables USB, HDMI, adaptadores de corriente', 1),
('Audio', 'Audífonos, parlantes, micrófonos', 1),
('Almacenamiento', 'Pendrives, discos externos, tarjetas SD', 1),
('Cargadores', 'Cargadores de notebook y celular', 1),
('Accesorios Computación', 'Mouse, teclados, pads, soportes', 1);

-- Productos
INSERT INTO producto (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, estado, id_categoria) VALUES
('CB-USB-001',  'Cable USB-C 1m',       'Cable trenzado USB-C a USB-A, carga rápida', 4900, 25, 5, 1, 1),
('CB-HDMI-002', 'Cable HDMI 2m',        'HDMI 2.0, 4K@60Hz, conector chapado en oro', 7900, 12, 5, 1, 1),
('AU-BT-001',   'Audífonos Bluetooth',  'Audífonos inalámbricos, batería 8h',       15900, 8, 4, 1, 2),
('AU-CB-002',   'Audífonos con cable',  'Audífonos con micrófono, jack 3.5mm',        6900, 3, 6, 1, 2),
('AL-PD-001',   'Pendrive 32GB',        'USB 3.0, velocidad lectura 80MB/s',         5900, 15, 5, 1, 3),
('AL-HD-002',   'Disco Externo 1TB',    'Portátil USB 3.1, compatible PC y Mac',     39900, 4, 3, 1, 3),
('CG-NB-001',   'Cargador Notebook',    'Universal 90W, 15 conectores intercambiables', 12900, 6, 3, 1, 4),
('AC-MS-001',   'Mouse Inalámbrico',    '2.4GHz, silencioso, 1600 DPI',              8900, 10, 5, 1, 5),
('AC-KB-002',   'Teclado USB',          'Teclado compacto, teclas de perfil bajo',   9900, 0, 3, 1, 5),
('AC-PT-003',   'Pad antideslizante',   'Lona de goma, 90x40cm',                     3500, 18, 8, 1, 5);

-- Movimientos de ejemplo
INSERT INTO movimiento (fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario) VALUES
('2026-11-01 09:30:00', 'entrada', 30, 'Compra inicial a proveedor', 1, 1),
('2026-11-03 14:15:00', 'salida',  5,  'Venta mostrador',            1, 1),
('2026-11-02 10:00:00', 'entrada', 10, 'Reposición stock',           4, 1),
('2026-11-05 16:45:00', 'salida',  7,  'Venta online',               4, 1),
('2026-11-08 11:20:00', 'salida',  10, 'Venta a cliente corporativo', 9, 1);

-- =====================================================================
-- Fin del script
-- =====================================================================
