<?php

require 'vendor/autoload.php';
require 'tokens.php';
require 'conexdb.php';
require 'mailer.php';

//require_once('fpdf.php');
//require_once('fpdi.php');

//require 'vendor/setasign/fpdi/fpdi.php';
//class_exists('TCPDF', true);
//$pdf = new FPDI();
require 'autenticarDocs.php';
require 'listadoMaestro.php';

define("PREFIJO","gd_");

//class_exists('TCPDF', true); // trigger Composers autoloader to load the TCPDF class


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
    // sendMailTest("http://dosisunitarias.com","ezerpa","enclave","ebertunerg@gmail.com","Ebert Zerpa");
    // notificar("ebertunerg@gmail.com","Ebert Zerpa","2");
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
$sql="SELECT `gestionid`, `denominacion` FROM `$tabla` WHERE 1;";


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
$sql="SELECT `convencionid`, `denominacion` FROM `$tabla` WHERE {$where};";


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
$sql="SELECT `archivoid`, `nombre`, `gestionid`, `convencionid`, `archivoidaux`, `denominacion`, `observaciones`, `$tabla`.`usuario`, `fecha`, `fechaexp`, concat(`nombres`,' ',`apellidos`) AS usuarionombre FROM `$tabla` ";
$sql.=" LEFT JOIN `usuarios` ON `usuarios`.`usuario`=`$tabla`.`usuario`";
$sql.=" WHERE {$where}";


$gestion=PREFIJO."gestion";
$convencion=PREFIJO."convencion";
$sql="SELECT `archivoid`, `nombre`, `$tabla`.`gestionid`, `$tabla`.`convencionid`, ";
$sql.="`archivoidaux`, `$tabla`.`denominacion`, `observaciones`, `$tabla`.`usuario`, ";
$sql.="`fecha`, `fechaexp`, concat(`nombres`,' ',`apellidos`) AS usuarionombre, vinculadoa, version ";    
$sql.=", `$gestion`.`denominacion` as gestion ";
$sql.=", `$convencion`.`denominacion` as convencion ";
$sql.=" FROM `$tabla` ";
$sql.=" LEFT JOIN `usuarios` ON `usuarios`.`usuario`=`$tabla`.`usuario`";
$sql.=" LEFT JOIN `$gestion` ON `$tabla`.`gestionid`=`$gestion`.`gestionid`";
$sql.=" LEFT JOIN `$convencion` ON `$tabla`.`convencionid`=`$convencion`.`convencionid`";
$sql.=" WHERE {$where}";


    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => $sql))
    //         ; 

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

// get fuente
$app->get("/archivofuente/{archivoid}", function($request, $response, $args) use($db, $app) { 
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
$where="`vinculadoa`='".$args["archivoid"]."'"; 
$sql="SELECT `archivoid`, `nombre`, `gestionid`, `convencionid`, `archivoidaux`, `denominacion`, `observaciones`, `$tabla`.`usuario`, `fecha`, `fechaexp`, concat(`nombres`,' ',`apellidos`) AS usuarionombre FROM `$tabla` ";
$sql.=" LEFT JOIN `usuarios` ON `usuarios`.`usuario`=`$tabla`.`usuario`";
$sql.=" WHERE {$where}";


$gestion=PREFIJO."gestion";
$convencion=PREFIJO."convencion";
$sql="SELECT `archivoid`, `nombre`, `$tabla`.`gestionid`, `$tabla`.`convencionid`, ";
$sql.="`archivoidaux`, `$tabla`.`denominacion`, `observaciones`, `$tabla`.`usuario`, ";
$sql.="`fecha`, `fechaexp`, concat(`nombres`,' ',`apellidos`) AS usuarionombre, vinculadoa, version ";    
$sql.=", `$gestion`.`denominacion` as gestion ";
$sql.=", `$convencion`.`denominacion` as convencion ";
$sql.=" FROM `$tabla` ";
$sql.=" LEFT JOIN `usuarios` ON `usuarios`.`usuario`=`$tabla`.`usuario`";
$sql.=" LEFT JOIN `$gestion` ON `$tabla`.`gestionid`=`$gestion`.`gestionid`";
$sql.=" LEFT JOIN `$convencion` ON `$tabla`.`convencionid`=`$convencion`.`convencionid`";
$sql.=" WHERE {$where}";


    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => $sql))
    //         ; 

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

