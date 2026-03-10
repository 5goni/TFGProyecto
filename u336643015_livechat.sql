-- Base de datos unificada: u336643015_livechat (incluye sistema de estudio)
CREATE DATABASE IF NOT EXISTS `u336643015_livechat` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `u336643015_livechat`;

-- --------------------------------------------------------
-- 1. Estructura de tabla para la tabla `usuarios`
-- --------------------------------------------------------
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos `usuarios`
INSERT INTO `usuarios` (`id`, `nombre`, `password`, `fecha_registro`) VALUES
(1, 'Gonzalo', '$2y$10$eWCsXbf6u0zk4Uml6cXdjOMJMQbAzGxM0.Gik1n3xaH2.kRj20RDO', '2026-01-30 07:26:47'),
(2, 'Pedropa', '$2y$10$lMEa9u/D5WRrHd2yYiuZaOBNF30piK1LE4Rp8h/AS2TQzcP1nT19a', '2026-01-30 07:42:29'),
(3, 'Juan pedro Cendrero gonzalez', '$2y$10$.M9PPDD28Pa3lSoJJSeX/.RN2r8Da0BLVnzxOIoVvusTAt.l3y8cq', '2026-01-30 09:39:00'),
(4, 'A', '$2y$10$RpZr0AVEjzX9IsvcNbdXoexfJYRasfaN.y/xTyy1/kVT8vRtwUIY2', '2026-01-30 09:47:23'),
(5, 'aldos', '$2y$10$KKuNFd8SBPv7MNahZp.3puVdT9CCpLzMiLiC7v88YxXclpyPfdZu2', '2026-01-30 16:09:45'),
(7, 'Goncho', '$2y$10$zphxB1o42sEUYePbwrH5/OX0QqAhahb1QhRa0KPwlT4X6iVpqRtmW', '2026-02-06 12:42:37');

-- --------------------------------------------------------
-- 2. Estructura de tabla para la tabla `mensajes`
-- --------------------------------------------------------
CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  `archivo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos `mensajes`
INSERT INTO `mensajes` (`id`, `usuario_id`, `mensaje`, `fecha`, `archivo`) VALUES
(3, 1, 'hola', '2026-01-30 07:41:14', ''),
(4, 1, '1', '2026-01-30 07:41:18', ''),
(6, 2, 'Hola', '2026-01-30 07:43:00', ''),
(7, 1, 's', '2026-01-30 07:43:16', ''),
(8, 2, '1234', '2026-01-30 07:47:43', ''),
(9, 1, 'hola', '2026-01-30 07:48:09', ''),
(10, 1, 'hola1234', '2026-01-30 08:05:03', ''),
(11, 2, 'Moros', '2026-01-30 08:05:20', ''),
(13, 3, 'hijo de puta', '2026-01-30 09:39:13', ''),
(14, 1, 'mamaguevo', '2026-01-30 09:39:20', ''),
(18, 5, 'HOLA', '2026-01-30 16:10:23', ''),
(19, 7, 'bola', '2026-02-06 12:42:48', ''),
(20, 7, 'hola', '2026-02-06 12:43:23', '1770381803_a56c4b49fe.png');

-- --------------------------------------------------------
-- 3. Estructura de tabla para la tabla `historial` (Sistema de Estudio)
-- --------------------------------------------------------
CREATE TABLE `historial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pregunta` text DEFAULT NULL,
  `resumen` text DEFAULT NULL,
  `aciertos` int(11) DEFAULT NULL,
  `total_preguntas` int(11) DEFAULT NULL,
  `tipo` varchar(20) DEFAULT 'resumen',
  `contenido_json` longtext DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `historial_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos `historial` (Se asignan al usuario ID 1 para consistencia)
INSERT INTO `historial` (`id`, `user_id`, `pregunta`, `resumen`, `aciertos`, `total_preguntas`, `fecha`) VALUES
(1, 1, 'hablame sobre la romanizacion', 'La romanización se refiere al proceso de adaptación...', 3, 4, '2026-02-17 08:23:49'),
(2, 1, 'explicame la romanizacion', 'La romanización fue el proceso de adaptación...', 0, 1, '2026-02-17 08:32:17'),
(3, 1, 'habla me sobre la romanizacion', 'La Romanización fue el proceso de adaptación...', NULL, NULL, '2026-02-17 08:38:00'),
(4, 1, 'habla me sobre la romanizacion', 'La romanización fue el proceso de adaptación...', NULL, NULL, '2026-02-17 08:38:10');

COMMIT;