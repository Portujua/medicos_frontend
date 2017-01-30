<?php
	trait Utils {
        public function check_cedula_paciente($post)
        {
            $query = $this->db->prepare("
                select *
                from Paciente
                where cedula=:ci
            ");

            $query->execute(array(
                ":ci" => $post['val']
            ));

            $json = array();
            $json['existe'] = $query->rowCount() > 0 ? true : false;
            $json['esValido'] = $query->rowCount() == 0 ? true : false;

            return json_encode($json);
        }

        public function check_usuario($post)
        {
            $query = $this->db->prepare("
                select *
                from Medico
                where usuario=:usuario
            ");

            $query->execute(array(
                ":usuario" => $post['val']
            ));

            $json = array();
            $json['existe'] = $query->rowCount() > 0 ? true : false;
            $json['esValido'] = $query->rowCount() == 0 ? true : false;

            return json_encode($json);
        }

		public function obtener_lugar($post, $lugarfull = array("nombre_completo" => "", "id" => -1, "nombre" => ""), $json = true)
        {
            $query = null;
            $lugar = null;

            if ($lugarfull['id'] == -1)
                $lugarfull['id'] = $post['lid'];

            if (!isset($post['fk']))
            {
                $query = $this->db->prepare("
                    select *
                    from Lugar
                    where id=:lid
                ");

                $query->execute(array(
                    ":lid" => $post['lid']
                ));

                if ($query->rowCount() == 0)
                    return json_encode(array(
                        "nombre_completo" => "Error, lugar inexistente.",
                        "nombre" => "Error, lugar inexistente.",
                        "id" => -1
                    ));
            }
            else
            {
                $query = $this->db->prepare("
                    select *
                    from Lugar
                    where id=:fk
                ");

                $query->execute(array(
                    ":fk" => $post['fk']
                ));
            }

            $lugar = $query->fetchAll();
            $lugar = $lugar[0];

            if (strlen($lugarfull['nombre']) == 0)
                $lugarfull['nombre'] = ucwords(strtolower($lugar['nombre']));

            $lugarfull['nombre_completo'] .= 
                (strlen($lugarfull['nombre_completo']) > 0 ? ", " : "") . 
                ($lugar['tipo'] == "municipio" ? "Municipio " : "") . 
                ucwords(strtolower($lugar['nombre']));

            if ($lugar['lugar'] != null)
            {
                $rec = $this->obtener_lugar(array("fk" => $lugar['lugar'], "lid" => $post['lid']), $lugarfull);

                if (!isset($post['fk']) && $json)
                    return json_encode($rec);
                else
                    return $rec;
            }
            else
                return $lugarfull;
        }

        function sendEmail($email)
        {
            $json = array();

            // Chequeo que sea apto
            $query = $this->db->prepare("
                select email
                from Medico
                where usuario=:usuario
            ");

            $query->execute(array(
                ":usuario" => $email['usuario']
            ));

            if ($query->rowCount() > 0)
            {
                $email['para'] = $query->fetchAll();
                $email['para'] = $email['para'][0];

                if ($email['para']['email'] != null)
                    $email['para'] = $email['para']['email'];
                else
                {
                    $json['error'] = 1;
                    $json['msg'] = "Error, este usuario no posee un correo electrónico asignado.";
                    return json_encode($json);
                }
            }
            else
            {
                $json['error'] = 1;
                $json['msg'] = "Error, este usuario no existe.";
                return json_encode($json);
            }

            // Envio el correo
            require "PHPMailer/PHPMailerAutoload.php";
            
            $mail = new PHPMailer;

            //$mail->isSMTP();
            $mail->Timeout = 3;
            $mail->SMTPDebug = 1;
            $mail->Host = 'mail.ejlorenzo.com.ve';
            $mail->SMTPAuth = true;
            $mail->Username = 'recuperar@ejlorenzo.com.ve';
            $mail->Password = '21115476';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 465; // 587

            $mail->From = 'recuperar@ejlorenzo.com.ve';
            $mail->FromName = 'Contacto';
            $mail->addAddress($email['para'], $email['usuario']);

            $mail->isHTML(true);

            $mail->Subject = 'Recuperar contraseña';
            $mail->Body = '
                Para reestablecer su contraseña por favor acceda al siguiente enlace: <br><br><br>
                <a href="http://ejlorenzo.com.ve/medicos/#/recuperar/'.$email['usuario'].'" target="_blank">http://ejlorenzo.com.ve/medicos/#/recuperar/'.$email['usuario'].'</a>
            ';
            $mail->AltBody = '
                Para reestablecer su contraseña por favor acceda al siguiente enlace: 

                http://ejlorenzo.com.ve/medicos/#/recuperar/'.$email['usuario'].'
            ';

            if(!$mail->Send()) 
            {
                $json['error'] = 1;
                $json['msg'] = "Mailer Error: " . $mail->ErrorInfo;
                return json_encode($json);
            } else {
                // Setteo la variable
                $query = $this->db->prepare("
                    update Medico set cambiar_contrasena=1
                    where usuario=:usuario
                ");

                $query->execute(array(
                    ":usuario" => $email['usuario']
                ));

                $json['success'] = 1;
                $json['msg'] = "Correo de recuperación enviado con éxito.";
                return json_encode($json);
            }
        }

        public function csv_personas()
        {
            $csv = array();
            $csv[] = array("Nombre completo", "Cedula", "Telefonos", "Correo electronico", "Sexo", "Estado Civil", "Direccion", "Facebook", "Twitter", "Instagram");

            $data = json_decode($this->cargar_personas(array()), true);
            
            foreach ($data as $d)
            {
                $tlfs = "";

                foreach ($d['telefonos'] as $tlf)
                    $tlfs .= $tlf['numero'] . " (" . $tlf['tipo'] . ")\r\n";

                $csv[] = array(
                    $d['nombre_completo'],
                    $d['cedula'],
                    $tlfs,
                    $d['email'],
                    $d['sexo'],
                    $d['estado_civil'],
                    $d['direccion'] . ", " . $d['lugar_str'],
                    isset($d['facebook']) ? $d['facebook'] : "",
                    isset($d['twitter']) ? $d['twitter'] : "",
                    isset($d['instagram']) ? $d['instagram'] : ""
                );
            }

            return $csv;
        }

        public function csv_personas_root()
        {
            $csv = array();
            $csv[] = array("Nombre completo", "Cedula", "Fecha de nacimiento");

            $data = json_decode($this->cargar_personas(array()), true);
            
            foreach ($data as $d)
                $csv[] = array(
                    $d['nombre_completo'],
                    $d['cedula'],
                    $d['fecha_nacimiento']
                );

            return $csv;
        }
	}
?>