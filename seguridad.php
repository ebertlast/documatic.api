<?php

require 'vendor/autoload.php';

require 'tokens.php';
require 'conexdb.php';
require 'autenticarDocs.php';
/*Intento de capturar los errores de SQL o PHP*/
/*
$container = $app->getContainer();
$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        // retrieve logger from $container here and log the error
        $response->getBody()->rewind();
        return $response->withStatus(500)
                        ->withHeader('Content-Type', 'text/html')
                        ->write("Oops, something's gone wrong!");
    };
};
$container['phpErrorHandler'] = function ($container) {
    return function ($request, $response, $error) use ($container) {
        // retrieve logger from $container here and log the error
        $response->getBody()->rewind();
        return $response->withStatus(500)
                        ->withHeader('Content-Type', 'text/html')
                        ->write("Oops, something's gone wrong!");
    };
};

// $container['phpErrorHandler'] = function ($container) {
//     return $container['errorHandler'];
// };

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting, so ignore it
        return;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});
*/
/*FIN Intento de capturar los errores de SQL o PHP*/

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


/* U S U A R I O S */
//http://localhost:8082/Angular/unidosis/api/seguridad.php/prueba
$app->get("/prueba", function($request, $response, $args) use($db, $app) {
	//require_once('fpdf/fpdf.php');
	//require_once('fpdi/fpdi.php');
	//$pdf1 = new Zend_Pdf();
	/*
	$pdf = new FPDI();
	$pdf->AddPage(); 
	$pdf->setSourceFile('uploads/Octubre - Cliente TOMAS ZERPA.pdf'); 
	$tplIdx = $pdf->importPage(1); 
	//$pdf->Output('uploads/gift_coupon_generated.pdf', 'D');
	$pdf->Output('uploads/prueba.pdf','F');
	*/
	/*
	$pdf = new FPDF();
	$file="uploads/Octubre - Cliente TOMAS ZERPA.pdf";
	$pdf->AddPage('uploads/gift_coupon_generated.pdf');
	//$pdf->setSourceFile('uploads/gift_coupon_generated.pdf'); 
	$pdf->SetFont('Arial','B',16);
	$pdf->Cell(80,10,'Ebert!');
	$pdf->Output('uploads/gift_coupon_generated.pdf','F');
	*/
	
	
	
	/*
	// initiate FPDI
	$pdf = new FPDI();
	// add a page
	$pdf->AddPage();
	// set the source file
	$pdf->setSourceFile("uploads/Octubre - Cliente TOMAS ZERPA.pdf");
	// import page 1
	$tplIdx = $pdf->importPage(1);
	// use the imported page and place it at point 10,10 with a width of 100 mm
	$pdf->useTemplate($tplIdx, 10, 10, 100);

	// now write some text above the imported page
	$pdf->SetFont('Helvetica');
	$pdf->SetTextColor(255, 0, 0);
	$pdf->SetXY(30, 30);
	$pdf->Write(0, 'This is just a simple text');
	// Insert a logo in the top-left corner at 300 dpi
	$pdf->Image('uploads/firmas/jvalera.png',10,10,-300);
	// Insert a dynamic image from a URL
	//$pdf->Image('http://chart.googleapis.com/chart?cht=p3&chd=t:60,40&chs=250x100&chl=Hello|World',60,30,90,0,'PNG');
	$pdf->Output('uploads/gift_coupon_generated.pdf','F');
	*/
	
    $firmas = array(
        0 => array('razonsocial' => 'Ebert Manuel Zerpa Figueroa','cargo'=>'Administrador','firma'=>'uploads/firmas/jvalera.png')
        ,1 => array('razonsocial' => 'Ebert Zerpa','cargo'=>'Tecnico','firma'=>'uploads/firmas/jvalera.png')
        ,2 => array('razonsocial' => 'Jose Pinero','cargo'=>'Supervisor','firma'=>'uploads/firmas/jvalera.png')
        ,3 => array('razonsocial' => 'Virinia Rojas','cargo'=>'Quimico','firma'=>'uploads/firmas/vrojas.png')
       // ,4 => array('razonsocial' => 'Armando Martinez','cargo'=>'Cantante','firma'=>'uploads/firmas/jvalera.png')
        );

    $firmas = array();
	$pdfOrigen="uploads/prueba2.pdf";
    $pdfDestino="uploads/prueba.pdf";

    firmar($firmas,$pdfOrigen,$pdfDestino,TRUE);
    
    
    $dataJson = array();
    $dataJson['usuario']='ezerpa';
    $dataJson['clave']='enclave';

    $sql="SELECT `usuario`,`email`,`masculino`,`fechanacimiento`,`activo`,`fecharegistro`,`nombres`,`apellidos`,`avatar` FROM `usuarios` where 1";
    // return $response->withStatus(302)->withHeader('Location', 'www.google.com.ve');
    try {
        $query = $db->query($sql);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}'; 
        // set_error_handler();
    }
    
    $data = array();
    
    if($query)
    {
         while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
            $fila["activo"]=($fila["activo"]==="1");
            $data[] = $fila;
        }
    }
    $result = array("status" => "success", "data" => $data, "token" => newToken($dataJson));
    // echo json_encode($result);return;
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withJson($result)
        ;
});

