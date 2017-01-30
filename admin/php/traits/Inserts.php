<?php
	trait Inserts {
		public function agregar_ejemplo($post)
        {
            $json = array();

            $query = $this->db->prepare("insert into Ejemplo (nombre) values (:nombre)");

            $query->execute(array(
                ":nombre" => $post['nombre']
            ));

            $json["status"] = "ok";
            $json["msg"] = "El ejemplo fue añadido correctamente.";

            return json_encode($json);
        }

        public function agregar_area($post)
        {
            $json = array();

            $query = $this->db->prepare("insert into Area (nombre) values (:nombre)");

            $query->execute(array(
                ":nombre" => $post['nombre']
            ));

            $json["status"] = "ok";
            $json["ok"] = true;
            $json["msg"] = "El area fue añadida correctamente.";

            return json_encode($json);
        }

        public function agregar_medico($post)
        {
            $json = array();

            $query = $this->db->prepare("
                insert into Medico (nombre, segundo_nombre, apellido, segundo_apellido, cedula, email, usuario, contrasena, fecha_nacimiento, fecha_creado, sexo, estado_civil, lugar, direccion, tipo_cedula) 
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
                        insert into Telefono (tlf, tipo, medico) 
                        values (:tlf, (select id from Telefono_Tipo where nombre=:tipo), (select id from Medico where cedula=:cedula))
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
	}
?>