/** get Historico */
$app->get("/archivo/obsoletos/[{archivoid}]", function($request, $response, $args) use($db, $app) { 
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

$tabla=PREFIJO."archivo_h";
$where="1";
if(isset($args['archivoid']))
    $where="`archivoid`='".$args["archivoid"]."'"; 
$gestion=PREFIJO."gestion";
$convencion=PREFIJO."convencion";
$sql="SELECT `archivoid`, `nombre`, `$tabla`.`gestionid`, `$tabla`.`convencionid`, ";
$sql.="`archivoidaux`, `$tabla`.`denominacion`, `observaciones`, `$tabla`.`usuario`, ";
$sql.="`fecha`, `fechaexp`, concat(`nombres`,' ',`apellidos`) AS usuarionombre, vinculadoa, version ";    
$sql.=", `$gestion`.`denominacion` as gestion ";
$sql.=", `$convencion`.`denominacion` as convencion ";
$sql.=" FROM `$tabla` ";
$sql.=" LEFT JOIN `usuarios` ON `usuarios`.`usuario`=`$tabla`.`usuario`";
$sql.=" LEFT JOIN `$gestion` ON `$tabla`.`gestionid`=`$gestion`.`gestionid`";
$sql.=" LEFT JOIN `$convencion` ON `$tabla`.`convencionid`=`$convencion`.`convencionid`";
$sql.=" WHERE {$where}";


    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => $sql))
    //         ; 

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



$sql="INSERT INTO `{$tabla}`(`archivoid`, `nombre`, `gestionid`, `convencionid`, `archivoidaux`, `denominacion`, `observaciones`, `usuario`,`vinculadoa`) VALUES ('{$modelo['archivoid']}', '{$modelo['nombre']}', '{$modelo['gestionid']}', '{$modelo['convencionid']}', '{$modelo['archivoidaux']}', '{$modelo['denominacion']}', '{$modelo['observaciones']}', '{$modelo['usuario']}', '{$modelo['vinculadoa']}')";

    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => json_encode($sql)))
    //         ;


try {
    $update = $db->query($sql);
    $sql2="UPDATE gd_revision set revisado=0 where archivoid='{$modelo['archivoid']}'";
    $db->query($sql2);
    $sql3="UPDATE gd_aprobacion set aprobado=0 where archivoid='{$modelo['archivoid']}'";
    $db->query($sql3);
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

// Eliminar
$app->delete('/archivo/{archivoid}', function ($request, $response, $args) use($db, $app) {
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

$sql="SELECT `nombre` FROM `{$tabla}` WHERE `{$tabla}`.`archivoid`='{$args["archivoid"]}'";
try {
    $query = $db->query($sql);
} catch(PDOException $e) {
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array('error' => $e->getMessage()))
    ; 
}
$archivo="";
if($query){
    while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
        $archivo="uploads/".$fila["nombre"];
    }
}

$sql="DELETE FROM `{$tabla}` WHERE `{$tabla}`.`archivoid`='{$args['archivoid']}'";

try {
    $delete = $db->query($sql);
} catch(PDOException $e) {
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array('error' => $e->getMessage()))
    ; 
}

if ($delete) {
    array_map('unlink', glob($archivo));
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

// Subir Archivo
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

//Descargar Archivo
$app->get("/archivo-download/{archivoid}", function($request, $response, $args) use($db, $app) { 

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
$tabla2=PREFIJO."aprobacion";

$sql="select case when count(archivoid)>0 then 's' else 'n' end as permiso from `{$tabla}` where archivoid='{$args["archivoid"]}' and ( archivoid not in (select archivoid from `{$tabla2}` where archivoid=`{$tabla}`.`archivoid` and aprobado<>1) or archivoid in (select archivoid from `{$tabla2}` where archivoid=`{$tabla}`.archivoid and usuario='{$usuario}'))";
    // $sql="select SQL_NO_CACHE * from (select case when count(archivoid)>0 then 's' else 'n' end as permiso from `gd_aprobacion` where archivoid='PGD001' AND aprobado=1 union ALL select case when count(archivoid)>0 then 's' else 'n' end as permiso from `gd_revision` where archivoid='{$args["archivoid"]}' AND revisado=1) as t ORDER by permiso limit 1";

    // $sql="select count(archivoid) from gd_archivo where archivoid='PGD001' and archivoid not in (select archivoid from gd_aprobacion where archivoid=gd_archivo.archivoid and aprobado<>1)";

    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => $sql))
    //         ; 

array_map('unlink', glob("uploads/*".$usuario."*.pdf"));

try {
    $query = $db->query($sql);
} catch(PDOException $e) {
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array('error' => $e->getMessage()))
    ; 
}
$aprobado=false;
$data = array();
if($query){
    while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
        $data[] = $fila;
        if(($fila['permiso'])=='s'){
            $aprobado=true;
        }
    }
}

$sql="SELECT `nombre`,`convencionid` FROM `{$tabla}` WHERE `{$tabla}`.`archivoid`='{$args["archivoid"]}'";

    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => $sql))
    //         ; 

try {
    $query = $db->query($sql);
} catch(PDOException $e) {
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array('error' => $e->getMessage()))
    ; 
}


$pdfOrigen="";
$convencionid='';
if($query){
    while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {            
        $pdfOrigen="uploads/".$fila["nombre"];
        $convencionid=$fila['convencionid'];
    }
}

// if($convencionid==='F' || $convencionid==='C'){
//     $data = array('filename' => $pdfOrigen);
//     return $response
//     ->withHeader('Content-type', 'application/json')
//     ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
//     ;
// }

    //Determinar si el archivo a descargar es PDF o no
