-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-05-2017 a las 12:03:21
-- Versión del servidor: 5.7.14
-- Versión de PHP: 7.0.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dosimatic`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `getMenues` (`_usuario` VARCHAR(20))  BEGIN
   SELECT `menuid`, `label`, `iconfa`, `orden`, `activo`, `ruta`, `resalte`, `tiporesalte`, `submenuid` ,case when(select count(*) from `menu` as `m` where `m`.`submenuid`=`menu`.`menuid`)>0 then 1 else 0 end as `hijos` 
   FROM menu WHERE menuid IN (
		SELECT DISTINCT menuid FROM (
			SELECT menuid FROM menu WHERE ruta IN (SELECT rutaid FROM rutasperfil WHERE perfilid=(SELECT perfilid FROM usuarios WHERE usuario=_usuario limit 1))
			UNION ALL
			SELECT submenuid FROM menu WHERE ruta IN (SELECT rutaid FROM rutasperfil WHERE perfilid=(SELECT perfilid FROM usuarios WHERE usuario=_usuario limit 1))
		)AS TMP ORDER BY menuid
	) ORDER BY ORDEN,RUTA,MENUID
	;
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getPermisos` (`_usuario` VARCHAR(20))  BEGIN
   SELECT `rutaid`, `ruta`, convert(`descripcion` USING ascii) as descripcion 
   FROM `rutas` 
   WHERE `rutas`.`rutaid` IN 
	(SELECT rutaid FROM rutasperfil WHERE perfilid=(SELECT perfilid FROM usuarios WHERE usuario=_usuario OR email=_usuario limit 1))
	;
    
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gd_aprobacion`
--

CREATE TABLE `gd_aprobacion` (
  `aprobacionid` int(11) NOT NULL,
  `aprobado` tinyint(1) NOT NULL DEFAULT '1',
  `archivoid` varchar(20) NOT NULL,
  `usuario` varchar(256) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `gd_aprobacion`
--

INSERT INTO `gd_aprobacion` (`aprobacionid`, `aprobado`, `archivoid`, `usuario`, `fecha`) VALUES
(11, 1, 'PGD001', 'ezerpa', '2017-05-21 21:06:05'),
(15, 0, 'MGD001', 'ezerpa', '2017-05-22 00:02:03'),
(14, 1, 'MGH001', 'ezerpa', '2017-05-21 23:38:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gd_archivo`
--

