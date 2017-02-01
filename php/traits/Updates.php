<?php
	trait Updates {

        public function cerrar_consulta($post)
        {
            $json = array();

            $query = $this->db->prepare("
                    update Suscripcion set cant_cons_restantes=cant_cons_restantes-1 where paciente=:pid and cant_cons_restantes>0
            ");

            $query->execute(array(
                ":pid" => $post['usuario'],
            ));

            $json["status"] = "ok";
            $json["ok"] = true;
            $json["msg"] = "Se ha cerrado la consulta";

            return json_encode($json);
        }
		
		public function actpaciente($post)
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
	}
?>