$ext = pathinfo($pdfOrigen, PATHINFO_EXTENSION);
    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => $ext))
    //         ; 
if($ext!=='pdf'){
    $data = array('filename' => $pdfOrigen);
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
    ;
}

$firmas = array();

// $pdfOrigen="uploads/prueba2.pdf";
// $pdfDestino="uploads/prueba.pdf";
$random=substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',5)),0,5);
$pdfDestino="uploads/".$usuario."_".$random.".pdf";
if(!$aprobado){
    $firmas= array();
    firmar($firmas,$pdfOrigen,$pdfDestino,TRUE);
    $data = array('filename' => $pdfDestino);
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
    ;
}

$sql="SELECT CASE WHEN count(archivoid)<=0 then 's' else 'n' end as permiso FROM `{$tabla2}` WHERE archivoid='{$args["archivoid"]}' AND gd_aprobacion.aprobado<1";
$sql="select SQL_NO_CACHE * from (select case when count(archivoid)>0 then 's' else 'n' end as permiso from `gd_aprobacion` where archivoid='{$args["archivoid"]}' AND aprobado=1 union ALL select case when count(archivoid)>0 then 's' else 'n' end as permiso from `gd_revision` where archivoid='{$args["archivoid"]}' AND revisado=1) as t ORDER by permiso limit 1";
     // return $response
     //        ->withHeader('Content-type', 'application/json')
     //        ->withJson(array('error' => $sql))
     //        ; 
try {
    $query = $db->query($sql);
} catch(PDOException $e) {
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array('error' => $e->getMessage()))
    ; 
}
$aprobado=false;
if($query){
    while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
        if(($fila['permiso'])=='s'){
            $aprobado=true;
        }
    }
}
if(!$aprobado){

    $firmas= array();
    firmar($firmas,$pdfOrigen,$pdfDestino,TRUE);
    $data = array('filename' => $pdfDestino);
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
    ;

}
$sql="SELECT CONCAT(usuarios.nombres,' ',usuarios.apellidos)razonsocial,perfiles.denominacion as cargo,usuarios.firma FROM gd_aprobacion INNER JOIN usuarios ON usuarios.usuario=gd_aprobacion.usuario inner join perfiles on perfiles.perfilid=usuarios.perfilid WHERE archivoid='{$args["archivoid"]}'";
$sql="  SELECT CONCAT(usuarios.nombres,' ',usuarios.apellidos)razonsocial,perfiles.denominacion as cargo,usuarios.firma, 'ELABORO' as rol, DATE_FORMAT(fecha, 'Fecha de Emision: %d/%m/%Y') AS fecha
FROM gd_archivo INNER JOIN usuarios ON usuarios.usuario=gd_archivo.usuario inner join perfiles on perfiles.perfilid=usuarios.perfilid WHERE archivoid='{$args["archivoid"]}'
UNION ALL
SELECT CONCAT(usuarios.nombres,' ',usuarios.apellidos)razonsocial,perfiles.denominacion as cargo,usuarios.firma, 'REVISO' AS rol, DATE_FORMAT(fecha, 'Fecha de Aplicacion: %d/%m/%Y') AS fecha FROM gd_revision INNER JOIN usuarios ON usuarios.usuario=gd_revision.usuario inner join perfiles on perfiles.perfilid=usuarios.perfilid WHERE archivoid='{$args["archivoid"]}'
UNION ALL
SELECT CONCAT(usuarios.nombres,' ',usuarios.apellidos)razonsocial,perfiles.denominacion as cargo,usuarios.firma, 'APROBO' AS rol, (SELECT DATE_FORMAT(fechaexp, 'Vigencia: %d/%m/%Y') FROM `gd_archivo` WHERE archivoid='{$args["archivoid"]}') AS fecha FROM gd_aprobacion INNER JOIN usuarios ON usuarios.usuario=gd_aprobacion.usuario inner join perfiles on perfiles.perfilid=usuarios.perfilid WHERE archivoid='{$args["archivoid"]}'";
    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => $sql))
    //         ; 
try {
    $query = $db->query($sql);
} catch(PDOException $e) {
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array('error' => $e->getMessage()))
    ; 
}
$firmas = array();
if($query){
    while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {

        $firmas[]=$fila;

        if($fila['firma']==''){
            $firmas= array();
            firmar($firmas,$pdfOrigen,$pdfDestino,TRUE);
            $data = array('filename' => $pdfDestino);
            return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
            ;
        }

    }
}

    //  $firmas = array(
        // 0 => array('razonsocial' => 'Ebert Manuel Zerpa Figueroa','cargo'=>'Administrador','firma'=>'uploads/firmas/jvalera.png')
        // ,1 => array('razonsocial' => 'Ebert Zerpa','cargo'=>'Tecnico','firma'=>'uploads/firmas/jvalera.png')
        // ,2 => array('razonsocial' => 'Jose Pinero','cargo'=>'Supervisor','firma'=>'uploads/firmas/jvalera.png')
        //,3 => array('razonsocial' => 'Virinia Rojas','cargo'=>'Quimico','firma'=>'uploads/firmas/vrojas.png')
       // ,4 => array('razonsocial' => 'Armando Martinez','cargo'=>'Cantante','firma'=>'uploads/firmas/jvalera.png')
        // );
	// return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => $firmas[0]["firma"]))
    //         ; 

