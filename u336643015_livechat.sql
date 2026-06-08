-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2026 at 11:58 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u336643015_livechat`
--

-- --------------------------------------------------------

--
-- Table structure for table `historial`
--

CREATE TABLE `historial` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pregunta` text DEFAULT NULL,
  `resumen` text DEFAULT NULL,
  `aciertos` int(11) DEFAULT NULL,
  `total_preguntas` int(11) DEFAULT NULL,
  `tipo` varchar(20) DEFAULT 'resumen',
  `contenido_json` longtext DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `historial`
--

INSERT INTO `historial` (`id`, `user_id`, `pregunta`, `resumen`, `aciertos`, `total_preguntas`, `tipo`, `contenido_json`, `fecha`) VALUES
(8, 12, 'de que va esto', '\"Mentor Cocina\" es un **proyecto emprendedor** para una plataforma digital (EdTech y Social Media) que ofrece **mentoría culinaria intergeneracional en tiempo real** a través de videollamadas. Su objetivo principal es conectar a jóvenes (18-30 años), que buscan aprender a cocinar de forma práctica y sostenible, con mentores senior (jubilados o expertos) que desean compartir sus conocimientos y combatir la soledad. La empresa, **Mentor Cocina S.L.**, se constituye como una Sociedad de Responsabilidad Limitada con un capital social de 12.000€.\n\nEl modelo de negocio se diferencia por la **conexión humana auténtica**, la **ayuda inmediata** y el enfoque en la **sostenibilidad** (reducción del desperdicio alimentario mediante un algoritmo de \"match por ingredientes\"). Aborda tendencias sociales como el \"lazy cooking\", la ansiedad alimentaria, la conciencia \"zero waste\" y el impacto de la **soledad en jóvenes y mayores**.\n\nEstratégicamente, la plataforma utiliza un **Análisis DAFO** que identifica fortalezas como la conexión humana y el enfoque sostenible; debilidades como la brecha digital de los mentores senior y la dependencia del voluntariado; oportunidades como subvenciones por impacto social y alianzas estratégicas con marcas de alimentación; y amenazas como la **saturación de contenido gratuito** y el avance de la **IA generativa**.\n\nEl **Análisis PESTEL** explora factores políticos (normas de la \"Gig Economy\", políticas de envejecimiento activo, estrategias de salud pública), económicos (auge de la \"Silver Economy\", inflación, cambios en el mercado publicitario), sociales (paradoja de la soledad, \"lazy cooking\", sostenibilidad, cambios en la estructura familiar), tecnológicos (IA generativa como aliada/rival, accesibilidad \"Age-Tech\", infraestructura de vídeo de baja latencia, integración IoT) y legales (responsabilidad civil, seguridad alimentaria, protección de datos RGPD, propiedad intelectual, normativa laboral).\n\nLa **financiación inicial** es mixta (capital privado para desarrollo MVP y público para impacto social), y el **modelo de ingresos** se basa principalmente en la **publicidad segmentada** y el **marketing de afiliación** una vez alcanzada una masa crítica de usuarios. Se contempla un **modelo freemium** para mentores que deseen mayor visibilidad. El **umbral de rentabilidad** se alcanzará cuando los ingresos por publicidad y patrocinios cubran los costes operativos fijos y variables, priorizando la retención de usuarios y alianzas estratégicas.', NULL, NULL, 'resumen', NULL, '2026-05-05 10:36:42'),
(9, 12, 'hazme unas flashcard', '', NULL, NULL, 'flashcards', '[{\"frente\":\"¿Cuál es la razón social y objeto social de la empresa Mentor Cocina?\",\"reverso\":\"Razón Social: Mentor Cocina S.L. Objeto Social: Crear plataformas tecnológicas para asesoría gastronómica en tiempo real, enseñar cocina y aprovechar alimentos con algoritmos de emparejamiento.\"},{\"frente\":\"¿Por qué se eligió la forma jurídica de Sociedad de Responsabilidad Limitada (S.L.) para Mentor Cocina?\",\"reverso\":\"Para limitar la responsabilidad de los socios al capital invertido, permitir una gestión ágil, y facilitar la entrada de futuros socios inversores.\"},{\"frente\":\"¿Cuáles son los tres segmentos principales de usuarios (demanda) a los que se dirige Mentor Cocina?\",\"reverso\":\"Gente joven (18-30 años), fans del \'Healthy Cooking\' y \'Zero Waste\', y personas con \'Ansiedad con la comida\'.\"},{\"frente\":\"¿Quiénes son los principales proveedores de \'talento\' (mentores) para la plataforma Mentor Cocina?\",\"reverso\":\"Mentores sénior (jubilados, \'Baby Boomers\') y expertos en \'Cocina de mercado\' que comparten conocimientos tradicionales y prácticas de aprovechamiento.\"},{\"frente\":\"¿Cuál es la principal fortaleza o diferenciador de Mentor Cocina frente a la competencia digital?\",\"reverso\":\"La conexión humana real en vivo, el aprendizaje práctico personalizado y la experiencia intergeneracional que genera confianza y compañía.\"},{\"frente\":\"¿Cuáles son los dos modelos de ingresos principales que Mentor Cocina plantea para su sostenibilidad?\",\"reverso\":\"Publicidad segmentada dentro de la app (CPM) y marketing de afiliación (comisiones por venta de productos recomendados).\"},{\"frente\":\"Dentro del análisis DAFO, ¿qué amenaza importante representa el avance de la IA para Mentor Cocina?\",\"reverso\":\"Herramientas de IA que ofrecen recetas personalizadas y resuelven dudas rápidamente, saturación de contenido gratuito y dificultad para sostener los costes del servicio.\"},{\"frente\":\"Según el análisis PESTEL, ¿cómo aborda Mentor Cocina la \'Paradoja de la Soledad\' a nivel social?\",\"reverso\":\"Actúa como un puente de cohesión social entre generaciones, permitiendo a los mayores sentirse útiles y a los jóvenes obtener una conexión humana y habilidades prácticas.\"},{\"frente\":\"Describe la fase de \'Transformación (Asesoría)\' en el proceso de prestación del servicio de Mentor Cocina.\",\"reverso\":\"El sistema conecta al usuario con un mentor libre mediante videollamada de baja latencia (WebRTC) para una sesión en vivo de 10-15 minutos, donde se transfiere conocimiento.\"},{\"frente\":\"¿Cuál es la estrategia inicial de financiación de Mentor Cocina?\",\"reverso\":\"Una financiación mixta (capital privado y capital público) para reducir el riesgo inicial, cubrir el desarrollo y la puesta en marcha, y una transición progresiva a un modelo basado en publicidad.\"}]', '2026-05-05 10:39:30');

