<?php

    /* 
    * Returns a copy of the base with the $opts values replaced
    */
    function _options($opts, $base) {
        $opts_ = [];

        foreach ($base as $key => $val) {
            $opts_[$key] = isset($opts[$key]) ? $opts[$key] : $val;
        }

        return $opts_;
    }

    /* 
    * Basic JSON response generator
    */
    function json_response($opts = []) {
        $base = [
            "status" => "ok",
            "msg" => "Success",
            "ok" => true,
            "error" => false
        ];

        return json_encode(_options($opts, $base));
    }

    function sendEmail($email = [])
    {
        $baseEmail = [
            "username" => "contacto@salazarseijas.com",
            "password" => "21115476",
            "timeout" => 5,
            "host" => "host.caracashosting40.com",
            "port" => 465,
            "smtpSecure" => "tls",
            // if "from" is not set then it will use "username"
            "fromName" => "Contacto",
            "isHTML" => true,
            "subject" => "This is a test",
            "body" => "Hi there, don't worry this is just a test.",
            "to" => null,
            "toName" => ""
        ];

        if (!isset($email["to"])) {
            echo "Mail sending requires you to specify 'to' value.";
            die();
        }

        require "PHPMailer/PHPMailerAutoload.php";

        $email = _options($email, $baseEmail);
        
        $mail = new PHPMailer;

        //$mail->isSMTP();
        $mail->Timeout = $email["timeout"];
        $mail->SMTPDebug = 1;
        $mail->Host = $email["host"];
        $mail->SMTPAuth = true;
        $mail->Username = $email["username"];
        $mail->Password = $email["password"];
        $mail->SMTPSecure = $email["smtpSecure"];
        $mail->Port = $email["port"];

        $mail->From = isset($email["from"]) ? $email["from"] : $email["username"];
        $mail->FromName = $email["fromName"];
        $mail->addAddress($email["to"], $email["toName"]);

        $mail->isHTML($email["isHTML"]);

        $mail->Subject = $email["subject"];
        $mail->Body = $email["body"];
        // $mail->AltBody = '
        //     El usuario '.$email['name'].' ha solicitado un reinicio de contraseña. Por favor use el siguiente enlace para concretar la petición: http://eidosconsultores.com/reportesgdd/resetear.php
        // ';

        if(!$mail->Send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
            return false;
        } else {
            echo "Message has been sent";
            return true;
        }
    }

    if (isset($_GET['testemail'])) {
        sendEmail(["to" => "ejlorenzo19@gmail.com"]);
    }

?>