// Si es Formato o Material de Consulta, y se ha aprobado y se ha revisado, entonces,
// Imprimir el archivo original y no firmarlo, de lo contrario, previamente ya se debe
// haber enviado con la marca de agua NO DEFINITIVO 
if($convencionid==='F' || $convencionid==='C'){
    $data = array('filename' => $pdfOrigen);
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
    ;
}


firmar($firmas,$pdfOrigen,$pdfDestino);

$data = array('filename' => $pdfDestino);
return $response
->withHeader('Content-type', 'application/json')
->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
            // ->withJson(array("status" => "success", "data" => $data))
;
});

//Editar Archivo
$app->post("/archivo/{archivoid}", function($request, $response, $args) use($db, $app) { 
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
     ->withJson($dataUser);
 }
 $usuario=$dataUser->usuario;
 $clave=$dataUser->clave;

 if(!$usuario){
    return $response
    ->withStatus(401)
    ->withHeader('Content-type', 'application/json')
    ->withJson(array('error' => 'No hemos podido identificarte, intenta volver a iniciar sesión'));
}


$json = $request->getParsedBody();
$modelo = json_decode($json['json'],true)["model"];

    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => json_encode((int)$perfil["activo"])))
    //         ;
$sql="INSERT INTO `gd_archivo_h`(`archivoid`, `nombre`, `gestionid`, `convencionid`, `archivoidaux`, `denominacion`, `observaciones`, `usuario`, `fecha`, `fechaexp`, `vinculadoa`, `version`) SELECT `archivoid`, `nombre`, `gestionid`, `convencionid`, `archivoidaux`, `denominacion`, `observaciones`, `usuario`, `fecha`, NOW(), `vinculadoa`, `version` FROM `gd_archivo` WHERE 1 AND `archivoid`='{$args['archivoid']}'";


