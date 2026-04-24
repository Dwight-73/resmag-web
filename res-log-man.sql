-- Script MySQL/MariaDB para phpMyAdmin
-- Base de datos: res-log-man
-- Codificación: UTF-8 (utf8mb4)

CREATE DATABASE IF NOT EXISTS `res-log-man`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `res-log-man`;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `correo` VARCHAR(190) NOT NULL,
  `clave_hash` VARCHAR(255) NOT NULL,
  `nombre_completo` VARCHAR(190) NOT NULL,
  `rol` ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uq_usuarios_correo` (`correo`),
  KEY `idx_usuarios_rol` (`rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de servicios
CREATE TABLE IF NOT EXISTS `servicios` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(50) NOT NULL,
  `nombre` VARCHAR(190) NOT NULL,
  `categoria` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_servicios_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos iniciales de servicios (se ignoran si ya existen por clave única)
INSERT IGNORE INTO `servicios` (`codigo`, `nombre`, `categoria`, `descripcion`) VALUES
('LIMP-IND', 'Limpieza industrial', 'general', 'Servicios de limpieza y saneamiento en áreas portuarias e industriales.'),
('MANT-OP', 'Mantenimiento', 'general', 'Mantenimiento preventivo y correctivo de equipos e infraestructura.'),
('HOT-WORK', 'Trabajos en caliente (cortes con amoladora)', 'general', 'Corte y desmonte controlado con procedimientos de seguridad.'),
('CRU-LIMP', 'Limpieza para cruceros', 'cruceros', 'Limpieza especializada en áreas y cubiertas de cruceros.'),
('CRU-AGUA', 'Abastecimiento de agua para cruceros', 'cruceros', 'Suministro y control de agua para naves de crucero.');

INSERT INTO `usuarios` (`correo`, `clave_hash`, `nombre_completo`, `rol`)
VALUES ('admin@resmag.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9/if3uG1qf/2uY5FvG1DGa', 'Administrador', 'admin')
ON DUPLICATE KEY UPDATE
  `clave_hash` = VALUES(`clave_hash`),
  `nombre_completo` = VALUES(`nombre_completo`),
  `rol` = 'admin';

INSERT INTO `usuarios` (`correo`, `clave_hash`, `nombre_completo`, `rol`)
VALUES ('admin2@resmag.com', '$2y$10$Jk3m2JrP6C1yQ7vB8sX7ue7q3hQ0YkH7q8f9lK2xP5vT9wZsF2JrS', 'Administrador 2', 'admin')
ON DUPLICATE KEY UPDATE
  `clave_hash` = VALUES(`clave_hash`),
  `nombre_completo` = VALUES(`nombre_completo`),
  `rol` = 'admin';