$app->get("/pruebatoken", function($request, $response, $args) use($db, $app) {
    $jwt='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE0OTI5ODYxOTcsImV4cCI6MTQ5Mjk4OTc5NywiZGF0YSI6eyJ1c3VhcmlvIjoiZXplcnBhIiwiY2xhdmUiOiJlbmNsYXZlIn19.NZQiYNwCmur8FRsHUrBwIRY9gFrUT53KS4VR3RN5TPA';
    // JWT::$leeway = 60; 
    // try{
    //     $decoded = JWT::decode($jwt, API_SECRET_KEY, array('HS256'));
    //     $data = $decoded->data;
        
    // } catch (Exception $e) {
    //     $bodyError = array('error' => $e->getMessage() );
    //     return $response
    //         ->withStatus(401)
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson($bodyError)
    //         ;
    // }
    $data=getToken($jwt);
    // var_dump($data);
    if(array_key_exists('error', $data)){
       return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson($data)
            ;
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withJson($data)
        ;
});

//http://localhost:8082/Angular/unidosis/api/seguridad.php/authenticar
$app->post("/autenticar", function($request, $response, $args) use($db, $app) {
    $json = $request->getParsedBody();
    $data = json_decode($json['json'],true);
     
    $usuario = $data["usuario"];
    $clave = $data["clave"];
     
    // $usuario = "ezerpa";
    // $clave = "enclave";
    // $usuario=$request->getParam('usuario');
    
    $sql="SELECT `usuario`,`email`,`masculino`,`fechanacimiento`,`activo`,`fecharegistro`,`nombres`,`apellidos`,`avatar` FROM usuarios where (usuario = '{$usuario}' or email = '{$usuario}') AND MD5('{$clave}')=clave;";
    $sql="SELECT `usuario`, `email`, `masculino`, `fechanacimiento`, `usuarios`.`activo`, `fecharegistro`, `nombres`, `apellidos`, `avatar`, `usuarios`.`perfilid`,`perfiles`.`denominacion` as 'perfil',`usuarios`.`firma` FROM `usuarios` LEFT JOIN `perfiles` ON `usuarios`.`perfilid`=`perfiles`.`perfilid` WHERE (`usuarios`.`usuario` = '{$usuario}' or `usuarios`.`email` = '{$usuario}') AND MD5('{$clave}')=`usuarios`.`clave`;";
    // var_dump($sql);
    try {
        $query = $db->query($sql);
    } catch(PDOException $e) {
        // echo '{"error":{"text":'. $e->getMessage() .'}}'; 
         return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage(),'sql'=>$sql))
            ;

    }
    
    $data = array();
    if($query)
    {
         while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
             if(!($fila["activo"]==="1"))
                return $response
                ->withHeader('Content-type', 'application/json')
                ->withJson(array('error' => 'Usuario inactivo. Contacta con nosotros a través de nuestro correo contacto@ixvenezuela.com.ve'))
                ;
            $fila["activo"]=($fila["activo"]==="1");
           
            $data[] = $fila;
        }
    }
  
    if(count($data)>0)
    {
        //$data = JWT::decode($jwt, $key, array('HS256'));
        /*01/05/2017 Para manejar permisos en los guardias de rutas*/

        if(false)
        {
            $sql="CALL getPermisos('{$usuario}');";
            try {
                $query = $db->query($sql);
            } catch(PDOException $e) {
                // echo '{"error":{"text":'. $e->getMessage() .'}}'; 
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withJson(array('error' => $e->getMessage(),'sql'=>$sql))
                    ;
            }
            $permisos = array();
            if($query){
                while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
                    $permisos[] = $fila;
                    // $data[0]['permisos'];
                    // array_push($data[0]['permisos'],$fila);
                }
            }
            $data[0]['permisos']=$permisos;
        }
        /* fin de permisos*/

        $dataJson = array('usuario'=>$usuario,'clave'=>$clave);
        $data[0]["token"] = newToken($dataJson);
        // $result = array("status" => "success", "data" => $data, "token" => newToken($dataJson));
        $result = array("status" => "success", "data" => $data);
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson($result)
            ;
    }else{
         return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'Usuario y contraseña no concuerdan.'))
            ;
    }
    // echo json_encode($result);
});

