-- SQL script para crear las tablas necesarias para el sistema de reportes

-- Crear tabla de acciones si no existe
CREATE TABLE IF NOT EXISTS `acciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accion` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar acciones predefinidas
INSERT INTO `acciones` (`accion`) VALUES
('Cambio de Motor'),
('Cambio de Bomba'),
('Mantenimiento Preventivo'),
('Reparación de Tubería'),
('Limpieza de Pozo'),
('Instalación de Equipos'),
('Otras acciones')
ON DUPLICATE KEY UPDATE `accion` = VALUES(`accion`);

-- Crear tabla de reportes
CREATE TABLE IF NOT EXISTS `reportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poso_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `incremento_lps` tinyint(1) DEFAULT 0,
  `incremento_usuarios` tinyint(1) DEFAULT 0,
  `accion_id` int(11) NOT NULL,
  `motor_id` int(11) DEFAULT NULL,
  `bomba_id` int(11) DEFAULT NULL,
  `otra_accion` text DEFAULT NULL,
  `unidad` varchar(100) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_reportes_poso` (`poso_id`),
  KEY `fk_reportes_accion` (`accion_id`),
  KEY `fk_reportes_motor` (`motor_id`),
  KEY `fk_reportes_bomba` (`bomba_id`),
  KEY `fk_reportes_usuario` (`usuario_id`),
  CONSTRAINT `fk_reportes_poso` FOREIGN KEY (`poso_id`) REFERENCES `posos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reportes_accion` FOREIGN KEY (`accion_id`) REFERENCES `acciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reportes_motor` FOREIGN KEY (`motor_id`) REFERENCES `motores` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reportes_bomba` FOREIGN KEY (`bomba_id`) REFERENCES `bombas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reportes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
