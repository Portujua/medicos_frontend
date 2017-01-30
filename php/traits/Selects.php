<?php
	trait Selects {
        public function cargar_lugares($post)
        {
            $query = $this->db->prepare("
                select *
                from Lugar
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_tipos_telefonos($post)
        {
            $query = $this->db->prepare("
                select *
                from Telefono_Tipo
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_medicos($post, $query_extra = "")
        {
            $query = $this->db->prepare("
                select
                    p.id as id,
                    p.nombre as nombre,
                    p.segundo_nombre as segundo_nombre,
                    p.apellido as apellido,
                    p.segundo_apellido as segundo_apellido,
                    concat(
                        p.nombre, ' ',
                        (case when p.segundo_nombre is not null then concat(p.segundo_nombre, ' ') else '' end),
                        p.apellido,
                        (case when p.segundo_apellido is not null then concat(' ', p.segundo_apellido) else '' end)
                    ) as nombre_completo,
                    p.cedula as cedula,
                    p.tipo_cedula as tipo_cedula,
                    p.email as email,
                    p.usuario as usuario,
                    p.contrasena as contrasena,
                    date_format(p.fecha_nacimiento, '%d/%m/%Y') as fecha_nacimiento, 
                    date_format(p.fecha_creado, '%d/%m/%Y') as fecha_creado,
                    p.sexo as sexo,
                    p.estado_civil as estado_civil,
                    p.estado as estado,
                    (select nombre_completo from Lugar where id=p.lugar) as lugar,
                    p.direccion as direccion
                from Medico as p
                where p.id > 1
                ".$query_extra."
            ");

            $query->execute();
            $medicos = $query->fetchAll();

            for ($i = 0; $i < count($medicos); $i++)
            {
                $medicos[$i]["snombre"] = $medicos[$i]["segundo_nombre"];
                $medicos[$i]["sapellido"] = $medicos[$i]["segundo_apellido"];

                /* Telefonos */
                $query = $this->db->prepare("
                    select 
                        t.tlf as tlf,
                        tt.nombre as tipo
                    from Telefono as t, Telefono_Tipo as tt
                    where t.tipo=tt.id and t.medico=:pid
                ");

                $query->execute(array(
                    ":pid" => $medicos[$i]['id']
                ));

                $medicos[$i]['telefonos'] = $query->fetchAll();

                /* Areas */
                $areas = array();

                $query = $this->db->prepare("
                    select 
                        a.id as id,
                        a.nombre as nombre
                    from Area as a, Medico_Area as ma
                    where a.id=ma.area and ma.medico=:medico
                ");

                $query->execute(array(
                    ":medico" => $medicos[$i]['id']
                ));

                $medicos[$i]['areas_'] = $query->fetchAll();

                foreach ($medicos[$i]['areas_'] as $a)
                    $areas[] = $a['id'];

                $medicos[$i]['areas'] = $areas;
            }

            return json_encode($medicos);
        }

        public function cargar_areas($post)
        {
            $query = $this->db->prepare("
                select *
                from Area
            ");

            $query->execute();

            $areas = $query->fetchAll();

            return json_encode($areas);
        }

        public function cargar_mensajes($post)
        {
            $n = intval($post['n']);
            $m = 0;
            $last = intval($post['last']) != -1 ? " and m.id<" . $post['last'] : "";

            $chat = array();

            $query = $this->db->prepare("
                select R.*
                from (select
                        m.id as id,
                        m.html as html,
                        m.img as img,
                        concat(time_format(m.hora, '%h:%i:%s %p'), ' ', date_format(m.hora, '%d/%m/%Y')) as hora_str,
                        m.hora as hora_completa,
                        date_format(m.hora, '%d/%m/%Y') as fecha,
                        time_format(m.hora, '%h:%i:%s %p') as hora,
                        (select concat(nombre, ' ', apellido) from Paciente where id=m.paciente) as paciente,
                        (select concat(nombre, ' ', apellido) from Medico where id=m.medico) as medico,
                        m.owner as owner,
                        m.owner_name as owner_name
                    from Mensaje as m
                    where 
                        m.paciente=:paciente and
                        m.medico=:medico ".$last."
                    order by m.hora desc
                    limit ".($n * $m).",".$n."
                ) R
                order by R.id asc
            ");

            $query->execute(array(
                ":paciente" => $post['paciente'],
                ":medico" => $post['medico']
            ));

            $chat['mensajes'] = $query->fetchAll();

            $query = $this->db->prepare("
                select
                    concat(nombre, ' ', apellido) as nombre_completo
                from Paciente
                where id=:paciente
            ");

            $query->execute(array(
                ":paciente" => $post['paciente']
            ));

            $chat['paciente'] = $query->fetchAll();
            $chat['paciente'] = $chat['paciente'][0];
            $chat['paciente'] = $chat['paciente']['nombre_completo'];

            $query = $this->db->prepare("
                select
                    concat(nombre, ' ', apellido) as nombre_completo
                from Medico
                where id=:medico
            ");

            $query->execute(array(
                ":medico" => $post['medico']
            ));

            $chat['medico'] = $query->fetchAll();
            $chat['medico'] = $chat['medico'][0];
            $chat['medico'] = $chat['medico']['nombre_completo'];

            return json_encode($chat);
        }
	}
?>