CREATE TABLE `gd_archivo` (
  `archivoid` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `gestionid` varchar(20) DEFAULT NULL,
  `convencionid` varchar(20) DEFAULT NULL,
  `archivoidaux` varchar(20) DEFAULT NULL,
  `denominacion` varchar(256) NOT NULL,
  `observaciones` varchar(5000) DEFAULT NULL,
  `usuario` varchar(256) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `gd_archivo`
--

INSERT INTO `gd_archivo` (`archivoid`, `nombre`, `gestionid`, `convencionid`, `archivoidaux`, `denominacion`, `observaciones`, `usuario`, `fecha`) VALUES
('MGD001', '20170521_000942_PGD-01-F2 LISTADO MAESTRO DOCUMENTOS UTDUC.xlsx', 'GD', 'M', '', 'GLOSARIO GENERAL UDTFUC', '', 'ezerpa', '2017-05-20 20:36:58'),
('PGD001', '20170521_141728_Rif Actualizado QZ.pdf', 'GD', 'P', '', 'ELABORACIÃ³N DE DOCUMENTOS', '', 'ezerpa', '2017-05-21 10:17:31'),
('MGH001', '20170522_033711_Presupuesto.pdf', 'GH', 'M', '', 'PRUEBAS DE DESARROLLO', '', 'ezerpa', '2017-05-21 23:37:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gd_convencion`
--

CREATE TABLE `gd_convencion` (
  `convencionid` varchar(10) NOT NULL,
  `denominacion` varchar(256) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `gd_convencion`
--

INSERT INTO `gd_convencion` (`convencionid`, `denominacion`) VALUES
('M', 'MANUAL'),
('P', 'PROCEDIMIENTO'),
('I', 'INSTRUCTIVO'),
('F', 'FORMATO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gd_gestion`
--

CREATE TABLE `gd_gestion` (
  `gestionid` varchar(10) NOT NULL,
  `denominacion` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `gd_gestion`
--

INSERT INTO `gd_gestion` (`gestionid`, `denominacion`) VALUES
('GA', 'GESTION DE ANALISIS, MEDICION Y MEJORA'),
('GP', 'GESTION DE PRODUCCION'),
('GH', 'GESTION DEL RECURSO HUMANO'),
('GD', 'GESTION DOCUMENTAL'),
('GF', 'GESTION DE RECURSO FISICO'),
('GC', 'GESTION DE CALIDAD');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gd_vigencia`
--

CREATE TABLE `gd_vigencia` (
  `vigenciaid` int(11) NOT NULL,
  `archivoid` varchar(20) NOT NULL,
  `fdesde` datetime NOT NULL,
  `fhasta` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu`
--

CREATE TABLE `menu` (
  `menuid` varchar(20) NOT NULL,
  `label` varchar(50) NOT NULL,
  `iconfa` varchar(50) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT '1',
  `activo` bit(1) NOT NULL DEFAULT b'1',
  `ruta` varchar(25) NOT NULL DEFAULT '#',
  `resalte` varchar(10) NOT NULL DEFAULT '',
  `tiporesalte` varchar(10) NOT NULL DEFAULT 'info',
  `submenuid` varchar(20) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `menu`
--

INSERT INTO `menu` (`menuid`, `label`, `iconfa`, `orden`, `activo`, `ruta`, `resalte`, `tiporesalte`, `submenuid`) VALUES
('prueba1', 'Niveles Prueba', 'fa-sitemap', 10, b'0', '#', '', 'info', ''),
('prueba21', 'Nivel 3', '', 1, b'1', '#', '', 'info', 'prueba1'),
('prueba22', 'Item Segundo Nivel', '', 2, b'1', '#', '', 'info', 'prueba1'),
('prueba23', 'Item Segundo Nivel', '', 3, b'1', '#', '', 'info', 'prueba1'),
('prueba211', 'Item Tercer Nivel', '', 1, b'1', 'home', '', 'info', 'prueba21'),
('prueba212', 'Item Tercer Nivel', '', 2, b'1', 'home', '', 'info', 'prueba21'),
('seguridad', 'Seguridad', 'fa-users', 1, b'1', '#', '', '', ''),
('usuarios', 'Usuarios', '', 2, b'1', 'usuarios', '', 'info', 'seguridad'),
('perfiles', 'Perfiles', '', 1, b'1', 'perfiles', '', 'info', 'seguridad'),
('inicio', 'Escritorio', 'fa-th-large', 0, b'1', 'home', '', '', ''),
('gestiondocumental', 'GestiÃ³n Documental', 'fa-edit', 1, b'1', '', '', '', ''),
('menues', 'Menues', '', 3, b'1', 'menues', '', 'info', 'seguridad'),
('app', 'Aplicacion Web', '', 1, b'0', '', '', '', ''),
('archivos', 'Archivos', '', 1, b'1', 'archivos', '', '', 'gestiondocumental');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `organizacion`
--

CREATE TABLE `organizacion` (
  `rif` varchar(20) NOT NULL,
  `razonsocial` varchar(512) NOT NULL,
  `activa` bit(1) NOT NULL DEFAULT b'1',
  `direccion` varchar(1024) NOT NULL,
  `telefono1` varchar(15) DEFAULT '',
  `telefono2` varchar(15) DEFAULT '',
  `paginaweb` varchar(256) DEFAULT '',
  `email` varchar(100) DEFAULT '',
  `logo` varchar(512) DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfiles`
--

CREATE TABLE `perfiles` (
  `perfilid` varchar(20) NOT NULL,
  `denominacion` varchar(512) NOT NULL,
  `activo` bit(1) NOT NULL DEFAULT b'1',
  `fechacreado` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `perfiles`
--

INSERT INTO `perfiles` (`perfilid`, `denominacion`, `activo`, `fechacreado`) VALUES
('admin', 'Administrador', b'1', '2017-04-23 10:31:11'),
('invitado', 'Invitado', b'1', '2017-04-23 10:32:25'),
('inactivos', 'Inactivos', b'0', '2017-04-23 10:32:25'),
('DGAC', 'Director de Gestion y Aseguramiento de la Calidad', b'1', '2017-05-22 07:52:12'),
('DT', 'Director Tecnico', b'1', '2017-05-22 07:52:12'),
('Gerente', 'Gerente', b'1', '2017-05-22 07:52:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutas`
--

CREATE TABLE `rutas` (
  `rutaid` varchar(50) NOT NULL,
  `ruta` varchar(100) NOT NULL,
  `descripcion` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `rutas`
--

INSERT INTO `rutas` (`rutaid`, `ruta`, `descripcion`) VALUES
('home', 'home', 'Módulo inicial del sistema.'),
('ingresar', 'ingresar', 'Iniciar y Cerrar Sesiones'),
('salir', 'salir', 'Cerrar sesion y salir del sistema'),
('perfiles', 'perfiles', 'Perfiles de usuarios'),
('usuarioadd', 'usuarioadd', 'Agregar usuarios al sistema'),
('usuarioedit', 'usuarioedit', 'Editar datos de usuarios'),
('perfil', 'perfil', 'Mostrar detalles de un perfil'),
('perfiledit', 'perfiledit', 'Editar datos de perfiles'),
('perfiladd', 'perfiladd', 'Agregar perfil'),
('menues', 'menues', 'Menu de la aplicacion'),
('enconstruccion', 'enconstruccion', 'Mostrar al usuario que el recurso que quiere acceder esta en construccion'),
('usuarios', 'usuarios', 'Listado de usuarios'),
('archivos', 'archivos', 'Archivos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutasperfil`
--

CREATE TABLE `rutasperfil` (
  `rutaid` varchar(50) NOT NULL,
  `perfilid` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `rutasperfil`
--

INSERT INTO `rutasperfil` (`rutaid`, `perfilid`) VALUES
('salir', 'admin'),
('perfiles', 'admin'),
('ingresar', 'admin'),
('home', 'admin'),
('usuarioedit', 'admin'),
('perfil', 'admin'),
('usuarioadd', 'admin'),
('perfiledit', 'admin'),
('perfiladd', 'admin'),
('menues', 'admin'),
('enconstruccion', 'admin'),
('usuarios', 'admin'),
('enconstruccion', 'invitado'),
('salir', 'invitado'),
('ingresar', 'invitado'),
('home', 'invitado'),
('archivos', 'invitado'),
('archivos', 'admin');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `usuario` varchar(256) NOT NULL,
  `clave` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `masculino` bit(1) NOT NULL DEFAULT b'1',
  `fechanacimiento` datetime NOT NULL,
  `activo` bit(1) NOT NULL DEFAULT b'1',
  `fecharegistro` datetime DEFAULT CURRENT_TIMESTAMP,
  `nombres` varchar(256) DEFAULT NULL,
  `apellidos` varchar(256) DEFAULT NULL,
  `avatar` varchar(256) DEFAULT NULL,
  `perfilid` varchar(20) DEFAULT NULL,
  `firma` varchar(1000) DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`usuario`, `clave`, `email`, `masculino`, `fechanacimiento`, `activo`, `fecharegistro`, `nombres`, `apellidos`, `avatar`, `perfilid`, `firma`) VALUES
('ezerpa', '1e80e757838a336a275c2c6c79473e2b', 'ebertunerg@gmail.com', b'1', '1987-09-16 00:00:00', b'1', '2017-04-21 15:41:54', 'Ebert Manuel', 'Zerpa Figueroa', 'assets/avatars/alfabeto/E.png', 'admin', 'uploads/firmas/ezerpa.png'),
('invitado', '81dc9bdb52d04dc20036dbd8313ed055', 'ebert15@hotmail.com', b'1', '1988-02-16 00:00:00', b'1', '2017-04-28 14:05:55', 'Invitado', '.', '', 'invitado', ''),
('pruebas2', '1e80e757838a336a275c2c6c79473e2b', 'pruebas2@pruebas.com', b'1', '1987-11-16 00:00:00', b'0', '2017-04-28 14:07:52', 'Pablo', 'Medina', '', '', ''),
('test', '0cc175b9c0f1b6a831c399e269772661', 'test@gmail.com', b'0', '1988-01-19 00:00:00', b'0', '2017-05-07 07:18:46', 'MarÃ­a', 'Medina', '', 'invitado', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `gd_aprobacion`
--
ALTER TABLE `gd_aprobacion`
  ADD PRIMARY KEY (`aprobacionid`);

--
-- Indices de la tabla `gd_vigencia`
--
ALTER TABLE `gd_vigencia`
  ADD PRIMARY KEY (`vigenciaid`);

--
-- Indices de la tabla `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`menuid`);

--
-- Indices de la tabla `organizacion`
--
ALTER TABLE `organizacion`
  ADD PRIMARY KEY (`rif`);

--
-- Indices de la tabla `perfiles`
--
ALTER TABLE `perfiles`
  ADD PRIMARY KEY (`perfilid`);

--
-- Indices de la tabla `rutas`
--
ALTER TABLE `rutas`
  ADD PRIMARY KEY (`rutaid`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `FK_perfilid` (`perfilid`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `gd_aprobacion`
--
ALTER TABLE `gd_aprobacion`
  MODIFY `aprobacionid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT de la tabla `gd_vigencia`
--
ALTER TABLE `gd_vigencia`
  MODIFY `vigenciaid` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
