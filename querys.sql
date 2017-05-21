/*Obtener menues segun los permisos del perfil del usuario*/
DROP PROCEDURE IF EXISTS getMenues; 
DELIMITER $$
CREATE PROCEDURE getMenues(_usuario VARCHAR(20))
BEGIN
   SELECT `menuid`, `label`, `iconfa`, `orden`, `activo`, `ruta`, `resalte`, `tiporesalte`, `submenuid` ,case when(select count(*) from `menu` as `m` where `m`.`submenuid`=`menu`.`menuid`)>0 then 1 else 0 end as `hijos` 
   FROM menu WHERE menuid IN (
		SELECT DISTINCT menuid FROM (
			SELECT menuid FROM menu WHERE ruta IN (SELECT rutaid FROM rutasperfil WHERE perfilid=(SELECT perfilid FROM usuarios WHERE usuario=_usuario limit 1))
			UNION ALL
			SELECT submenuid FROM menu WHERE ruta IN (SELECT rutaid FROM rutasperfil WHERE perfilid=(SELECT perfilid FROM usuarios WHERE usuario=_usuario limit 1))
		)AS TMP ORDER BY menuid
	) ORDER BY ORDEN,RUTA,MENUID
	;
    
END $$
DELIMITER ;
/*CALL getMenues('EZERPA');*/

/*Obtener todas las rutas a la cual el usuario tiene permiso a acceder*/
DROP PROCEDURE IF EXISTS getPermisos; 
DELIMITER $$
CREATE PROCEDURE getPermisos(_usuario VARCHAR(20))
BEGIN
   SELECT `rutaid`, `ruta`, convert(`descripcion` USING ascii) as descripcion 
   FROM `rutas` 
   WHERE `rutas`.`rutaid` IN 
	(SELECT rutaid FROM rutasperfil WHERE perfilid=(SELECT perfilid FROM usuarios WHERE (usuario=_usuario OR email=_usuario) limit 1))
	;
    
END $$
DELIMITER ;

/*CALL getPermisos('EZERPA');*/



SELECT rutaid FROM rutasperfil WHERE rutaid='/home' and perfilid=(SELECT perfilid FROM usuarios WHERE usuario='ezerpa' limit 1)