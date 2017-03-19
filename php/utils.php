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

    function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);
        return $min + $rnd;
    }

    function getToken($length = 128)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[crypto_rand_secure(0, $max-1)];
        }

        return $token;
    }





    if (isset($_GET['testemail'])) {
        sendEmail(["to" => strlen($_GET['testemail']) > 0 ? $_GET['testemail'] : "ejlorenzo19@gmail.com"]);
    }

    if (isset($_GET['testtoken'])) {
        echo getToken(strlen($_GET['testtoken']) > 0 ? intval($_GET['testtoken']) : 128);
    }

?>