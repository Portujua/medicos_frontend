<?php
	trait Selects {
        abstract public function run($query, $opts = []);

        public function get_lugares($post)
        {
            $query = $this->db->prepare("
                select *
                from Lugar
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function get_tipos_telefonos($post)
        {
            $query = $this->db->prepare("
                select *
                from Telefono_Tipo
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function get_medicos($post, $query_extra = "")
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

        public function get_areas($post)
        {
            $query = $this->db->prepare("
                select *
                from Area
            ");

            $query->execute();

            $areas = $query->fetchAll();

            return json_encode($areas);
        }
        public function get_tiposuscripcion($post)
        {
            $query = $this->db->prepare("
                select *
                from Tipo_Suscripcion
            ");

            $query->execute();

            $tsuscripcion = $query->fetchAll();

            return json_encode($tsuscripcion);
        }

        public function get_mensajes_pendientes($post) {
            $query = $this->db->prepare("
                select 
                    id as id,
                    html as html,
                    img as img,
                    concat(time_format(hora, '%h:%i:%s %p'), ' ', date_format(hora, '%d/%m/%Y')) as hora_str,
                    hora as hora_completa,
                    date_format(hora, '%d/%m/%Y') as fecha,
                    time_format(hora, '%h:%i:%s %p') as hora,
                    (select concat(nombre, ' ', apellido) from Paciente where id=paciente) as paciente,
                    (select concat(nombre, ' ', apellido) from Medico where id=medico) as medico,
                    owner as owner,
                    owner_name as owner_name,
                    leido as leido
                from Mensaje 
                where 
                    paciente=:paciente and medico=:medico and leido=0 and owner!=:usuario
                order by id desc
            ");

            $query->execute([
                ":paciente" => $post['paciente'],
                ":medico" => $post['medico'],
                ":usuario" => $post['usuario']
            ]);

            $json = [];
            $json['cantidad'] = $query->rowCount();
            $json['ultimo'] = null;

            if ($json['cantidad'] > 0) {
                $aux = $query->fetchAll();
                $json['ultimo'] = $aux[0];
            }

            return json_encode($json);
        }

        public function get_leidos($post) {
            $query = $this->db->prepare("select * from Mensaje where id in (".$post['ids'].")");
            $query->execute();
            $mensajes = $query->fetchAll();
            $json = [];
            $json['mensajes'] = [];

            foreach ($mensajes as $m) {
                if ($m['leido'] == '1') {
                    $json['mensajes'][] = $m['id'];
                }
            }

            return json_encode($json);
        }
        
        public function get_mensajes($post)
        {
            $n = intval($post['n']);
            $m = 0;
            $last = intval($post['last']) != -1 ? " and m.id<" . $post['last'] : "";

            $nuevos = isset($post['nuevos']) ? " and m.leido=0" : "";
            $noSystem = $post['es_medico'] == 'true' && isset($post['nuevos']) ? " and m.owner!='_____system_____'" : "";

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
                        m.owner_name as owner_name,
                        m.leido as leido
                    from Mensaje as m
                    where 
                        m.paciente=:paciente and
                        m.medico=:medico $last
                        $nuevos $noSystem
                    order by m.hora desc
                    limit ".($n * $m).",$n
                ) R
                order by R.id asc
            ");

            $query->execute(array(
                ":paciente" => $post['paciente'],
                ":medico" => $post['medico']
            ));

            $chat['mensajes'] = $query->fetchAll();

            foreach ($chat['mensajes'] as $m) {
                if (strcmp(strtoupper($m['owner']), strtoupper($post['me'])) != 0) {
                    $this->run("update Mensaje set leido=1 where id=:id", [":id" => $m['id']]);
                }
            }

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

        public function get_pacientes($post, $query_extra = "")
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
                    p.email_validado as email_validado
                from Paciente as p
                where 1=1
                ".$query_extra."
            ");

            $query->execute();
            $pacientes = $query->fetchAll();

            for ($i = 0; $i < count($pacientes); $i++)
            {
                $pacientes[$i]["snombre"] = $pacientes[$i]["segundo_nombre"];
                $pacientes[$i]["sapellido"] = $pacientes[$i]["segundo_apellido"];

                /* Telefonos */
                $query = $this->db->prepare("
                    select 
                        t.tlf as tlf,
                        tt.nombre as tipo
                    from Telefono as t, Telefono_Tipo as tt
                    where t.tipo=tt.id and t.paciente=:pid
                ");

                $query->execute(array(
                    ":pid" => $pacientes[$i]['id']
                ));

                $pacientes[$i]['telefonos'] = $query->fetchAll();
            }

            return json_encode($pacientes);
        }
	}
?>