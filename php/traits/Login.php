<?php
	trait Login {
		public function actualizar_hora_sesion()
        {
            @session_start();
            $_SESSION['login_time'] = time();
        }

        public function session_expired()
        {
            @session_start();

            if (!isset($_SESSION['login_username']))
                return true;

            if (!isset($_SESSION['login_time']))
                return true;

            if (time() - $_SESSION['login_time'] > $this->session_duration)
                return true;

            return false;
        }

        public function login($post)
        {
            $query = $this->db->prepare("
                select u.id as id, u.usuario as username, u.nombre as nombre, u.apellido as apellido, u.cedula as cedula, u.email as email, u.telefono as tlf, (select d.nombre from Personal_Departamento as pd, Departamento as d where pd.departamento=d.id and pd.personal=u.id limit 1) as departamento,
                    (u.usuario in ".$this->admin_usernames.") as es_admin
                from Personal as u
                where u.usuario=:username and u.contrasena=:password and u.estado=1
                limit 1
            ");

            $query->execute(array(
                ":username" => $post['username'],
                ":password" => $post['password']
            ));

            $u = $query->fetchAll();

            if (count($u) > 0)
            {
                /* Obtengo los permisos */
                $user = $u[0];
                $user['es_admin'] = $user['es_admin'] == "1" ? true : false;

                $query_root = "
                    select nombre
                    from Permiso as p
                ";

                $query_no_root = "
                    select nombre
                    from Permiso_Asignado as pa, Permiso as p
                    where pa.permiso=p.id and pa.usuario=:uid
                ";

                $query = null;

                if ($user['es_admin'])
                    $query = $this->db->prepare($query_root);
                else
                    $query = $this->db->prepare($query_no_root);

                $query->execute(array(
                    ":uid" => $user['id']
                ));

                $permisos = $query->fetchAll();

                foreach ($permisos as $p)
                    $user[$p['nombre']] = 1;

                /* Obtengo la ultima conexion */
                $query = $this->db->prepare("
                    select date_format(l.fecha, '%d/%m/%Y') as fecha, time_format(l.fecha, '%h:%i:%s %p') as hora
                    from Log_Login as l
                    where username=:username
                    order by l.fecha desc
                    limit 1
                ");

                $query->execute(array(
                    ":username" => $post['username']
                ));

                $ult = $query->fetchAll();

                if (count($ult) > 0)
                    $user['ultima_visita'] = $ult[0];
                else
                    $user['ultima_visita'] = array(
                        "fecha" => "",
                        "hora" => ""
                    );

                /* Setteo la sesion y registro el login */
                @session_start();
                $_SESSION['login_username'] = $post['username'];
                $this->actualizar_hora_sesion();

                $query = $this->db->prepare("
                    insert into Log_Login (fecha, username)
                    values (now(), :username)
                ");

                $query->execute(array(
                    ":username" => $post['username']
                ));

                return json_encode($user);
            }
            else
                return json_encode(array("error" => 1));
        }
	}
?>