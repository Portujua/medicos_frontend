<?php
	trait Updates {
		
		public function put_paciente($post)
        {
            $json = array();

            $query = $this->db->prepare("
                update Paciente set 
                    nombre=:nombre, 
                    segundo_nombre=:snombre, 
                    apellido=:apellido, 
                    segundo_apellido=:sapellido, 
                    email=:email 
                where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":nombre" => strtoupper($post['nombre']),
                ":snombre" => isset($post['snombre']) ? strtoupper($post['snombre']) : null,
                ":apellido" => strtoupper($post['apellido']),
                ":sapellido" => isset($post['sapellido']) ? strtoupper($post['sapellido']) : null,
                ":email" => isset($post['email']) ? strtoupper($post['email']) : null
 
            ));

            /* Borro los telefonos viejos */
            // $query = $this->db->prepare("
            //     delete from Telefono where paciente=:id
            // ");

            // $query->execute(array(
            //     ":id" => $post['id']
            // ));
            
            // /* Añado los nuevos */
            // if (isset($post['telefonos']))
            //     foreach ($post['telefonos'] as $tlf)
            //     {
            //        $query = $this->db->prepare("
            //             insert into Telefono (tlf, tipo, paciente) 
            //             values (:tlf, (select id from Telefono_Tipo where nombre=:tipo), (select id from Paciente where cedula=:cedula))
            //         ");

            //         $query->execute(array(
            //             ":tlf" => $tlf['tlf'],
            //             ":tipo" => $tlf['tipo'],
            //             ":cedula" => $post['cedula']
            //         )); 
            //     }

            $json["status"] = "ok";
            $json["ok"] = true;
            $json["msg"] = $post['nombre'] . " " . $post['apellido'] . " fue modificado correctamente.";

            return json_encode($json);
        }

        public function put_medico($post)
        {
            $json = array();

            $query = $this->db->prepare("
                update Medico set 
                    nombre=:nombre, 
                    segundo_nombre=:snombre, 
                    apellido=:apellido, 
                    segundo_apellido=:sapellido, 
                    email=:email 
                where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":nombre" => strtoupper($post['nombre']),
                ":snombre" => isset($post['snombre']) ? strtoupper($post['snombre']) : null,
                ":apellido" => strtoupper($post['apellido']),
                ":sapellido" => isset($post['sapellido']) ? strtoupper($post['sapellido']) : null,
                ":email" => isset($post['email']) ? strtoupper($post['email']) : null
 
            ));

            /* Borro los telefonos viejos */
            // $query = $this->db->prepare("
            //     delete from Telefono where medico=:id
            // ");

            // $query->execute(array(
            //     ":id" => $post['id']
            // ));
            
            // /* Añado los nuevos */
            // if (isset($post['telefonos']))
            //     foreach ($post['telefonos'] as $tlf)
            //     {
            //        $query = $this->db->prepare("
            //             insert into Telefono (tlf, tipo, medico) 
            //             values (:tlf, (select id from Telefono_Tipo where nombre=:tipo), (select id from Medico where cedula=:cedula))
            //         ");

            //         $query->execute(array(
            //             ":tlf" => $tlf['tlf'],
            //             ":tipo" => $tlf['tipo'],
            //             ":cedula" => $post['cedula']
            //         )); 
            //     }

            $json["status"] = "ok";
            $json["ok"] = true;
            $json["msg"] = $post['nombre'] . " " . $post['apellido'] . " fue modificado correctamente.";

            return json_encode($json);
        }
	}
?>