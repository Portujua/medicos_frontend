<?php
	trait Updates {
		public function cambiar_estado_medico($post)
        {
            $json = array();

            $query = $this->db->prepare("
                update Medico set estado=:estado where id=:pid
            ");

            $query->execute(array(
                ":pid" => $post['pid'],
                ":estado" => $post['estado']
            ));

            $json["status"] = "ok";
            $json["ok"] = true;
            $json["msg"] = "El medico fue ".($post['estado'] == 1 ? "habilitada" : "deshabilitada")." correctamente.";

            return json_encode($json);
        }

        public function cambiar_estado_area($post)
        {
            $json = array();

            $query = $this->db->prepare("
                update Area set estado=:estado where id=:pid
            ");

            $query->execute(array(
                ":pid" => $post['id'],
                ":estado" => $post['estado']
            ));

            $json["status"] = "ok";
            $json["ok"] = true;
            $json["msg"] = "El area fue ".($post['estado'] == 1 ? "habilitado" : "deshabilitado")." correctamente.";

            return json_encode($json);
        }

        public function cambiar_contrasena($post)
        {
            $json = array();

            $query = $this->db->prepare("
                select * from Medico where usuario=:usuario and cambiar_contrasena=1
            ");

            $query->execute(array(
                ":usuario" => $post['usuario']
            ));

            if ($query->rowCount() > 0)
            {
                $query = $this->db->prepare("
                    update Medico set 
                        contrasena=:contrasena,
                        cambiar_contrasena=0
                    where usuario=:usuario
                ");

                $query->execute(array(
                    ":contrasena" => $post['contrasena'],
                    ":usuario" => $post['usuario']
                ));

                $json["status"] = "ok";
                $json["ok"] = true;
                $json["msg"] = "La contraseña ha sido cambiada correctamente.";
            }
            else
            {
                $json["status"] = "ok";
                $json["error"] = true;
                $json["msg"] = "Cambio de contraseña invalido.";
            }

            return json_encode($json);
        }

        public function editar_medico($post)
        {
            $json = array();

            $query = $this->db->prepare("
                update Medico set 
                    nombre=:nombre, 
                    segundo_nombre=:snombre, 
                    apellido=:apellido, 
                    segundo_apellido=:sapellido, 
                    cedula=:cedula,
                    tipo_cedula=:tipo_cedula,
                    email=:email, 
                    usuario=:usuario, 
                    contrasena=:contrasena, 
                    fecha_nacimiento=:nacimiento, 
                    sexo=:sexo, 
                    estado_civil=:estado_civil, 
                    lugar=(select id from Lugar where nombre_completo=:lugar), 
                    direccion=:direccion
                where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
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

            /* Borro los telefonos viejos */
            $query = $this->db->prepare("
                delete from Telefono where medico=:id
            ");

            $query->execute(array(
                ":id" => $post['id']
            ));

            /* Añado los nuevos */
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


            /* Borro las areas viejas */
            $query = $this->db->prepare("
                delete from Medico_Area where medico=:id
            ");

            $query->execute(array(
                ":id" => $post['id']
            ));

            /* Añado las nuevas */
            if (isset($post['areas']))
                foreach ($post['areas'] as $area)
                {
                   $query = $this->db->prepare("
                        insert into Medico_Area (area, medico) 
                        values (:area, :medico)
                    ");

                    $query->execute(array(
                        ":area" => $area,
                        ":medico" => $post['id']
                    )); 
                }

            $json["status"] = "ok";
            $json["ok"] = true;
            $json["msg"] = $post['nombre'] . " " . $post['apellido'] . " fue modificado correctamente.";

            return json_encode($json);
        }


        public function editar_paciente($post)
        {
            $json = array();

            $query = $this->db->prepare("
                update Paciente set 
                    nombre=:nombre, 
                    segundo_nombre=:snombre, 
                    apellido=:apellido, 
                    segundo_apellido=:sapellido, 
                    cedula=:cedula,
                    tipo_cedula=:tipo_cedula,
                    email=:email, 
                    usuario=:usuario, 
                    contrasena=:contrasena, 
                    fecha_nacimiento=:nacimiento, 
                    sexo=:sexo, 
                    estado_civil=:estado_civil, 
                    lugar=(select id from Lugar where nombre_completo=:lugar), 
                    direccion=:direccion
                where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
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

            /* Borro los telefonos viejos */
            $query = $this->db->prepare("
                delete from Telefono where paciente=:id
            ");

            $query->execute(array(
                ":id" => $post['id']
            ));
            /* Añado los nuevos */
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
            $json["msg"] = $post['nombre'] . " " . $post['apellido'] . " fue modificado correctamente.";

            return json_encode($json);
        }

        public function editar_area($post)
        {
            $json = array();

            $query = $this->db->prepare("
                update Area set 
                    nombre=:nombre
                where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":nombre" => $post['nombre']
            ));

            $json["status"] = "ok";
            $json["ok"] = true;
            $json["msg"] = "El area " . $post['nombre'] . " fue modificada correctamente.";

            return json_encode($json);
        }
	}
?>