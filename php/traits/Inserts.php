<?php
	trait Inserts {
		public function agregar_paciente($post)
        {
            $json = array();

            $query = $this->db->prepare("
                insert into Paciente (nombre, segundo_nombre, apellido, segundo_apellido, cedula, email, usuario, contrasena, fecha_nacimiento, fecha_creado, sexo, estado_civil, lugar, direccion, tipo_cedula) 
                values (:nombre, :snombre, :apellido, :sapellido, :cedula, :email, :usuario, :contrasena, :nacimiento, now(), :sexo, :estado_civil, (select id from Lugar where nombre_completo=:lugar), :direccion, :tipo_cedula)
            ");

            $query->execute(array(
                ":nombre" => strtoupper($post['nombre']),
                ":snombre" => isset($post['snombre']) ? strtoupper($post['snombre']) : null,
                ":apellido" => strtoupper($post['apellido']),
                ":sapellido" => isset($post['sapellido']) ? strtoupper($post['sapellido']) : null,
                ":cedula" => $post['cedula'],
                ":tipo_cedula" => $post['tipo_cedula'],
                ":email" => isset($post['email']) ? strtoupper($post['email']) : null,
                ":usuario" => isset($post['usuario']) ? strtoupper($post['usuario']) : null,
                ":contrasena" => isset($post['contrasena']) ? $post['contrasena'] : null,
                ":nacimiento" => $post['nacimiento'],
                ":sexo" => $post['sexo'],
                ":estado_civil" => $post['estado_civil'],
                ":lugar" => $post['lugar'],
                ":direccion" => isset($post['direccion']) ? strtoupper($post['direccion']) : null
            ));

            $uid = $this->db->lastInsertId();

            /* Telefonos */
            if (isset($post['telefonos']))
                foreach ($post['telefonos'] as $tlf)
                {
                   $query = $this->db->prepare("
                        insert into Telefono (tlf, tipo, paciente) 
                        values (:tlf, (select id from Telefono_Tipo where nombre=:tipo), (select id from Paciente where cedula=:cedula))
                    ");

                    $query->execute(array(
                        ":tlf" => $tlf['tlf'],
                        ":tipo" => $tlf['tipo'],
                        ":cedula" => $post['cedula']
                    )); 
                }

            $json["status"] = "ok";
            $json["ok"] = true;
            $json["msg"] = $post['nombre'] . " " . $post['apellido'] . " fue añadido correctamente.";

            return json_encode($json);
        }

        public function agregar_suscripcion($post = array())
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

        public function agregar_mensaje($post = array())
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