$app->post("/check", function($request, $response, $args) use($db, $app) {
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
   
    $json = $request->getParsedBody();
    $ruta = json_decode($json['json'],true)['ruta'];

     
    $sql="SELECT count(rutaid) as activo FROM rutasperfil WHERE rutaid='{$ruta}' and perfilid=(SELECT perfilid FROM usuarios WHERE (usuario='{$usuario}' or email='{$usuario}') and clave=md5('{$clave}') and activo=1 limit 1)";

    try {
        $query = $db->query($sql);
    } catch(PDOException $e) {
         return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage(),'sql'=>$sql))
            ;

    }
    
    if($query)
    {
         while( $fila = $query->fetch(PDO::FETCH_ASSOC) ) {
             if(($fila["activo"]==="1"))
                return $response
                ->withHeader('Content-type', 'application/json')
                ->withJson(array('success' => 'Usuario verificado.'))
                ;
        }
    }
    return $response
                ->withHeader('Content-type', 'application/json')
                ->withJson(array('error' => 'No posee los privilegios necesarios para acceder al recurso solicitado. Contacto con nosotros para mayor información.'))
                ;
    // echo json_encode($result);
});
/** Devuelve todos los perfiles */
//http://localhost:8082/Angular/unidosis/api/seguridad.php/perfiles
$app->get("/perfiles", function($request, $response, $args) use($db, $app) { 
    if (!$request->hasHeader('Authorization')) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'Token no encontrado en la solicitud.'))
            ;
    }
    $jwt=explode(" ",$request->getHeaderLine('Authorization'))[1];
    /**/
    // JWT::$leeway = 60; 
    // try{
    //     // $decoded = JWT::decode($jwt, API_SECRET_KEY, array('HS256'));

    // } catch (Exception $e) {
    //     $bodyError = array('error' => $e->getMessage() );
    //    return $response
    //         ->withStatus(401)
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson($bodyError)
    //         ;
    // }
    // $dataUser
    /**/
    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(getToken($jwt)->clave)
    //         ;
    $dataUser=getToken($jwt);
    // if(isset($dataUser->error)){
    // if(isset($dataUser->error)||isset($dataUser["error"])){
    if(array_key_exists('error', $dataUser)){
       return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson($dataUser)
            ;
    }

    
    $usuario=$dataUser->usuario;
    $clave=$dataUser->clave;
   
    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson($usuario)
    //         ;


    if(!$usuario){
        return $response
            ->withStatus(401)
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'No hemos podido identificarte, intenta volver a iniciar sesión'))
            ;
    }else{
        
        // if(!this.activo($usuario))
            // return $response
            //     ->withStatus(401)
            //     ->withHeader('Content-type', 'application/json')
            //     ->withJson(array("error" => "Usted se encuentra inactivo"))
            //     ;
    }


    $sql="SELECT `perfilid`, `denominacion`, `activo`, `fechacreado` FROM `perfiles` WHERE 1";
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
            $fila["activo"]=($fila["activo"]==="1");
            $data[] = $fila;
        }
    
    // $token = newToken($dataUser);
	// $result = array("status" => "success", "data" => $data, "token"=>$token);
	// echo json_encode($result);
    return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
            ;
});

