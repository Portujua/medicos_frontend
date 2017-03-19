<?php
	trait Deletes {
		public function delete_consulta($post)
    {
        $json = array();

        // Chequeo si el ultimo mensaje de la consulta no es ya de cerrado
        $query = $this->db->prepare("
            select *
            from Mensaje 
            where paciente=:paciente
            	and medico=:medico
            order by id desc
            limit 1
        ");

        $query->execute(array(
            ":paciente" => $post['paciente'],
            ":medico" => $post['medico']
        ));

        $msjs = $query->fetchAll();

        if (count($msjs) > 0) {
        	if ($msjs[0]['owner'] == '_____system_____') {
        		$json["status"] = "error";
        		$json["error"] = true;
        		$json["msg"] = "No se puede cerrar esta consulta.";

        		return json_encode($json);
        	}
        }

        $query = $this->db->prepare("
          update Suscripcion 
          set 
          	cant_cons_restantes=cant_cons_restantes-1 
          where paciente=:pid and cant_cons_restantes>0
        ");

        $query->execute(array(
            ":pid" => $post['paciente'],
        ));

        // Añado un mensaje al chat
        $query = $this->db->prepare("
            insert into Mensaje (paciente, medico, hora, html, owner, owner_name)
            values (:paciente, :medico, now(), 'Se ha cerrado la consulta', '_____system_____', '_____system_____')
        ");

        $query->execute(array(
            ":paciente" => $post['paciente'],
            ":medico" => $post['medico']
        ));

        $json["status"] = "ok";
        $json["ok"] = true;
        $json["msg"] = "Se ha cerrado la consulta";

        return json_encode($json);
    }
	}
?>