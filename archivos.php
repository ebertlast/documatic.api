<?php

require 'vendor/autoload.php';
require 'tokens.php';
require 'conexdb.php';

define("PREFIJO","gd_");

/*Adaptando los headers permitidos en las peticiones*/
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});



$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            // ->withHeader('Authorization', 'Zerpa')
            ->withHeader('Access-Control-Allow-Origin', '*')            
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, Authentication')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ;
});


/*FIN Adaptando los headers permitidos en las peticiones*/



$app->get('/', function ($request, $response, $args = [])  use($db, $app) {
    return $response->withStatus(400)->write('Bad Request');
});

/*
$app->post('/upload', function ($request, $response, $args) {
    $files = $request->getUploadedFiles();
    if (empty($files['newfile'])) {
        throw new Exception('Expected a newfile');
    }
 
    $newfile = $files['newfile'];

    if ($newfile->getError() === UPLOAD_ERR_OK) {
        $uploadFileName = $newfile->getClientFilename();
        $newfile->moveTo("/documentos/$uploadFileName");
    }
    // do something with $newfile
});*/

/* G E S T I O N E S */
// get gestiones
$app->get("/gestion", function($request, $response, $args) use($db, $app) { 
    if (!$request->hasHeader('Authorization')) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'Token no encontrado en la solicitud.'))
            ;
    }
    $jwt=explode(" ",$request->getHeaderLine('Authorization'))[1];
   
    $dataUser=getToken($jwt);

    if(array_key_exists('error', $dataUser)){
       return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson($dataUser)
            ;
    }
    
    $usuario=$dataUser->usuario;
    $clave=$dataUser->clave;
   

    if(!$usuario){
        return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'No hemos podido identificarte, intenta volver a iniciar sesión'))
            ;
    }else{

    }
    
    $tabla=PREFIJO."gestion";
    $sql="SELECT `gestionid`, convert(`denominacion` USING ascii) as `denominacion` FROM `$tabla` WHERE 1;";
    
    
    try {
        $query = $db->query($sql);
    } catch(PDOException $e) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage()))
            ; 
    }

    $data = array();   
    if($query)
        while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
            $data[] = $fila;
        }

    return $response
            ->withHeader('Content-type', 'application/json')
            // ->withJson(array("status" => "success", "data" => $data))
            ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
            ;
});

// edit gestion
$app->post("/gestion/{gestionid}", function($request, $response, $args) use($db, $app) { 
    if (!$request->hasHeader('Authorization')) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'Token no encontrado en la solicitud.'))
            ;
    }
    $jwt=explode(" ",$request->getHeaderLine('Authorization'))[1];
    
    $dataUser=getToken($jwt);
    if(array_key_exists('error', $dataUser)){
       return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson($dataUser)
            ;
    }
    
    $usuario=$dataUser->usuario;
    $clave=$dataUser->clave;
   
    if(!$usuario){
        return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'No hemos podido identificarte, intenta volver a iniciar sesión'))
            ;
    }else{

    }


    $json = $request->getParsedBody();
    $modelo = json_decode($json['json'],true)["gestion"];
     
    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => json_encode((int)$perfil["activo"])))
    //         ;


    $modelo["gestionid"]=strtoupper($modelo["gestionid"]);
    $modelo["denominacion"]=strtoupper($modelo["denominacion"]);
    $tabla=PREFIJO."gestion";
    
    $sql="UPDATE `{$tabla}` SET `gestionid`='{$modelo['gestionid']}',`denominacion`='{$modelo['denominacion']}' WHERE `{$tabla}`.`gestionid`='{$args['gestionid']}'";
    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => json_encode($sql)))
    //         ;


    try {
        $update = $db->query($sql);
    } catch(PDOException $e) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage()))
            ; 
    }

	if ($update) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('success' => 'Datos del proceso actualizados'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El proceso no ha podido ser actualizado, vuelve a intentarlo'))
            ; 
	}
    
    // $token = newToken($dataUser);
	// $result = array("status" => "success", "data" => $data[0], "token"=>$token);
	// echo json_encode($result);
    return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array("status" => "success", "data" => $data[0], "token"=>newToken($dataUser)))
            ;
    
});