/**Obtiene un perfil con el id pasado como parametro por el GET */
//http://localhost:8082/Angular/unidosis/api/seguridad.php/perfil/admin
$app->get("/perfil/{perfilid}", function($request, $response, $args) use($db, $app) { 
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

    $perfilid=$args['perfilid'];
    $sql="SELECT `perfilid`, `denominacion`, `activo`, `fechacreado` FROM `perfiles` WHERE `perfilid`='{$perfilid}'";
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
            $fila["activo"]=($fila["activo"]==="1");
            $data[] = $fila;
        }
    
    // $token = newToken($dataUser);
	// $result = array("status" => "success", "data" => $data[0], "token"=>$token);
	// echo json_encode($result);
    return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array("status" => "success", "data" => $data[0], "token"=>newToken($dataUser)))
            ;

});

/** Actualiza un perfil*/
//http://localhost:8082/Angular/unidosis/api/seguridad.php/perfil/admin
$app->post("/perfil/{perfilid}", function($request, $response, $args) use($db, $app) { 
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

    $perfilidOLD=$args['perfilid'];

    $json = $request->getParsedBody();
    $perfil = json_decode($json['json'],true)["perfil"];
     
    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => json_encode((int)$perfil["activo"])))
    //         ;


    $activo=(int)$perfil['activo'];
    $sql="UPDATE `perfiles` SET `perfilid`='{$perfil["perfilid"]}',`denominacion`='{$perfil["denominacion"]}',`activo`={$activo} WHERE `perfilid`='{$perfilidOLD}'";

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
            ->withJson(array('success' => 'Datos del perfil actualizados'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El perfil no ha podido ser actualizado, vuelve a intentarlo'))
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

//http://localhost:8082/Angular/unidosis/api/seguridad.php/usuariosenperfil/admin
$app->get("/usuariosenperfil/{perfilid}", function($request, $response, $args) use($db, $app) { 
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


    $sql="SELECT `usuario`, `clave`, `email`, `masculino`, `fechanacimiento`, `activo`, `fecharegistro`, `nombres`, `apellidos`, `avatar`, `perfilid` FROM `usuarios` WHERE `perfilid`='{$args['perfilid']}'";
    $sql="SELECT `usuario`, `clave`, `email`, `masculino`, `fechanacimiento`, `usuarios`.`activo`, `fecharegistro`, `nombres`, `apellidos`, `avatar`, `usuarios`.`perfilid`,`perfiles`.`denominacion` as 'perfil' FROM `usuarios` LEFT JOIN `perfiles` ON `usuarios`.`perfilid`=`perfiles`.`perfilid` WHERE `usuarios`.`perfilid`='{$args['perfilid']}';";
    
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
            $fila["activo"]=($fila["activo"]==="1");
            $fila["masculino"]=($fila["masculino"]==="1");
            $data[] = $fila;
        }
    
    // $token = newToken($dataUser);
	// $result = array("status" => "success", "data" => $data, "token"=>$token);
	// echo json_encode($result);
    return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
            ;
});
//http://localhost:8082/Angular/unidosis/api/seguridad.php/usuariosfueraperfil/admin
$app->get("/usuariosfueraperfil/{perfilid}", function($request, $response, $args) use($db, $app) { 
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


    $sql="SELECT `usuario`, `clave`, `email`, `masculino`, `fechanacimiento`, `usuarios`.`activo`, `fecharegistro`, `nombres`, `apellidos`, `avatar`, `usuarios`.`perfilid`,`perfiles`.`denominacion` as 'perfil' FROM `usuarios` LEFT JOIN `perfiles` ON `usuarios`.`perfilid`=`perfiles`.`perfilid` WHERE IFNULL(`usuarios`.`perfilid`,'')<>'{$args['perfilid']}';";
    
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
            $fila["activo"]=($fila["activo"]==="1");
            $fila["masculino"]=($fila["masculino"]==="1");
            $data[] = $fila;
        }
    
    // $token = newToken($dataUser);
	// $result = array("status" => "success", "data" => $data, "token"=>$token);
	// echo json_encode($result);
    return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
            ;
});
//http://localhost:8082/Angular/unidosis/api/seguridad.php/usuariosenperfil/admin
$app->get("/usuarios", function($request, $response, $args) use($db, $app) { 
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


    $sql="SELECT `usuario`, `email`, `masculino`, `fechanacimiento`, `usuarios`.`activo`, `fecharegistro`, `nombres`, `apellidos`, `avatar`, `usuarios`.`perfilid`,`perfiles`.`denominacion` as 'perfil',`usuarios`.`firma` FROM `usuarios` LEFT JOIN `perfiles` ON `usuarios`.`perfilid`=`perfiles`.`perfilid` WHERE 1;";
    
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
            $fila["activo"]=($fila["activo"]==="1");
            $fila["masculino"]=($fila["masculino"]==="1");
            $data[] = $fila;
        }
    
    // $token = newToken($dataUser);
	// $result = array("status" => "success", "data" => $data, "token"=>$token);
	// echo json_encode($result);
    return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
            ;
});
//http://localhost:8082/Angular/unidosis/api/seguridad.php/usuario/ezerpa
$app->get("/usuario/{usuario}", function($request, $response, $args) use($db, $app) { 
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

    
    $sql="SELECT `usuario`, `email`, `masculino`, `fechanacimiento`, `usuarios`.`activo`, `fecharegistro`, `nombres`, `apellidos`, `avatar`, `usuarios`.`perfilid`,`perfiles`.`denominacion` as 'perfil', `usuarios`.`firma` FROM `usuarios` LEFT JOIN `perfiles` ON `usuarios`.`perfilid`=`perfiles`.`perfilid` WHERE '{$args['usuario']}' IN (`usuarios`.`usuario`,`usuarios`.`email`);";
    try {
        $query = $db->query($sql);
    } catch(PDOException $e) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage()))
            ; 
    }
    $data = array();
    if($query){
        $fila = $query->fetch(PDO::FETCH_ASSOC);
        if($fila)
            $data = $fila;
    }
    
    return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
            ;

});

