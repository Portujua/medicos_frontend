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
                select 
                    u.id as id, 
                    u.usuario as usuario, 
                    u.nombre as nombre, 
                    u.apellido as apellido, 
                    concat(u.nombre, ' ', u.apellido) as nombre_completo,
                    u.cedula as cedula, 
                    u.tipo_cedula as tipo_cedula, 
                    u.email as email, 
                    u.sexo as sexo,
                    u.estado_civil as estado_civil,
                    u.direccion as direccion,
                    u.lugar as lugar_id,
                    u.contrasena as contrasena,
                    date_format(u.fecha_nacimiento, '%d/%m/%Y') as fecha_nacimiento, 
                    date_format(u.fecha_creado, '%d/%m/%Y') as fecha_creado,
                    (
                        case when (select datediff(termina, now()) from Suscripcion where paciente=u.id and (now() between empieza and termina) order by termina desc) is not null then (select datediff(termina, now()) from Suscripcion where paciente=u.id and (now() between empieza and termina) order by termina desc) else -1 end
                    ) as dias_restantes
                from Paciente as u
                where upper(u.usuario)=:username and u.contrasena=:password and u.estado=1
                limit 1
            ");

            $query->execute(array(
                ":username" => strtoupper($post['username']),
                ":password" => $post['password']
            ));

            $u = $query->fetchAll();

            if (count($u) > 0)
            {
                $user = $u[0];

                /* Obtengo los telefonos */
                $query = $this->db->prepare("
                    select *
                    from Telefono 
                    where paciente=:pid
                ");

                $query->execute(array(
                    ":pid" => $user['id']
                ));

                $user['telefonos'] = $query->fetchAll();

                /* Obtengo loas suscripciones */
                $query = $this->db->prepare("
                    select 
                        date_format(empieza, '%d/%m/%Y') as empieza,
                        date_format(termina, '%d/%m/%Y') as termina,
                        datediff(termina, empieza) as dias
                    from Suscripcion
                    where paciente=:pid
                ");

                $query->execute(array(
                    ":pid" => $user['id']
                ));

                $user['suscripciones'] = $query->fetchAll();

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