<?php
	trait Deletes {
		public function eliminar_ejemplo($post)
        {
            $json = array();

            $query = $this->db->prepare("
                update Stock set eliminado=1 where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id']
            ));

            $json["status"] = "ok";
            $json["msg"] = "El ejemplo fue eliminado correctamente.";

            return json_encode($json);
        }
	}
?>