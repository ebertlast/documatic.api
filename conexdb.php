<?php
define("API_SECRET_KEY","36347");
$app = new \Slim\App();

function getConnection($tipoConexion="mysql"){
    //$tipoConexion="mssql";
    //$tipoConexion="mysql";
    $produccion=true;
    if($tipoConexion=="mysql"){
        $dbhost="localhost";
        $dbuser="root";
        $dbpass="";
        $dbname="dosimatic";
        if($produccion){
            $dbname="dosisun1_dosimatic";
            $dbuser="dosisun1_root";
            $dbpass="123.qwerty";
        }
        $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname",$dbuser,$dbpass);
        return $dbh;
    }else{
        $dbhost="VAIO";
        $dbuser="sa";
        $dbpass="123456";
        $dbname="K_MLF_2";
        $dbh = new PDO("odbc:Driver={SQL Server Native Client 11.0};Server=$dbhost;Database=$dbname; Uid=$dbuser;Pwd=$dbpass;");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh -> exec("SET NAMES 'utf8';");
        return $dbh;
    }
}



try {
    $db = getConnection();
} catch(PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}