// delete gestion
$app->delete('/gestion/{gestionid}', function ($request, $response, $args) use($db, $app) {
    if (!$request->hasHeader('Authorization')) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'Token no encontrado en la solicitud.'))
            ;
    }
    $jwt=explode(" ",$request->getHeaderLine('Authorization'))[1];
    
    $dataUser=getToken($jwt);
    if(array_key_exists('error', $dataUser)){
       return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson($dataUser)
            ;
    }
    
    $usuario=$dataUser->usuario;
    $clave=$dataUser->clave;
   
    if(!$usuario){
        return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'No hemos podido identificarte, intenta volver a iniciar sesión'))
            ;
    }else{

    }

    $tabla=PREFIJO."gestion";
     
   $sql="DELETE FROM `{$tabla}` WHERE `{$tabla}`.`gestionid`='{$args['gestionid']}'";

    try {
        $delete = $db->query($sql);
    } catch(PDOException $e) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage()))
            ; 
    }

	if ($delete) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('success' => 'Proceso eliminado'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El proceso no ha podido ser eliminado, vuelve a intentarlo'))
            ; 
	}
});

/** add gestion*/
$app->put("/gestion", function($request, $response, $args) use($db, $app) { 
    if (!$request->hasHeader('Authorization')) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'Token no encontrado en la solicitud.'))
            ;
    }
    $jwt=explode(" ",$request->getHeaderLine('Authorization'))[1];
    
    $dataUser=getToken($jwt);
    if(array_key_exists('error', $dataUser)){
       return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson($dataUser)
            ;
    }
    
    $usuario=$dataUser->usuario;
    $clave=$dataUser->clave;
   
    if(!$usuario){
        return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'No hemos podido identificarte, intenta volver a iniciar sesión'))
            ;
    }else{

    }

    $json = $request->getParsedBody();
    $modelo = json_decode($json['json'],true)["gestion"];
     
    $modelo["gestionid"]=strtoupper($modelo["gestionid"]);
    $modelo["denominacion"]=strtoupper($modelo["denominacion"]);
    
    $sql="INSERT INTO `gd_gestion` (`gestionid`, `denominacion`) VALUES ('{$modelo['gestionid']}', '{$modelo['denominacion']}')";
    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => json_encode($sql)))
    //         ;


    try {
        $update = $db->query($sql);
    } catch(PDOException $e) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage()))
            ; 
    }

	if ($update) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('success' => 'Proceso registrado satisfactoriamente'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El proceso no ha podido ser registrado, vuelve a intentarlo'))
            ; 
	}
    
    // $token = newToken($dataUser);
	// $result = array("status" => "success", "data" => $data[0], "token"=>$token);
	// echo json_encode($result);
    return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array("status" => "success", "data" => $data[0], "token"=>newToken($dataUser)))
            ;
    
});

/* C O N V E N C I O N E S */
// get convencion
$app->get("/convencion/[{convencionid}]", function($request, $response, $args) use($db, $app) { 
    if (!$request->hasHeader('Authorization')) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'Token no encontrado en la solicitud.'))
            ;
    }
    $jwt=explode(" ",$request->getHeaderLine('Authorization'))[1];
   
    $dataUser=getToken($jwt);

    if(array_key_exists('error', $dataUser)){
       return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson($dataUser)
            ;
    }
    
    $usuario=$dataUser->usuario;
    $clave=$dataUser->clave;
   

    if(!$usuario){
        return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'No hemos podido identificarte, intenta volver a iniciar sesión'))
            ;
    }else{

    }
    $tabla=PREFIJO."convencion";
    $where="1";
    if(isset($args['convencionid']))
        $where="`convencionid`='".$args["convencionid"]."'"; 
    $sql="SELECT `convencionid`, convert(`denominacion` USING ascii) as `denominacion` FROM `$tabla` WHERE {$where};";
    
    
    try {
        $query = $db->query($sql);
    } catch(PDOException $e) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage()))
            ; 
    }
    $data = array();
    if($query)
        while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
            $data[] = $fila;
        }

    return $response
            ->withHeader('Content-type', 'application/json')
            // ->withJson(array("status" => "success", "data" => $data))
            ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
            ;
});