$app->post('/firma-upload', function ($request, $response, $args) {
    $files = $request->getUploadedFiles();
    if (empty($files['files'])) {
        return $response
            ->withStatus(412)
            ->withHeader('Content-type', 'application/json')
            // ->withJson(array('error' => (empty($files['files']))))
            ->withJson(array('error' => 'Ningún fichero encontrado en la solicitud'))
        ; 
    }
    // $allPostPutVars = $request->getParsedBody();
    // foreach (json_decode($allPostPutVars) as $key => $value) {
    //     $uploadFileName.=$value;
    // }

    $newfile = $files['files'];
    //$uploadFileName=is_array($allPostPutVars);//date("Ymd_His_");
    //var_dump($body);   
    $usuario=($request->getParsedBody()['usuario']);
    $uploadFileName=$usuario;   
    $fn=$newfile->getClientFilename();
    $uploadFileName.=substr($fn,strrpos($fn, '.'),strlen($fn));
    if ($newfile->getError() === UPLOAD_ERR_OK) {
        // $uploadFileName .= $newfile->getClientFilename();
        $newfile->moveTo("./uploads/firmas/$uploadFileName");        

    }else{
        return $response
            ->withStatus(500)
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => "Documento no ha podido ser subido a nuestro servidor"))
        ; 
    }  

    // $url="./uploads/firmas/".$uploadFileName;
    // $sql="UPDATE `usuarios` SET `firma`='{$uploadFileName}' WHERE `usuarios`.`usuario`='{$usuario}'";
    // try {
        // $update = $db->query($sql);
    // } catch(PDOException $e) {
    //     return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => $e->getMessage()))
    //         ; 
    // }

    $data = array('filename' => $uploadFileName);
    return $response
            // ->withStatus(500)
            ->withHeader('Content-type', 'application/json')
            // ->withJson(array('error' => (empty($files['files']))))
            ->withJson(array('status' => 'success','data'=>$data))
        ; 
    
   
});
/**
* Registrar nuevos usuarios
*/
//http://localhost:8082/Angular/unidosis/api/seguridad.php/usuario
$app->post("/usuario", function($request, $response, $args) use($db, $app) { 
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
    $usuario = json_decode($json['json'],true)["usuario"];
     
    $activo=(int)$usuario['activo'];
    $masculino=(int)$usuario['masculino'];
    $usuario["nombres"]=ucwords($usuario["nombres"]);
    $usuario["apellidos"]=ucwords($usuario["apellidos"]);
    $fechanacimiento = date("Y-m-d", strtotime($usuario["fechanacimiento"]));
    $sql="INSERT INTO `usuarios`(`usuario`, `clave`, `email`, `masculino`, `fechanacimiento`, `activo`, `nombres`, `apellidos`, `avatar`, `perfilid`) VALUES ('{$usuario['usuario']}',MD5('{$usuario['clave']}'),'{$usuario['email']}',{$masculino},'{$fechanacimiento}',{$activo},'{$usuario['nombres']}','{$usuario['apellidos']}','{$usuario['avatar']}','{$usuario['perfilid']}')";
    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => json_encode($sql)))
    //         ;

    try {
        $insert = $db->query($sql);
    } catch(PDOException $e) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage()))
            ; 
    }

	if ($insert) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('success' => 'Usuario registrado satisfactoriamente'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El usuario no ha podido ser registrado en la base de datos, vuelve a intentarlo'))
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

/** Actualiza un usuario*/
//http://localhost:8082/Angular/unidosis/api/seguridad.php/usuario/ezerpa
$app->post("/usuario/{usuarioOLD}", function($request, $response, $args) use($db, $app) { 
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

    $usuarioOLD=$args['usuarioOLD'];

    $json = $request->getParsedBody();
    $modelo = json_decode($json['json'],true)["usuario"];
     
    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => json_encode((int)$perfil["activo"])))
    //         ;


    $activo=(int)$modelo['activo'];
    $masculino=(int)$modelo['masculino'];
    $modelo["nombres"]=ucwords($modelo["nombres"]);
    $modelo["apellidos"]=ucwords($modelo["apellidos"]);
    $fechanacimiento = date("Y-m-d", strtotime($modelo["fechanacimiento"]));
    $sql="UPDATE `usuarios` SET `usuario`='{$modelo['usuario']}',`clave`=MD5('{$modelo['clave']}'),`email`='{$modelo['email']}',`masculino`={$masculino},`fechanacimiento`='{$fechanacimiento}',`activo`={$activo},`nombres`='{$modelo['nombres']}',`apellidos`='{$modelo['apellidos']}',`avatar`='{$modelo['avatar']}',`perfilid`='{$modelo['perfilid']}',`firma`='{$modelo['firma']}' WHERE `usuarios`.`usuario`='{$usuarioOLD}'";
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
            ->withJson(array('success' => 'Datos del perfil actualizados'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El perfil no ha podido ser actualizado, vuelve a intentarlo'))
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
//http://localhost:8082/Angular/unidosis/api/seguridad.php/usuario/ezerpa
$app->post("/setfirma", function($request, $response, $args) use($db, $app) { 
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
    $modelo = json_decode($json['json'],true)["usuario"];
     
    $sql="UPDATE `usuarios` SET `firma`='{$modelo['firma']}' WHERE `usuarios`.`usuario`='{$modelo['usuario']}'";


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
            ->withJson(array('success' => 'Firma actualizada'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'La firma no ha podido ser actualizada, vuelve a intentarlo'))
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
//http://localhost:8082/Angular/unidosis/api/seguridad.php/asignarperfil/ezerpa/admin/
$app->get("/asignarperfil/{usuario}/{perfilid}", function($request, $response, $args) use($db, $app) { 
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


    $sql="UPDATE `usuarios` SET `usuarios`.`perfilid`='{$args['perfilid']}' WHERE `usuarios`.`usuario`='{$args['usuario']}';";
    
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
            ->withJson(array('success' => 'Perfil asignado al usuario'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El perfil no ha podido ser asignado, vuelve a intentarlo'))
            ; 
	}
});

/** Coloca el perfil de un usuario en NULL*/
//http://localhost:8082/Angular/unidosis/api/seguridad.php/quitarperfil/ezerpa/admin/
$app->get("/quitarperfil/{usuario}", function($request, $response, $args) use($db, $app) { 
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


    $sql="UPDATE `usuarios` SET `usuarios`.`perfilid`=NULL WHERE `usuarios`.`usuario`='{$args['usuario']}';";
    
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
            ->withJson(array('success' => 'Usuario desvinculado del Perfil'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El perfil no ha podido ser desvinculado, vuelve a intentarlo'))
            ; 
	}
});

/** Elimina un perfil */
//http://localhost:8082/Angular/unidosis/api/seguridad.php/eliminarperfil/admin
$app->get("/eliminarperfil/{perfilid}", function($request, $response, $args) use($db, $app) { 
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
     
   $sql="DELETE FROM `perfiles` WHERE `perfiles`.`perfilid`='{$args['perfilid']}'";
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
            ->withJson(array('success' => 'Perfil eliminado', "token"=>newToken($dataUser)))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El perfil no ha podido ser eliminado, vuelve a intentarlo'))
            ; 
	}
    
    
    
});

//http://localhost:8082/Angular/unidosis/api/seguridad.php/agregarrutaaperfil/admin/seguridad
$app->get("/agregarrutaaperfil/{perfilid}/{rutaid}", function($request, $response, $args) use($db, $app) { 
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

    $sql="INSERT INTO `rutasperfil`(`rutaid`, `perfilid`) ".
        "SELECT * FROM (SELECT  '{$args['rutaid']}','{$args['perfilid']}') AS tmp ".
        "WHERE NOT EXISTS ( ".
        "    SELECT `rutaid` FROM `rutasperfil` WHERE `rutaid`='{$args['rutaid']}' AND `perfilid`='{$args['perfilid']}' ".
        ") LIMIT 1;";
    //  return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => json_encode($sql)))
    //         ; 

    
   try {
        $insert = $db->query($sql);
    } catch(PDOException $e) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage()))
            ; 
    }
    
    if ($insert) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('success' => 'Permiso habilitado al perfil'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El permiso no ha podido habilitarse, vuelve a intentarlo'))
            ; 
    }
});

/**
* Elimina una ruta para que el usuario que este asociado a ese perfil no pueda acceder 
*/
//http://localhost:8082/Angular/unidosis/api/seguridad.php/agregarrutaaperfil/admin/seguridad
$app->get("/quitarrutaaperfil/{perfilid}/{rutaid}", function($request, $response, $args) use($db, $app) { 
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

    $sql="DELETE FROM `rutasperfil` WHERE `rutaid`='{$args['rutaid']}' AND `perfilid`='{$args['perfilid']}'";
    //  return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => json_encode($sql)))
    //         ; 

    
   try {
        $insert = $db->query($sql);
    } catch(PDOException $e) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => $e->getMessage()))
            ; 
    }
    
    if ($insert) {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('success' => 'Permiso deshabilitado del perfil'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El permiso no ha podido desvincularse, vuelve a intentarlo'))
            ; 
    }
});

/** Activa o desactiva un usuario*/
//http://localhost:8082/Angular/unidosis/api/seguridad.php/quitarperfil/ezerpa/admin/
$app->get("/cambiarstatus/{usuario}/{status}", function($request, $response, $args) use($db, $app) { 
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


    $sql="UPDATE `usuarios` SET `usuarios`.`activo`={$args['status']} WHERE `usuarios`.`usuario`='{$args['usuario']}';";
    
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
            ->withJson(array('success' => 'Estado del usuario actualizado satisfactoriamente'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El estado del usuario no ha podido ser actualizado, vuelve a intentarlo'))
            ; 
	}
});

/** Obtiene todos los menues que tiene permitido ver un usuario */
//http://localhost:8082/Angular/unidosis/api/seguridad.php/menus/ezerpa
$app->get("/menues/{usuario}", function($request, $response, $args) use($db, $app) { 
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


    $sql="SELECT `menuid`, `label`, `iconfa`, `orden`, `activo`, `ruta`, `resalte`, `tiporesalte`, `submenuid` ,case when(select count(*) from `menu` as `m` where `m`.`submenuid`=`menu`.`menuid`)>0 then 1 else 0 end as `hijos` FROM `menu` WHERE `menu`.`activo`=1 ";
    $sql.=" ORDER BY `menu`.`submenuid`,`menu`.`orden`";
    $sql="CALL getMenues('{$args['usuario']}');";
    
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
            $fila["activo"]=($fila["activo"]==="1");
            $fila["hijos"]=($fila["hijos"]==="1");
            $data[] = $fila;
        }
    
    // $token = newToken($dataUser);
	// $result = array("status" => "success", "data" => $data, "token"=>$token);
	// echo json_encode($result);
    return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
            ;
});


/** Todos los menues*/
//http://localhost:8082/Angular/unidosis/api/seguridad.php/menus/ezerpa
$app->get("/allmenues", function($request, $response, $args) use($db, $app) { 
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


    $sql="SELECT `menuid`, `label`, `iconfa`, `orden`, `activo`, `ruta`, `resalte`, `tiporesalte`, `submenuid` ,case when(select count(*) from `menu` as `m` where `m`.`submenuid`=`menu`.`menuid`)>0 then 1 else 0 end as `hijos` FROM `menu` WHERE 1 ";
    $sql.=" ORDER BY `menu`.`submenuid`,`menu`.`orden`";
    
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
            $fila["activo"]=($fila["activo"]==="1");
            $fila["hijos"]=($fila["hijos"]==="1");
            $data[] = $fila;
        }
    
    // $token = newToken($dataUser);
	// $result = array("status" => "success", "data" => $data, "token"=>$token);
	// echo json_encode($result);
    return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array("status" => "success", "data" => $data, "token"=>newToken($dataUser)))
            ;
});