$ext = pathinfo($modelo['nombre'], PATHINFO_EXTENSION);
if($ext==='pdf'){
    // $update = $db->query($sql);
    // $sql2="UPDATE gd_revision set revisado=0 where archivoid='{$modelo['archivoid']}'";
    // $db->query($sql2);
    // $sql3="UPDATE gd_aprobacion set aprobado=0 where archivoid='{$modelo['archivoid']}'";
    // $db->query($sql3);

    /*******************************************************************************************/

    
    $tabla=PREFIJO."archivo";
    $tabla2=PREFIJO."aprobacion";

    $sql="select case when count(archivoid)>0 then 's' else 'n' end as permiso from `{$tabla}` where archivoid='{$args["archivoid"]}' and ( archivoid not in (select archivoid from `{$tabla2}` where archivoid=`{$tabla}`.`archivoid` and aprobado<>1) or archivoid in (select archivoid from `{$tabla2}` where archivoid=`{$tabla}`.archivoid and usuario='{$usuario}'))";
    try {
        $query = $db->query($sql);
    } catch(PDOException $e) {
        return $response
        ->withHeader('Content-type', 'application/json')
        ->withJson(array('error' => $e->getMessage()))
        ; 
    }

    $aprobado=false;
    $data = array();
    if($query){
        while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
            $data[] = $fila;
            if(($fila['permiso'])=='s'){
                $aprobado=true;
            }
        }
    }

    $sql="SELECT `nombre` FROM `{$tabla}` WHERE `{$tabla}`.`archivoid`='{$args["archivoid"]}'";
    try {
        $query = $db->query($sql);
    } catch(PDOException $e) {
        return $response
        ->withHeader('Content-type', 'application/json')
        ->withJson(array('error' => $e->getMessage()))
        ; 
    }

    $pdfOrigen="";
    $nombreArchivo="";
    if($query){
        while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {            
            $pdfOrigen="uploads/".$fila["nombre"];
            $nombreArchivo=$fila['nombre'];
        }
    }

    $firmas = array();
    $pdfDestino="uploads/obsoletos/".$nombreArchivo;
    if(!$aprobado){
        $firmas= array();
        firmar($firmas,$pdfOrigen,$pdfDestino,TRUE);
        $data = array('filename' => $pdfDestino);
        return $response
        ->withHeader('Content-type', 'application/json')
        ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)));
    }

    $sql="SELECT CASE WHEN count(archivoid)<=0 then 's' else 'n' end as permiso FROM `{$tabla2}` WHERE archivoid='{$args["archivoid"]}' AND gd_aprobacion.aprobado<1";
    $sql="SELECT SQL_NO_CACHE * from (select case when count(archivoid)>0 then 's' else 'n' end as permiso from `gd_aprobacion` where archivoid='{$args["archivoid"]}' AND aprobado=1 union ALL SELECT case when count(archivoid)>0 then 's' else 'n' end as permiso from `gd_revision` where archivoid='{$args["archivoid"]}' AND revisado=1) as t ORDER by permiso limit 1";



    try {
        $query = $db->query($sql);
    } catch(PDOException $e) {
        return $response
        ->withHeader('Content-type', 'application/json')
        ->withJson(array('error' => $e->getMessage()))
        ; 
    }

    $aprobado=false;
    if($query){
        while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
            if(($fila['permiso'])=='s'){
                $aprobado=true;
            }
        }
    }

    if(!$aprobado){
        $firmas= array();
        firmar($firmas,$pdfOrigen,$pdfDestino,TRUE);
    }

    $sql="SELECT CONCAT(usuarios.nombres,' ',usuarios.apellidos)razonsocial,perfiles.denominacion as cargo,usuarios.firma FROM gd_aprobacion INNER JOIN usuarios ON usuarios.usuario=gd_aprobacion.usuario inner join perfiles on perfiles.perfilid=usuarios.perfilid WHERE archivoid='{$args["archivoid"]}'";
    $sql="  SELECT CONCAT(usuarios.nombres,' ',usuarios.apellidos)razonsocial,perfiles.denominacion as cargo,usuarios.firma, 'ELABORO' as rol, DATE_FORMAT(fecha, 'Fecha de Emision: %d/%m/%Y') AS fecha
    FROM gd_archivo INNER JOIN usuarios ON usuarios.usuario=gd_archivo.usuario inner join perfiles on perfiles.perfilid=usuarios.perfilid WHERE archivoid='{$args["archivoid"]}'
    UNION ALL
    SELECT CONCAT(usuarios.nombres,' ',usuarios.apellidos)razonsocial,perfiles.denominacion as cargo,usuarios.firma, 'REVISO' AS rol, DATE_FORMAT(fecha, 'Fecha de Aplicacion: %d/%m/%Y') AS fecha FROM gd_revision INNER JOIN usuarios ON usuarios.usuario=gd_revision.usuario inner join perfiles on perfiles.perfilid=usuarios.perfilid WHERE archivoid='{$args["archivoid"]}'
    UNION ALL
    SELECT CONCAT(usuarios.nombres,' ',usuarios.apellidos)razonsocial,perfiles.denominacion as cargo,usuarios.firma, 'APROBO' AS rol, (SELECT DATE_FORMAT(fechaexp, 'Vigencia: %d/%m/%Y') FROM `gd_archivo` WHERE archivoid='{$args["archivoid"]}') AS fecha FROM gd_aprobacion INNER JOIN usuarios ON usuarios.usuario=gd_aprobacion.usuario inner join perfiles on perfiles.perfilid=usuarios.perfilid WHERE archivoid='{$args["archivoid"]}'";

    // return $response
    //     ->withHeader('Content-type', 'application/json')
    //     ->withJson(array('error' => $sql))
    //     ; 

    try {
        $query = $db->query($sql);
    } catch(PDOException $e) {
        return $response
        ->withHeader('Content-type', 'application/json')
        ->withJson(array('error' => $e->getMessage()))
        ; 
    }
    // return $response
    //     ->withHeader('Content-type', 'application/json')
    //     ->withJson(array('error' => $pdfOrigen.' '.$pdfDestino))
    //     ; 
    $firmas = array();
    if($query){
        while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
            $firmas[]=$fila;
            if($fila['firma']==''){
                $firmas= array();
                firmar($firmas,$pdfOrigen,$pdfDestino,TRUE);
            }
        }
    }
    // return $response
    //     ->withHeader('Content-type', 'application/json')
    //     ->withJson(array('error' => $pdfOrigen.' '.$pdfDestino))
    //     ; 
    firmar($firmas,$pdfOrigen,$pdfDestino);

    // return $response
    //     ->withHeader('Content-type', 'application/json')
    //     ->withJson(array('error' => $pdfOrigen.' '.$pdfDestino))
    //     ; 
    // $update = $db->query($sql);


    // $sql2="UPDATE gd_revision set revisado=0 where archivoid='{$modelo['archivoid']}'";
    // $db->query($sql2);
    // $sql3="UPDATE gd_aprobacion set aprobado=0 where archivoid='{$modelo['archivoid']}'";
    // $db->query($sql3);



    $sql="INSERT INTO `gd_archivo_h`(`archivoid`, `nombre`, `gestionid`, `convencionid`, `archivoidaux`, `denominacion`, `observaciones`, `usuario`, `fecha`, `fechaexp`, `vinculadoa`, `version`) SELECT `archivoid`, `{$nombreArchivo}`, `gestionid`, `convencionid`, `archivoidaux`, `denominacion`, `observaciones`, `usuario`, `fecha`, NOW(), `vinculadoa`, `version` FROM `gd_archivo` WHERE 1 AND `archivoid`='{$args['archivoid']}'";
    /*******************************************************************************************/

}