// edit convencion
$app->post("/convencion/{convencionid}", function($request, $response, $args) use($db, $app) { 
    if (!$request->hasHeader('Authorization')) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'Token no encontrado en la solicitud.'))
            ;
    }
    $jwt=explode(" ",$request->getHeaderLine('Authorization'))[1];
    
    $dataUser=getToken($jwt);
    if(array_key_exists('error', $dataUser)){
       return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson($dataUser)
            ;
    }
    
    $usuario=$dataUser->usuario;
    $clave=$dataUser->clave;
   
    if(!$usuario){
        return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'No hemos podido identificarte, intenta volver a iniciar sesión'))
            ;
    }else{

    }


    $json = $request->getParsedBody();
    $modelo = json_decode($json['json'],true)["model"];
     
    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => json_encode((int)$perfil["activo"])))
    //         ;


    $modelo["convencionid"]=strtoupper($modelo["convencionid"]);
    $modelo["denominacion"]=strtoupper($modelo["denominacion"]);
    $tabla=PREFIJO."convencion";
    
    $sql="UPDATE `{$tabla}` SET `convencionid`='{$modelo['convencionid']}',`denominacion`='{$modelo['denominacion']}' WHERE `{$tabla}`.`convencionid`='{$args['convencionid']}'";
    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => json_encode($sql)))
    //         ;


    try {
        $update = $db->query($sql);
    } catch(PDOException $e) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage()))
            ; 
    }

	if ($update) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('success' => 'Datos del proceso actualizados'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El proceso no ha podido ser actualizado, vuelve a intentarlo'))
            ; 
	}
    
    // $token = newToken($dataUser);
	// $result = array("status" => "success", "data" => $data[0], "token"=>$token);
	// echo json_encode($result);
    return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array("status" => "success", "data" => $data[0], "token"=>newToken($dataUser)))
            ;
    
});

// delete convencion
$app->delete('/convencion/{convencionid}', function ($request, $response, $args) use($db, $app) {
    if (!$request->hasHeader('Authorization')) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'Token no encontrado en la solicitud.'))
            ;
    }
    $jwt=explode(" ",$request->getHeaderLine('Authorization'))[1];
    
    $dataUser=getToken($jwt);
    if(array_key_exists('error', $dataUser)){
       return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson($dataUser)
            ;
    }
    
    $usuario=$dataUser->usuario;
    $clave=$dataUser->clave;
   
    if(!$usuario){
        return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'No hemos podido identificarte, intenta volver a iniciar sesión'))
            ;
    }else{

    }

    $tabla=PREFIJO."convencion";
     
   $sql="DELETE FROM `{$tabla}` WHERE `{$tabla}`.`convencionid`='{$args['convencionid']}'";

    try {
        $delete = $db->query($sql);
    } catch(PDOException $e) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage()))
            ; 
    }

	if ($delete) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('success' => 'Registro eliminado'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El registro no ha podido ser eliminado, vuelve a intentarlo'))
            ; 
	}
});

/** add convencion*/
$app->put("/convencion", function($request, $response, $args) use($db, $app) { 
    if (!$request->hasHeader('Authorization')) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'Token no encontrado en la solicitud.'))
            ;
    }
    $jwt=explode(" ",$request->getHeaderLine('Authorization'))[1];
    
    $dataUser=getToken($jwt);
    if(array_key_exists('error', $dataUser)){
       return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson($dataUser)
            ;
    }
    
    $usuario=$dataUser->usuario;
    $clave=$dataUser->clave;
   
    if(!$usuario){
        return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'No hemos podido identificarte, intenta volver a iniciar sesión'))
            ;
    }else{

    }

    $json = $request->getParsedBody();
    $modelo = json_decode($json['json'],true)["model"];
     
    $modelo["convencionid"]=strtoupper($modelo["convencionid"]);
    $modelo["denominacion"]=strtoupper($modelo["denominacion"]);
    
    $sql="INSERT INTO `gd_convencion` (`convencionid`, `denominacion`) VALUES ('{$modelo['convencionid']}', '{$modelo['denominacion']}')";
    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => json_encode($sql)))
    //         ;


    try {
        $update = $db->query($sql);
    } catch(PDOException $e) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage()))
            ; 
    }

	if ($update) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('success' => 'Proceso registrado satisfactoriamente'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El proceso no ha podido ser registrado, vuelve a intentarlo'))
            ; 
	}
    
    // $token = newToken($dataUser);
	// $result = array("status" => "success", "data" => $data[0], "token"=>$token);
	// echo json_encode($result);
    return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array("status" => "success", "data" => $data[0], "token"=>newToken($dataUser)))
            ;
    
});


