<?php
require 'vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

function sendMailTest($linkInicioSesion,$usuario,$contrasena,$email,$nombres=''){
    $mail = new PHPMailer;

    //$mail->SMTPDebug = 3;                               // Enable verbose debug output
    if(true){
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'arauca.tepuyserver.net';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'ezerpa@dosisunitarias.com';                 // SMTP username
        $mail->Password = '201619duv';                           // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port =  465;                                    // TCP port to connect to
    }else{
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'ebertunerg@gmail.com';                 // SMTP username
        $mail->Password = '123Enclave.21978';                           // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port =  465;                                    // TCP port to connect to
    }
    $mail->setFrom('ezerpa@dosisunitarias.com', 'Gestion Documental');
    // $mail->setFrom('ebertunerg@gmail.com', 'Mailer');
    // $mail->addAddress('contacto@ixvenezuela.com.ve', 'Contacto IX Venezuela');     // Add a recipient
     $mail->addAddress($email, (trim($nombres)=="")?$usuario:$nombres);     // Add a recipient
    // $mail->addAddress('ezerpa@ixvenezuela.com.ve');               // Name is optional
    $mail->addReplyTo('ezerpa@dosisunitarias.com', 'Información');
    // $mail->addCC('ycarolm13@gmail.com');
    // $mail->addBCC('ezerpa@dosisunitarias.com');

    // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
    $mail->isHTML(true);                                  // Set email format to HTML
    // echo construirEmail($linkInicioSesion,$usuario,$contrasena);
    $mail->Subject = 'Gestion Documental';
    $mail->Body    = construirEmail($linkInicioSesion,$usuario,$contrasena);
    $mail->AltBody = 'Usuario:'.$usuario.", Contraseña: ".$contrasena;
    // return $mail->send();
    if(!$mail->send()) {
        // echo 'Message could not be sent.';
        // echo 'Mailer Error: ' . $mail->ErrorInfo;
        return $mail->ErrorInfo;
    } else {
        // echo 'Message has been sent';
        return "";
    }
}

function construirEmail($url,$razonsocial,$cantidad){
    return 
    '
    <div style="margin:0;padding:0;">
    <table style="border-collapse:collapse;width:310px;margin:0 auto" width="310" cellspacing="0" cellpadding="0" border="0" align="center">


    <tbody><tr style="page-break-before:always">
    <td id="m_-6623719781032598603firefox-logo" style="padding:20px 0" align="center">
        <img src="http://www.dosisunitarias.com/logo.png" alt="" width="128" height="auto">
    </td>
    </tr>

    <tr style="page-break-before:always">
    <td valign="top">
        <h1 style="font-family:sans-serif;font-weight:normal;margin:0 0 24px 0;text-align:center">Estimado(a) <b>'.$razonsocial.'</b></h1>
        <p style="font-family:sans-serif;font-size:14px;font-weight:normal;margin:0 0 24px 0;text-align:center">
            Notificaciones Gestion Documental Dosis Unitarias de Colombia.<br/>
            Este es un mensaje autogenerado de nuestro sistema de gestion de calidad para notificarle que <b>'.$cantidad.'</b> documentos necesitan su atencion<br/><br/>
            Todos los acentos fueron suprimidos para tener mayor compatibilidad con la diversidad de dispositivos.</p>
    </td>
    </tr>

    <tr height="50">
    <td valign="top" align="center">
        <table style="border-collapse:collapse;background-color:#62B284;border-radius:4px;height:50px;width:310px!important" width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
        <tbody><tr style="page-break-before:always">
            <td style="font-family:sans-serif;font-weight:normal;text-align:center;margin:0;color:#ffffff;font-size:20px;line-height:100%" valign="middle" align="center">
            
            <a href="'.$url.'" id="link" style="font-family:sans-serif;color:#fff;display:block;padding:15px;text-decoration:none;width:280px" target="_blank">Iniciar Sesi&oacute;n</a>
            
            </td>
        </tr>
        </tbody></table>
    </td>
    </tr>
    </tbody>
    </table>
</div>
    ';
}


function mailing($asunto,$cuerpoHTML,$cuerpoTXT,$destinatario,$razonsocial=''){
    $mail = new PHPMailer;

    //$mail->SMTPDebug = 3;                               // Enable verbose debug output
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'arauca.tepuyserver.net';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'documatic@dosisunitarias.com';                 // SMTP username
    $mail->Password = '123.qwerty';                           // SMTP password
    $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port =  465;                                    // TCP port to connect to
    $mail->setFrom('documatic@dosisunitarias.com', $asunto);
     $mail->addAddress($destinatario, (trim($razonsocial)=="")?"Estimado usuario":$razonsocial);     // Add a recipient
    $mail->addReplyTo('ezerpa@dosisunitarias.com', 'Información');
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = $asunto;
    $mail->Body    = $cuerpoHTML;
    //Definimos AltBody por si el destinatario del correo no admite email con formato html 
    $mail->AltBody = $cuerpoTXT;
    if(!$mail->send()) {
        return $mail->ErrorInfo;
    } else {
        return "";
    }
}

function notificar($destinatario,$razonsocial,$cantidad){
    $asunto='Notificaciones Gestion de Calidad';
    $cuerpoTXT='Estimado '.$razonsocial.', le notificamos de Dosis Unitarias de Colombia que '.$cantidad.' documentos necesitan de su atencion para la aprobacion de los mismos';
    $url='http://dosisunitarias.com/';
    $cuerpoHTML=construirEmail($url,$razonsocial,$cantidad);
    mailing($asunto,$cuerpoHTML,$cuerpoTXT,$destinatario,$razonsocial);
}

function notificarCambioClave($destinatario,$razonsocial,$clave){
    $asunto='Cambio de claves - Sistema de Gestion de Calidad';
    $cuerpoTXT='Estimado '.$razonsocial.', le notificamos de Dosis Unitarias de Colombia que su nueva clave para acceder a nuestro sistema es: '.$clave.'';
    $url='http://dosisunitarias.com/';
    $cuerpoHTML=$cuerpoTXT;//construirEmail($url,$razonsocial,$cantidad);
    mailing($asunto,$cuerpoHTML,$cuerpoTXT,$destinatario,$razonsocial);
}