try {
    $insert = $db->query($sql);
} catch(PDOException $e) {
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array('error' => $e->getMessage()))
    ; 
}
    // $modelo["convencionid"]=strtoupper($modelo["convencionid"]);
    // $modelo["denominacion"]=strtoupper($modelo["denominacion"]);
$tabla=PREFIJO."archivo";

$sql="UPDATE `{$tabla}` SET `archivoid`='{$modelo['archivoid']}',`nombre`='{$modelo['nombre']}',`gestionid`='{$modelo['gestionid']}',`convencionid`='{$modelo['convencionid']}',`archivoidaux`='{$modelo['archivoidaux']}',`denominacion`='{$modelo['denominacion']}',`observaciones`='{$modelo['observaciones']}',`usuario`='{$modelo['usuario']}',`fecha`='{$modelo['fecha']}',`fechaexp`='{$modelo['fechaexp']}' WHERE `archivoid`='{$args['archivoid']}'";

$sql="UPDATE `{$tabla}` SET `archivoid`='{$modelo['archivoid']}',`nombre`='{$modelo['nombre']}',`gestionid`='{$modelo['gestionid']}',`convencionid`='{$modelo['convencionid']}',`archivoidaux`='{$modelo['archivoidaux']}',`denominacion`='{$modelo['denominacion']}',`observaciones`='{$modelo['observaciones']}',`usuario`='{$modelo['usuario']}',`fecha`=NOW(),`fechaexp`=DATE_ADD(NOW(), INTERVAL 2 YEAR), `version`=(version+1) WHERE `archivoid`='{$args['archivoid']}'";
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
    ->withJson(array('success' => 'Datos del registro actualizados'))
    ;
} else {
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array('error' => 'El registro no ha podido ser actualizado, vuelve a intentarlo'))
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

// get
$app->get("/listadomaestro", function($request, $response, $args) use($db, $app) { 
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
    ->withJson(array('error' => 'No hemos podido identificarte, intenta volver a iniciar sesión'));
    }

    $tabla=PREFIJO."gestion";
    $sql="SELECT `gestionid`, `denominacion` FROM `$tabla` WHERE 1;";

    try {
        $query = $db->query($sql);
    } catch(PDOException $e) {
        return $response
        ->withHeader('Content-type', 'application/json')
        ->withJson(array('error' => $e->getMessage()))
        ; 
    }

    $gestiones = array();   
    if($query){
      while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
            $fila['denominacion'] = utf8_decode($fila['denominacion']);
            // $fila->denominacion = utf8_decode($fila->denominacion);
            $gestiones[] = $fila;
      };
    }



    $tabla=PREFIJO."archivo";
    $where=" COALESCE(`$tabla`.`vinculadoa`,'') = '' ";
    // if(isset($args['archivoid']))
        // $where="`archivoid`='".$args["archivoid"]."'"; 
    $gestion=PREFIJO."gestion";
    $convencion=PREFIJO."convencion";
    $sql="SELECT `archivoid`, `nombre`, `$tabla`.`gestionid`, `$tabla`.`convencionid`, ";
    $sql.="`archivoidaux`, `$tabla`.`denominacion`, `observaciones`, `$tabla`.`usuario`, ";
    $sql.="`fecha`, `fechaexp`, concat(`nombres`,' ',`apellidos`) AS usuarionombre, vinculadoa, version ";    
    $sql.=", `$gestion`.`denominacion` as gestion ";
    $sql.=", `$convencion`.`denominacion` as convencion ";
    $sql.=" FROM `$tabla` ";
    $sql.=" LEFT JOIN `usuarios` ON `usuarios`.`usuario`=`$tabla`.`usuario`";
    $sql.=" LEFT JOIN `$gestion` ON `$tabla`.`gestionid`=`$gestion`.`gestionid`";
    $sql.=" LEFT JOIN `$convencion` ON `$tabla`.`convencionid`=`$convencion`.`convencionid`";
    $sql.=" WHERE {$where}";
    $sql.=" ORDER BY `$tabla`.`archivoid`";
    try {
        $query = $db->query($sql);
    } catch(PDOException $e) {
        return $response
        ->withHeader('Content-type', 'application/json')
        ->withJson(array('error' => $e->getMessage()))
        ; 
    }
    $documentos = array();
    if($query){
      while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
        $fila['denominacion'] = utf8_decode($fila['denominacion']);
        $documentos[] = $fila;
      }
    }

    $sql="SELECT sum(version)+(select count(archivoid)*0 from `gd_archivo` where COALESCE(`vinculadoa`,'')='') AS version FROM `gd_archivo` WHERE version>0 and COALESCE(`vinculadoa`,'')=''";
    try {
        $query = $db->query($sql);
    } catch(PDOException $e) {
        return $response
        ->withHeader('Content-type', 'application/json')
        ->withJson(array('error' => $e->getMessage()))
        ; 
    }
    $version='001';
    if($query){
      while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
        $version = substr('000'.$fila['version'],-3,3);
      }
    }

    $pdfDestino='uploads/listado_maestro.pdf';
    generarListado($pdfDestino,'PGD-01-F2',$version,$gestiones,$documentos);
    $data = array('filename' => $pdfDestino);
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
    ;
});