-- --------------------------------------------------------

--
-- Table structure for table `mensajes`
--

CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  `archivo` varchar(255) DEFAULT NULL,
  `archivo_original` varchar(255) DEFAULT NULL,
  `adjunto_tipo` varchar(50) DEFAULT NULL,
  `adjunto_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mensajes`
--

INSERT INTO `mensajes` (`id`, `usuario_id`, `mensaje`, `fecha`, `archivo`, `archivo_original`, `adjunto_tipo`, `adjunto_id`) VALUES
(3, 1, 'hola', '2026-01-30 06:41:14', '', NULL, NULL, NULL),
(4, 1, '1', '2026-01-30 06:41:18', '', NULL, NULL, NULL),
(6, 2, 'Hola', '2026-01-30 06:43:00', '', NULL, NULL, NULL),
(7, 1, 's', '2026-01-30 06:43:16', '', NULL, NULL, NULL),
(8, 2, '1234', '2026-01-30 06:47:43', '', NULL, NULL, NULL),
(9, 1, 'hola', '2026-01-30 06:48:09', '', NULL, NULL, NULL),
(10, 1, 'hola1234', '2026-01-30 07:05:03', '', NULL, NULL, NULL),
(11, 2, 'Moros', '2026-01-30 07:05:20', '', NULL, NULL, NULL),
(13, 3, 'hijo de puta', '2026-01-30 08:39:13', '', NULL, NULL, NULL),
(14, 1, 'mamaguevo', '2026-01-30 08:39:20', '', NULL, NULL, NULL),
(18, 5, 'HOLA', '2026-01-30 15:10:23', '', NULL, NULL, NULL),
(19, 7, 'bola', '2026-02-06 11:42:48', '', NULL, NULL, NULL),
(20, 7, 'hola', '2026-02-06 11:43:23', '1770381803_a56c4b49fe.png', NULL, NULL, NULL),
(21, 8, 'hola', '2026-03-15 11:45:34', NULL, NULL, NULL, NULL),
(22, 8, 'hola', '2026-03-15 11:45:52', NULL, NULL, NULL, NULL),
(23, 8, 'hola', '2026-03-15 11:48:52', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `resumenes`
--

CREATE TABLE `resumenes` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `documento` varchar(255) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `resumenes`
--

INSERT INTO `resumenes` (`id`, `titulo`, `descripcion`, `user_id`, `documento`, `fecha_creacion`) VALUES
(3, 'apuntes cosas', 'dasadasdasd', 11, '1777537057_4088ffb9ca93a2a9_imagen_2026-04-30_101736853.png', '2026-04-30 08:17:37');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `fecha_registro`) VALUES
(1, 'Gonzalo', NULL, '$2y$10$eWCsXbf6u0zk4Uml6cXdjOMJMQbAzGxM0.Gik1n3xaH2.kRj20RDO', '2026-01-30 06:26:47'),
(2, 'Pedropa', NULL, '$2y$10$lMEa9u/D5WRrHd2yYiuZaOBNF30piK1LE4Rp8h/AS2TQzcP1nT19a', '2026-01-30 06:42:29'),
(3, 'Juan pedro Cendrero gonzalez', NULL, '$2y$10$.M9PPDD28Pa3lSoJJSeX/.RN2r8Da0BLVnzxOIoVvusTAt.l3y8cq', '2026-01-30 08:39:00'),
(4, 'A', NULL, '$2y$10$RpZr0AVEjzX9IsvcNbdXoexfJYRasfaN.y/xTyy1/kVT8vRtwUIY2', '2026-01-30 08:47:23'),
(5, 'aldos', NULL, '$2y$10$KKuNFd8SBPv7MNahZp.3puVdT9CCpLzMiLiC7v88YxXclpyPfdZu2', '2026-01-30 15:09:45'),
(7, 'Goncho', NULL, '$2y$10$zphxB1o42sEUYePbwrH5/OX0QqAhahb1QhRa0KPwlT4X6iVpqRtmW', '2026-02-06 11:42:37'),
(8, 'Ana1234', NULL, '$2y$10$kPzRD44COFmtH8TZbK6Aqu5A..aWDDS1zbPdx2IUXiefcEOD41Ir2', '2026-03-15 11:41:39'),
(9, 'Jc1234', NULL, '$2y$10$JobkvZsmWfMJMhIBEt4P4u6MQpi5MiGnt0/Nqywrm1Q2RQeOM4laW', '2026-03-19 14:27:36'),
(10, 'muchacho', NULL, '$2y$10$okesjW/DBtcOlpEeOc0S2uJ1YUzSWdAwVlXpiy123esCRj7H2jGwO', '2026-04-29 07:26:06'),
(11, 'pedrito', NULL, '$2y$10$/DBRVlnBXsx3mzVWIQ4jxeOtj4mOOJr7ct/kDRFHmnolfkq7jiIfy', '2026-04-30 07:46:40'),
(12, 'josepe', NULL, '$2y$10$7cIYfcNYOh/..UsQFRTnLeM.kc2Bjr1EQ4eiaKLYeTF4Tfnsrv.bG', '2026-04-30 08:18:40'),
(13, 'blablabla', NULL, '$2y$10$jwhyUshofVMMXJZJLxcg6uFRkb2FJmvZv367J8KuXp4bo/GvTdQpO', '2026-05-06 10:20:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `historial`
--
ALTER TABLE `historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `resumenes`
--
ALTER TABLE `resumenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `historial`
--
ALTER TABLE `historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `resumenes`
--
ALTER TABLE `resumenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `historial`
--
ALTER TABLE `historial`
  ADD CONSTRAINT `historial_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resumenes`
--
ALTER TABLE `resumenes`
  ADD CONSTRAINT `resumenes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