/** Actualiza un menu*/
//http://localhost:8082/Angular/unidosis/api/seguridad.php/usuario/ezerpa
$app->post("/menu/{menuid}", function($request, $response, $args) use($db, $app) { 
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

    $menuid=$args['menuid'];

    $json = $request->getParsedBody();
    $modelo = json_decode($json['json'],true)["menu"];
     
    // return $response
    //         ->withHeader('Content-type', 'application/json')
    //         ->withJson(array('error' => json_encode((int)$perfil["activo"])))
    //         ;


    $modelo['activo']=(int)$modelo['activo'];
    $modelo["label"]=ucwords($modelo["label"]);
    $sql="UPDATE `menu` SET `menuid`='{$modelo['menuid']}',`label`='{$modelo['label']}',`iconfa`='{$modelo['iconfa']}',`orden`='{$modelo['orden']}',`activo`={$modelo['activo']},`ruta`='{$modelo['ruta']}',`resalte`='{$modelo['resalte']}',`tiporesalte`='{$modelo['tiporesalte']}',`submenuid`='{$modelo['submenuid']}' WHERE `menu`.`menuid`='{$args['menuid']}'";
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
            ->withJson(array('success' => 'Datos del menu actualizados'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El menu no ha podido ser actualizado, vuelve a intentarlo'))
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

/** Nuevo menu*/
//http://localhost:8082/Angular/unidosis/api/seguridad.php/usuario/ezerpa
$app->post("/menu", function($request, $response, $args) use($db, $app) { 
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
    $modelo = json_decode($json['json'],true)["menu"];
     
    $modelo['activo']=(int)$modelo['activo'];
    $sql="INSERT INTO `menu`(`menuid`, `label`, `iconfa`, `orden`, `activo`, `ruta`, `resalte`, `tiporesalte`, `submenuid`) VALUES ('{$modelo['menuid']}','{$modelo['label']}','{$modelo['iconfa']}',{$modelo['orden']},{$modelo['activo']},'{$modelo['ruta']}','{$modelo['resalte']}','{$modelo['tiporesalte']}','{$modelo['submenuid']}')";
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
            ->withJson(array('success' => 'Menu registrado satisfactoriamente'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El menu no ha podido ser registrado, vuelve a intentarlo'))
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
//http://localhost:8082/Angular/unidosis/api/seguridad.php/rutas
$app->get("/rutas", function($request, $response, $args) use($db, $app) { 
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

    $sql="SELECT `rutaid`, `ruta`, convert(`descripcion` USING ascii) as descripcion FROM `rutas` ";
    
    
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
            ->withJson(array("status" => "success", "data" => $data))
            ;
});
//http://localhost:8082/Angular/unidosis/api/seguridad.php/rutas
$app->get("/rutasenperfil/{perfilid}", function($request, $response, $args) use($db, $app) { 
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

    $sql="SELECT `rutaid`, `ruta`, convert(`descripcion` USING ascii) as descripcion FROM `rutas` WHERE `rutas`.`rutaid` IN (SELECT `rutasperfil`.`rutaid` FROM `rutasperfil` WHERE `rutasperfil`.`perfilid`='{$args['perfilid']}')";
    
    
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
            ->withJson(array("status" => "success", "data" => $data))
            ;
});

/** Elimina un menu */
//http://localhost:8082/Angular/unidosis/api/seguridad.php/eliminarmenu/seguridad
$app->get("/eliminarmenu/{menuid}", function($request, $response, $args) use($db, $app) { 
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
     
   $sql="DELETE FROM `menu` WHERE `menu`.`menuid`='{$args['menuid']}'";
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
            ->withJson(array('success' => 'Menu eliminado'))
            ;
	} else {
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withJson(array('error' => 'El menu no ha podido ser eliminado, vuelve a intentarlo'))
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
$app->run();