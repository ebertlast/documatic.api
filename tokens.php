<?php

use \Firebase\JWT\JWT;

function newToken($data){
    $time = time(); 
    $token = array(
        'iat' => $time, // Tiempo que inició el token
        'exp' => $time + (60*60), // Tiempo que expirará el token (+1 hora)
        'data' => $data
    );
    return JWT::encode($token, API_SECRET_KEY);
}

function getToken($jwt){
    JWT::$leeway = 60; 
    try{
        $decoded = JWT::decode($jwt, API_SECRET_KEY, array('HS256'));
    } catch (Exception $e) {
        return $bodyError = array('error' => $e->getMessage() );
        
    }
    return $decoded->data;
}


function activo($usuario){
    if(!$usuario){
        return false;
    }
    
    $sql="SELECT `usuario`, `email`, `masculino`, `fechanacimiento`, `usuarios`.`activo`, `fecharegistro`, `nombres`, `apellidos`, `avatar`, `usuarios`.`perfilid`,`perfiles`.`denominacion` as 'perfil' FROM `usuarios` LEFT JOIN `perfiles` ON `usuarios`.`perfilid`=`perfiles`.`perfilid` WHERE '{$usuario}' IN (`usuarios`.`usuario`,`usuarios`.`email`);";
    try {
        $query = $db->query($sql);
    } catch(PDOException $e) {
        return false;
    }
    if($query)
        while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
            return ($fila["activo"]==="1");
        }
    return false;
}