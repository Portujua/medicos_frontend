<?php
	trait Deletes {
		public function eliminar_stock($post)
        {
            $query = $this->db->prepare("
                update Stock set eliminado=1 where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id']
            ));
        }

        public function eliminar_material_asignado($post)
        {
            $query = $this->db->prepare("
                update Stock_Personal set 
                    eliminado=1,
                    eliminado_por=:eliminado_por
                where id=:id
            ");

            $query->execute(array(
                ":eliminado_por" => $_SESSION['login_username'],
                ":id" => $post['id']
            ));
        }

        public function eliminar_material_danado($post)
        {
            // Actualizo primero el stock
            $query = $this->db->prepare("
                update Stock_Personal set
                    restante=restante + (select cantidad from Stock_Personal_Danado where id=:id)
                where id=(select stock from Stock_Personal_Danado where id=:id)
            ");

            $query->execute(array(
                ":id" => $post['id']
            ));

            // Elimino
            $query = $this->db->prepare("
                delete from Stock_Personal_Danado 
                where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id']
            ));
        }

        public function eliminar_precio_producto($post)
        {
            $query = $this->db->prepare("
                update Producto_Costo set eliminado=1 where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id']
            ));
        }

        public function borrar_guia_web($post)
        {
            unlink("../../soincopy_files/guias_web/" . $post['file']);
            $query = $this->db->prepare("
                update Guia_Web set revisada=1 where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id']
            ));
        }

        public function eliminar_pdf($pdf)
        {
            $query = $this->db->prepare("
                update Guia set pdf=null where pdf=:pdf
            ");

            $query->execute(array(
                ":pdf" => $pdf
            ));
        }
	}
?>