/* A R C H I V O S */
// get
$app->get("/archivo/[{archivoid}]", function($request, $response, $args) use($db, $app) { 
    if (!$request->hasHeader('Authorization')) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'Token no encontrado en la solicitud.'))
            ;
    }
    $jwt=explode(" ",$request->getHeaderLine('Authorization'))[1];
   
    $dataUser=getToken($jwt);

    if(array_key_exists('error', $dataUser)){
       return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson($dataUser)
            ;
    }
    
    $usuario=$dataUser->usuario;
    $clave=$dataUser->clave;
   

    if(!$usuario){
        return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'No hemos podido identificarte, intenta volver a iniciar sesión'))
            ;
    }else{

    }

    $tabla=PREFIJO."archivo";
    $where="1";
    if(isset($args['archivoid']))
        $where="`archivoid`='".$args["archivoid"]."'"; 
    $sql="SELECT `archivoid`, `nombre`, `gestionid`, `convencionid`, `archivoidaux`, `denominacion`, `observaciones`, `usuario`, `fecha` FROM `$tabla` WHERE {$where};";
    
    
    try {
        $query = $db->query($sql);
    } catch(PDOException $e) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage()))
            ; 
    }

    $data = array();
    if($query)
        while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
            $data[] = $fila;
        }

    return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
            // ->withJson(array("status" => "success", "data" => $data))
            ;
});

/** nuevo */
$app->put("/archivo", function($request, $response, $args) use($db, $app) { 
    if (!$request->hasHeader('Authorization')) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'Token no encontrado en la solicitud.'))
            ;
    }
    $jwt=explode(" ",$request->getHeaderLine('Authorization'))[1];
    
    $dataUser=getToken($jwt);
    if(array_key_exists('error', $dataUser)){
       return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson($dataUser)
            ;
    }
    
    $usuario=$dataUser->usuario;
    $clave=$dataUser->clave;
   
    if(!$usuario){
        return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'No hemos podido identificarte, intenta volver a iniciar sesión'))
            ;
    }else{

    }

    $json = $request->getParsedBody();
    $modelo = json_decode($json['json'],true)["model"];
     
    $modelo["archivoid"]=strtoupper($modelo["archivoid"]);
    $modelo["denominacion"]=strtoupper($modelo["denominacion"]);
    $modelo["usuario"]=$usuario;
    $tabla=PREFIJO."archivo";
    
    $sql="INSERT INTO `{$tabla}`(`archivoid`, `nombre`, `gestionid`, `convencionid`, `archivoidaux`, `denominacion`, `observaciones`, `usuario`) VALUES ('{$modelo['archivoid']}', '{$modelo['nombre']}', '{$modelo['gestionid']}', '{$modelo['convencionid']}', '{$modelo['archivoidaux']}', '{$modelo['denominacion']}', '{$modelo['observaciones']}', '{$modelo['usuario']}')";

    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => json_encode($sql)))
    //         ;


    try {
        $update = $db->query($sql);
    } catch(PDOException $e) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage()))
            ; 
    }

	if ($update) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('success' => 'Proceso registrado satisfactoriamente'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El proceso no ha podido ser registrado, vuelve a intentarlo'))
            ; 
	}
    
    // $token = newToken($dataUser);
	// $result = array("status" => "success", "data" => $data[0], "token"=>$token);
	// echo json_encode($result);
    return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array("status" => "success", "data" => $data[0], "token"=>newToken($dataUser)))
            ;
    
});


$app->post('/archivo-upload', function ($request, $response, $args) {
    $files = $request->getUploadedFiles();
    if (empty($files['files'])) {
        return $response
            ->withStatus(412)
            ->withHeader('Content-type', 'application/json')
            // ->withJson(array('error' => (empty($files['files']))))
            ->withJson(array('error' => 'Ningún fichero encontrado en la solicitud'))
        ; 
    }
    $newfile = $files['files'];
    $uploadFileName=date("Ymd_His_");   
    if ($newfile->getError() === UPLOAD_ERR_OK) {
        $uploadFileName .= $newfile->getClientFilename();
        $newfile->moveTo("./uploads/$uploadFileName");
    }else{
        return $response
            ->withStatus(500)
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => "Documento no ha podido ser subido a nuestro servidor"))
        ; 
    }  
    $data = array('filename' => $uploadFileName);
    return $response
            // ->withStatus(500)
            ->withHeader('Content-type', 'application/json')
            // ->withJson(array('error' => (empty($files['files']))))
            ->withJson(array('status' => 'success','data'=>$uploadFileName))
        ; 
        /*
    
    // do something with $newfile

    if ($newfile->getError() === UPLOAD_ERR_OK) {
        $uploadFileName = $newfile->getClientFilename();
        $newfile->moveTo("/uploads/$uploadFileName");
        $data = array('filename' => $uploadFileName);
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('status' => "success", "data" => $data))
            ; 
    }else{
        return $response
            // ->withStatus(500)
            ->withHeader('Content-type', 'application/json')
            // ->withJson(array('error' => isset($_FILES["uploads"])))
            ->withJson(array('error' => 'El fichero no ha sido subido, intentelo de nuevo'))
        ; 
    }
    */
});


$app->run();