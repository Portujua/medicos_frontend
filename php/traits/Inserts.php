<?php
	trait Inserts {
        abstract public function run($query, $opts = []);

		public function post_agregar_paciente($post)
        {
            $this->run("
                insert into Paciente (nombre, segundo_nombre, apellido, segundo_apellido, cedula, email, usuario, contrasena, fecha_nacimiento, fecha_creado, sexo, estado_civil, lugar, direccion, tipo_cedula) 
                values (:nombre, :snombre, :apellido, :sapellido, :cedula, :email, :usuario, :contrasena, :nacimiento, now(), :sexo, :estado_civil, (select id from Lugar where nombre_completo=:lugar), :direccion, :tipo_cedula)
            ", [
                ":nombre" => strtoupper($post['nombre']),
                ":snombre" => isset($post['snombre']) ? strtoupper($post['snombre']) : null,
                ":apellido" => strtoupper($post['apellido']),
                ":sapellido" => isset($post['sapellido']) ? strtoupper($post['sapellido']) : null,
                ":cedula" => $post['cedula'],
                ":tipo_cedula" => $post['tipo_cedula'],
                ":email" => strtoupper($post['email']),
                ":usuario" => isset($post['usuario']) ? strtoupper($post['usuario']) : null,
                ":contrasena" => isset($post['contrasena']) ? $post['contrasena'] : null,
                ":nacimiento" => $post['nacimiento'],
                ":sexo" => $post['sexo'],
                ":estado_civil" => $post['estado_civil'],
                ":lugar" => $post['lugar'],
                ":direccion" => isset($post['direccion']) ? strtoupper($post['direccion']) : null
            ]);

            $uid = $this->lastID();

            /* Telefonos */
            if (isset($post['telefonos']))
                foreach ($post['telefonos'] as $tlf)
                {
                   $this->run("
                        insert into Telefono (tlf, tipo, paciente) 
                        values (:tlf, (select id from Telefono_Tipo where nombre=:tipo), (select id from Paciente where cedula=:cedula))
                    ", [
                        ":tlf" => $tlf['tlf'],
                        ":tipo" => $tlf['tipo'],
                        ":cedula" => $post['cedula']
                    ]); 
                }

            $token = getToken();

            $this->run("insert into Token (token) values (:token)", [":token" => $token]);

            $nombreCompleto = strtoupper($post['nombre'])." ".strtoupper($post['apellido']);
            $url = "http://www.salazarseijas.com/medicos/php/validate.php?token=$token";

            sendEmail([
                "fromName" => "Contacto",
                "subject" => "Por favor confirma tu cuenta",
                "body" => "Hola $nombreCompleto,<br><br>Por favor haz click en el siguiente enlace para confirmar tu cuenta.<br><br><a href='$url' target='_blank'>$url</a>",
                "to" => $post['email'],
                "toName" => $nombreCompleto
            ]);

            return json_response(["msg" => $post['nombre'] . " " . $post['apellido'] . " fue añadido correctamente."]);
        }

        public function post_agregar_suscripcion($post = array())
        {
            $json = array();

            try {
                $query = $this->db->prepare("
                    insert into Suscripcion (paciente, tipo_suscripcion, empieza, termina, cant_cons_restantes)
                    values (:paciente,:tsuscripcion, now(), date_add(now(), interval (select num_dias from Tipo_Suscripcion where id = :tsuscripcion) day), (select cant_cons from Tipo_Suscripcion where id = :tsuscripcion))
                ");

                $query->execute(array(
                    ":paciente" => $post['usuario'],
                    ":tsuscripcion" => $post['tsuscripcion']
                ));

                $json['ok'] = true;
                $json['msg'] = "Se han añadido su suscripción";
            }
            catch (Exception $e) {
                $json['error'] = true;
                $json['msg'] = $e->getMessage();
            }

            return json_encode($json);
        }

        public function post_mensaje($post = array())
        {
            $json = array();

            try {
                $query = $this->db->prepare("
                    insert into Mensaje (paciente, medico, hora, html, owner, owner_name)
                    values (:paciente, :medico, now(), :mensaje, :owner, :owner_name)
                ");

                $query->execute(array(
                    ":paciente" => $post['paciente'],
                    ":medico" => $post['medico'],
                    ":mensaje" => $post['mensaje'],
                    ":owner" => $post['owner'],
                    ":owner_name" => $post['owner_name']
                ));

                $json['ok'] = true;
            }
            catch (Exception $e) {
                $json['error'] = true;
                $json['msg'] = $e->getMessage();
            }

            return json_encode($json);
        }

        public function adjuntar_imagen($post = array())
        {
            $json = array();

            try {
                $query = $this->db->prepare("
                    insert into Mensaje (paciente, medico, hora, html, owner, owner_name)
                    values (:paciente, :medico, now(), :mensaje, :owner, :owner_name)
                ");

                $query->execute(array(
                    ":paciente" => $post['paciente'],
                    ":medico" => $post['medico'],
                    ":mensaje" => $post['mensaje'],
                    ":owner" => $post['owner'],
                    ":owner_name" => $post['owner_name']
                ));

                $json['ok'] = true;
                $json['msg'] = "Se ha añadido el mensaje";
            }
            catch (Exception $e) {
                $json['error'] = true;
                $json['msg'] = $e->getMessage();
            }

            return json_encode($json);
        }
	}
?>