/* A P R O B A C I O N E S */
$app->get("/aprobacion/[{archivoid}]", function($request, $response, $args) use($db, $app) { 
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
$tabla=PREFIJO."aprobacion";
$where="1";
if(isset($args['archivoid'])){
    $where="`archivoid`='".$args["archivoid"]."'"; 
}
$sql="SELECT `aprobacionid`, `aprobado`, `archivoid`, `{$tabla}`.`usuario`, `fecha`,concat(`usuarios`.`nombres`,' ',`usuarios`.`apellidos`) as usuarionombre FROM `$tabla` ";
$sql.="LEFT JOIN `usuarios` ON `usuarios`.`usuario`=`{$tabla}`.`usuario`";
$sql.=" WHERE {$where}";
    //   return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => $sql))
    //         ; 

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
            // $fila['aprobado']=($model["aprobado"]=="1");
        $data[] = $fila;
    }

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
            // ->withJson(array("status" => "success", "data" => $data))
    ;
});
$app->delete('/aprobacion/{aprobacionid}', function ($request, $response, $args) use($db, $app) {
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

$tabla=PREFIJO."aprobacion";

$sql="DELETE FROM `{$tabla}` WHERE `{$tabla}`.`aprobacionid`='{$args['aprobacionid']}' ";
    //    $sql+="AND archivoid in (SELECT `archivoid` from `".PREFIJO."archivo` where usuario='".$usuario."')";
    //  return $response
    //             ->withHeader('Content-type', 'application/json')
    //             ->withJson(array('error' => $sql))
    //             ; 
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
$app->put("/aprobacion", function($request, $response, $args) use($db, $app) { 
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

$model = json_decode($json['json'],true)["model"];

$tabla=PREFIJO."aprobacion";
$model['aprobado']=($model["aprobado"]==="1")?"1":"0";
    // $sql="INSERT INTO `{$tabla}`(`aprobado`, `archivoid`, `usuario`, `fecha`) VALUES (`{$model['aprobado']}`, `{$model['archivoid']}`, `{$model['usuario']}`, `{$model['fecha']}`)";

$sql="INSERT INTO `{$tabla}`(`aprobado`, `archivoid`, `usuario`) VALUES ({$model['aprobado']}, '{$model['archivoid']}', '{$model['usuario']}')";
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
    notificarAprobacion($db,$model['usuario']);
        // notificar("ebertunerg@gmail.com","Ebert Zerpa",10);
        // mailing("asunto","cuerpoHTML","cuerpoTXT","ebertunerg@gmail.com","Ebert Zerpa");
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array('success' => 'Registro procesado'))
    ;
} else {
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withJson(array('error' => 'El registro no ha podido ser procesado, vuelve a intentarlo'))
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

function notificarAprobacion($db, $usuario) {
    $tabla=PREFIJO."aprobacion";
    $sql="SELECT count(".$tabla.".aprobacionid)pendientes from ".$tabla." where ".$tabla.".usuario='{$usuario}' and ".$tabla.".aprobado<=0";
    try {$query = $db->query($sql);} catch(PDOException $e) {}
    $pendientes=0;
    if($query)
        while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
            $pendientes = intval($fila['pendientes']);
            // notificar("ebertunerg@gmail.com",intval($fila['pendientes']),$fila['pendientes']);
        }
        if($pendientes>0){
            $sql="select concat(nombres,' ',apellidos) as razonsocial,email from usuarios where usuarios.usuario='{$usuario}'";
            try {$query = $db->query($sql);} catch(PDOException $e) {}
            $destinatario="";
            $razonsocial="";
            if($query)
            {
                while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
                    $razonsocial = $fila['razonsocial'];
                    $destinatario = $fila['email'];
                // notificar("ebertunerg@gmail.com",$fila['razonsocial'],$pendientes);
                }
                if($destinatario!=""&&$razonsocial!=""){
                    notificar($destinatario,$razonsocial,$pendientes);
                }
            }
        }
    }

    $app->get("/aprobar/{archivoid}/{aprobado}", function($request, $response, $args) use($db, $app) { 
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
    $tabla=PREFIJO."aprobacion";
    $where="`archivoid`='".$args["archivoid"]."' AND `usuario`='{$usuario}'"; 
    $sql="UPDATE `{$tabla}` SET `aprobado`='".$args["aprobado"]."', `fecha`=NOW()";
    $sql.=" WHERE {$where}";
    //   return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => $sql))
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
        ->withJson(array('success' => 'Documento actualizado'))
        ;
    } else {
        return $response
        ->withHeader('Content-type', 'application/json')
        ->withJson(array('error' => 'El documento no ha podido ser actualizado, vuelve a intentarlo'))
        ; 
    }
});

    /* V I G E N C I A */
    $app->get("/vigencia/[{vigenciaid}]", function($request, $response, $args) use($db, $app) { 
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

    $tabla=PREFIJO."vigencia";
    $where="1";
    if(isset($args['vigenciaid']))
        $where="`vigenciaid`='".$args["vigenciaid"]."'"; 
    $sql="SELECT `vigenciaid`, `archivoid`, `fdesde`, `fhasta` FROM `$tabla` ";
    $sql.=" WHERE {$where}";
    
    
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
        ;
    });
    $app->delete('/vigencia/{vigenciaid}', function ($request, $response, $args) use($db, $app) {
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

    $tabla=PREFIJO."vigencia";

    $sql="DELETE FROM `{$tabla}` WHERE `{$tabla}`.`vigenciaid`='{$args['vigenciaid']}'";

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
    $app->put("/vigencia", function($request, $response, $args) use($db, $app) { 
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

    $tabla=PREFIJO."vigencia";
    
    $sql="INSERT INTO `{$tabla}`(`archivoid`, `fdesde`, `fhasta`) VALUES (`{$model['archivoid']}`, `{$model['fdesde']}`, `{$model['fhasta']}`)";
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

    /* R E V I S I O N E S */
    $app->get("/revision/[{archivoid}]", function($request, $response, $args) use($db, $app) { 
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
    $tabla=PREFIJO."revision";
    $where="1";
    if(isset($args['archivoid'])){
        $where="`archivoid`='{$args["archivoid"]}'"; 
    }
    $sql="SELECT `revisionid`, `revisado`, `archivoid`, `{$tabla}`.`usuario`, `fecha`, concat(`usuarios`.`nombres`,' ',`usuarios`.`apellidos`) as usuarionombre FROM `{$tabla}` ";
    $sql.="LEFT JOIN `usuarios` ON `usuarios`.`usuario`=`{$tabla}`.`usuario`";
    $sql.=" WHERE {$where}";
    //   return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => $sql))
    //         ; 
    
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
            // $fila['revisado']=($model["revisado"]==="1");
            $data[] = $fila;
        }

        return $response
        ->withHeader('Content-type', 'application/json')
        ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
            // ->withJson(array("status" => "success", "data" => $data))
        ;
    });
    $app->delete('/revision/{revisionid}', function ($request, $response, $args) use($db, $app) {
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

    $tabla=PREFIJO."revision";

    $sql="DELETE FROM `{$tabla}` WHERE `{$tabla}`.`revisionid`='{$args['revisionid']}' ";
    //    $sql+="AND archivoid in (SELECT `archivoid` from `".PREFIJO."archivo` where usuario='".$usuario."')";
    //  return $response
    //             ->withHeader('Content-type', 'application/json')
    //             ->withJson(array('error' => $sql))
    //             ; 
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
    $app->put("/revision", function($request, $response, $args) use($db, $app) { 
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
    
    $model = json_decode($json['json'],true)["model"];

    $tabla=PREFIJO."revision";
    $model['revisado']=($model["revisado"]==="1")?"1":"0";

    $sql="INSERT INTO `{$tabla}`(`revisionid`, `revisado`, `archivoid`, `usuario`, `fecha`) VALUES ('{$model['revisionid']}', '{$model['revisado']}', '{$model['archivoid']}', '{$model['usuario']}', '{$model['fecha']}')";
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
        notificarAprobacion($db,$model['usuario']);
        // notificar("ebertunerg@gmail.com","Ebert Zerpa",10);
        // mailing("asunto","cuerpoHTML","cuerpoTXT","ebertunerg@gmail.com","Ebert Zerpa");
        return $response
        ->withHeader('Content-type', 'application/json')
        ->withJson(array('success' => 'Registro procesado'))
        ;
    } else {
        return $response
        ->withHeader('Content-type', 'application/json')
        ->withJson(array('error' => 'El registro no ha podido ser procesado, vuelve a intentarlo'))
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
    /* Aprobar Revisión */
    $app->get("/revisar/{archivoid}/{revisado}", function($request, $response, $args) use($db, $app) { 
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
    $tabla=PREFIJO."revision";
    $where="`archivoid`='".$args["archivoid"]."' AND `usuario`='{$usuario}'"; 
    $sql="UPDATE `{$tabla}` SET `revisado`='".$args["revisado"]."', `fecha`=NOW()";
    $sql.=" WHERE {$where}";
    //   return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => $sql))
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
        ->withJson(array('success' => 'Documento actualizado'))
        ;
    } else {
        return $response
        ->withHeader('Content-type', 'application/json')
        ->withJson(array('error' => 'El documento no ha podido ser actualizado, vuelve a intentarlo'))
        ; 
    }
});
    $app->run();