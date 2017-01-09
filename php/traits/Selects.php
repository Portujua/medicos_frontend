<?php
	trait Selects {
		public function cargar_materias_carrera($post)
        {
            $query = $this->db->prepare("
                select m.id as id, m.nombre as nombre, c.nombre as carrera, (case when p.numero=99 then 'Otro' else p.numero end) as periodo, c.id as carrera_id, p.tipo as tipo_carrera, m.estado as estado, p.id as periodo_id, t.nombre as tipo, t.nombre as tipo_nombre, t.id as tipo_id
                from Materia as m, Car_Per as cp, Carrera as c, Periodo as p, Tipo_Materia as t
                where m.dictada_en=cp.id and cp.carrera=c.id and cp.periodo=p.id and m.tipo=t.id and c.id=:cid and m.estado=1
                order by p.numero asc
            ");

            $query->execute(array(
                ":cid" => $post['cid']
            ));

            return json_encode($query->fetchAll());
        }

        public function cargar_materias($post)
        {
            $query = $this->db->prepare("
                select m.id as id, m.nombre as nombre, c.nombre as carrera, (case when p.numero=99 then 'Otro' else p.numero end) as periodo, c.id as carrera_id, p.tipo as tipo_carrera, m.estado as estado, cp.id as periodo_id, t.nombre as tipo, t.nombre as tipo_nombre, t.id as tipo_id
                from Materia as m, Car_Per as cp, Carrera as c, Periodo as p, Tipo_Materia as t
                where m.dictada_en=cp.id and cp.carrera=c.id and cp.periodo=p.id and m.tipo=t.id
                order by p.numero asc
            ");
            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_menciones($post)
        {
            $query = $this->db->prepare("
                select m.id as id, m.nombre as nombre, c.nombre as carrera, c.id as cid, m.estado as estado
                from Mencion as m, Carrera as c
                where m.carrera=c.id
                order by c.nombre asc
            ");
            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_proveedores($post)
        {
            $query = $this->db->prepare("
                select p.*
                from Proveedor as p
                order by p.nombre asc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_menciones_de($post)
        {
            $query = $this->db->prepare("
                select m.id as id, m.nombre as nombre, c.nombre as carrera, c.id as cid, m.estado as estado
                from Mencion as m, Carrera as c
                where m.carrera=c.id and c.id=:cid
                order by c.nombre asc
            ");

            $query->execute(array(
                ":cid" => $post['cid']
            ));

            return json_encode($query->fetchAll());
        }

        public function cargar_carreras($post)
        {
            $query = $this->db->prepare("
                select c.id as id, c.nombre as nombre, c.estado as estado
                from Carrera as c
                order by c.nombre asc
            ");

            $query->execute();
            $carreras = $query->fetchAll();

            for ($i = 0; $i < count($carreras); $i++)
            {
                $query = $this->db->prepare("
                    select p.tipo as tipo
                    from Car_Per as cp, Periodo as p
                    where cp.periodo=p.id and cp.carrera=:cid
                    limit 1
                ");

                $query->execute(array(
                    ":cid" => $carreras[$i]['id']
                ));

                $tipo = $query->fetchAll();
                $tipo = $tipo[0]['tipo'];
                $carreras[$i]['tipo'] = $tipo;
            }

            return json_encode($carreras);
        }

        public function cargar_tipos_materias($post)
        {
            $query = $this->db->prepare("
                select *
                from Tipo_Materia
                order by id asc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_condiciones_pago($post)
        {
            $query = $this->db->prepare("
                select *
                from Condicion_Pago
                order by nombre asc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_cuentaabiertas($post)
        {
            $query = $this->db->prepare("
                select id, nombre, inicia, vence, estado, (case when vence is not null then date_format(vence, '%d/%m/%Y') else 'Nunca' end) as vence_el, (case when vence is not null then (curdate() > vence) else 0 end) as vencido, date_format(inicia, '%d/%m/%Y') as inicia_el
                from CuentaAbierta
                order by id asc
            ");

            $query->execute();
            $cuentas = $query->fetchAll();

            for ($i = 0; $i < count($cuentas); $i++)
            {
                /* Personas */
                $cuentas[$i]['personas'] = array();

                $query = $this->db->prepare("
                    select id, nombre_completo, nombre_completo as nombre, cedula as cedula
                    from Persona_Autorizada as p
                    where p.cuentaabierta=:cuentaabierta
                ");

                $query->execute(array(
                    ":cuentaabierta" => $cuentas[$i]['id']
                ));

                $personas = $query->fetchAll();
                $cuentas[$i]['personas'] = $personas;

                /* Productos */
                $cuentas[$i]['productos'] = array();
                $cuentas[$i]['total_cuenta'] = 0.0;

                $query = $this->db->prepare("
                    select p.id as producto, op.nro_copias as nro_copias, op.nro_originales as nro_originales, (select costo from Producto_Costo where producto=p.id and eliminado=0 order by fecha desc limit 1) as costo_unitario, op.precio_unitario as costo_unitario_facturado, op.precio_total as costo_total_facturado, op.id as capid, date_format(op.fecha_anadido, '%d/%m/%Y') as fecha_anadido, p.nombre as producto_nombre
                    from CuentaAbierta_Producto as op, Producto as p
                    where op.producto=p.id
                    and op.cuentaabierta=:cuentaabierta
                ");

                $query->execute(array(
                    ":cuentaabierta" => $cuentas[$i]['id']
                ));

                $productos = $query->fetchAll();

                foreach ($productos as $p)
                {
                    $nuevo = array();

                    $nuevo['producto'] = $p['producto'];
                    $nuevo['producto_nombre'] = $p['producto_nombre'];
                    $nuevo['capid'] = $p['capid'];
                    $nuevo['fecha_anadido'] = $p['fecha_anadido'];
                    $nuevo['copias'] = intval($p['nro_copias']);
                    $nuevo['originales'] = intval($p['nro_originales']);
                    $nuevo['costo_unitario'] = floatval($p['costo_unitario']);
                    $nuevo['costo_unitario_facturado'] = floatval($p['costo_unitario_facturado']);
                    $nuevo['costo_total_facturado'] = floatval($p['costo_total_facturado']);

                    $cuentas[$i]['total_cuenta'] += floatval($p['costo_total_facturado']);

                    $cuentas[$i]['productos'][] = $nuevo;
                }
            }

            return json_encode($cuentas);
        }

        public function cargar_inventario($post)
        {
            $query = $this->db->prepare("
                select m.id as id, m.nombre as nombre, m.estado as estado, (select (case when sum(cantidad_disponible) is not null then sum(cantidad_disponible) else 0 end) from Stock where material=m.id and eliminado=0) as cantidad, (select concat(date_format(fecha_anadido, '%d/%m/%Y'), ' a las ', time_format(fecha_anadido, '%h:%i:%s %p')) from Stock where material=m.id and eliminado=0 order by fecha_anadido desc limit 1) as fecha_ultimo_ingreso, (select (case when sum(restante) is not null then sum(restante) else 0 end) from Stock_Personal where material=m.id and agotado=0 and eliminado=0) as cantidad_asignada
                from Material as m
            ");

            $query->execute();
            $inventario = $query->fetchAll();

            for ($i = 0; $i < count($inventario); $i++)
            {
                $inventario[$i]['stock'] = array();

                $query = $this->db->prepare("
                    select s.id as id, s.cantidad as cantidad, s.fecha_anadido as fecha_anadido, s.costo as costo, concat(date_format(s.fecha_anadido, '%d/%m/%Y'), ' a las ', time_format(s.fecha_anadido, '%h:%i:%s %p')) as fecha_str, p.id as proveedor, p.nombre as proveedor_nombre, p.ni as proveedor_ni
                    from Stock as s, Proveedor as p
                    where s.proveedor=p.id and s.material=:mid and s.eliminado=0
                    order by fecha_anadido desc
                ");

                $query->execute(array(
                    ":mid" => $inventario[$i]['id']
                ));

                $inventario[$i]['stock'] = $query->fetchAll();




                $inventario[$i]['inventario_asignado'] = array();

                $query = $this->db->prepare("
                    select (case when sum(sp.restante) is not null then sum(sp.restante) else 0 end) as cantidad, concat(p.nombre, ' ', p.apellido) as personal 
                    from Stock_Personal as sp, Personal as p
                    where sp.personal=p.id and sp.material=:mid and sp.agotado=0 and sp.eliminado=0
                ");

                $query->execute(array(
                    ":mid" => $inventario[$i]['id']
                ));

                $inventario[$i]['inventario_asignado'] = $query->fetchAll();
            }

            return json_encode($inventario);
        }

        public function cargar_materiales_guias($post)
        {
            $query = $this->db->prepare("
                select p.id as id, p.nombre as nombre, d.nombre as departamento
                from Producto as p, Departamento as d
                where p.departamento=d.id and d.nombre='Originales' and p.estado=1 and p.id>=1 and p.id<=36
            ");

            $query->execute();
            $inventario = $query->fetchAll();

            for ($i = 0; $i < count($inventario); $i++)
            {
                $inventario[$i]['stock'] = array();

                $query = $this->db->prepare("
                    select s.id as id, s.cantidad as cantidad, s.fecha_anadido as fecha_anadido, s.costo as costo, concat(date_format(s.fecha_anadido, '%d/%m/%Y'), ' a las ', time_format(s.fecha_anadido, '%h:%i:%s %p')) as fecha_str, p.id as proveedor, p.nombre as proveedor_nombre, p.ni as proveedor_ni
                    from Stock as s, Proveedor as p
                    where s.proveedor=p.id and s.material=:mid and s.eliminado=0
                    order by fecha_anadido desc
                ");

                $query->execute(array(
                    ":mid" => $inventario[$i]['id']
                ));

                $inventario[$i]['stock'] = $query->fetchAll();




                $inventario[$i]['inventario_asignado'] = array();

                $query = $this->db->prepare("
                    select (case when sum(sp.restante) is not null then sum(sp.restante) else 0 end) as cantidad, concat(p.nombre, ' ', p.apellido) as personal 
                    from Stock_Personal as sp, Personal as p
                    where sp.personal=p.id and sp.material=:mid and sp.agotado=0 and sp.eliminado=0
                ");

                $query->execute(array(
                    ":mid" => $inventario[$i]['id']
                ));

                $inventario[$i]['inventario_asignado'] = $query->fetchAll();
            }

            return json_encode($inventario);
        }

        public function cargar_productos($post)
        {
            $productos = array();

            if (!isset($post['did']))
            {
                $query = $this->db->prepare("
                    select p.nombre as nombre, 
                    p.nombre as nombre_viejo, 
                    p.id as id, 
                    p.descripcion as descripcion, 
                    p.estado as estado, 
                    d.nombre as departamento_nombre, 
                    d.id as departamento, 
                    (select costo from Producto_Costo where producto=p.id and eliminado=0 order by fecha desc limit 1) as costo_unitario, 
                    pf.id as familia, 
                    pf.nombre as familia_nombre,
                    (select sum(pm.cantidad * (s.costo / s.cantidad)) from Producto_Material as pm, Stock as s where pm.producto=p.id and pm.material=s.material and s.eliminado=0 and s.cantidad_disponible>0) as costo_materiales,
                    cast((select (
                            select sum(s.cantidad_disponible) as disponible
                            from Stock as s
                            where pm.material=s.material and s.eliminado=0
                            group by s.material
                        ) / pm.cantidad as disponibles
                        from Producto_Material as pm
                        where pm.producto=p.id
                        order by disponibles asc
                        limit 1) as unsigned
                        ) as disponibles, 
                        p.exento_iva as exento_iva, 
                        concat(pf.id, p.id) as codigo, 
                        p.tokens as tokens
                    from Producto as p, Departamento as d, Producto_Familia as pf
                    where p.departamento=d.id and p.familia=pf.id
                    group by nombre
                    order by codigo asc
                ");

                $query->execute();

                $productos = $query->fetchAll();
            }
            else
            {
                $query = $this->db->prepare("
                    select p.nombre as nombre, 
                    p.nombre as nombre_viejo, 
                    p.id as id, 
                    p.descripcion as descripcion, 
                    p.estado as estado, 
                    d.nombre as departamento_nombre, 
                    d.id as departamento, 
                    (select costo from Producto_Costo where producto=p.id and eliminado=0 order by fecha desc limit 1) as costo_unitario, 
                    pf.id as familia, 
                    pf.nombre as familia_nombre,
                    (select sum(pm.cantidad * (s.costo / s.cantidad)) from Producto_Material as pm, Stock as s where pm.producto=p.id and pm.material=s.material and s.eliminado=0 and s.cantidad_disponible>0) as costo_materiales,
                    cast((select (
                            case when (
                                select sum(s.restante) as disponible
                                from Stock_Personal as s
                                where pm.material=s.material and s.eliminado=0 and s.personal=(select id from Personal where usuario=:login_username)
                                group by s.material
                            ) is not null then (
                                select sum(s.restante) as disponible
                                from Stock_Personal as s
                                where pm.material=s.material and s.eliminado=0 and s.personal=(select id from Personal where usuario=:login_username)
                                group by s.material
                            )
                            else 0 end
                        ) 
                        / pm.cantidad as disponibles
                        from Producto_Material as pm
                        where pm.producto=p.id
                        order by disponibles asc
                        limit 1) as unsigned
                        ) as disponibles, 
                        p.exento_iva as exento_iva, 
                        concat(pf.id, p.id) as codigo, 
                        p.tokens as tokens
                    from Producto as p, Departamento as d, Producto_Familia as pf
                    where p.departamento=d.id and p.familia=pf.id
                    group by nombre
                    order by codigo asc
                ");

                $query->execute(array(
                    ":did" => $post['did'],
                    ":login_username" => isset($_SESSION['login_username']) ? $_SESSION['login_username'] : $post['login_username']
                ));

                $productos = $query->fetchAll();
            }

            for ($i = 0; $i < count($productos); $i++)
            {
                /* Cast a numero aquellos necesarios */
                $productos[$i]['disponibles'] = intval($productos[$i]['disponibles']);

                /* Historial de precios */
                $productos[$i]['historial_costos'] = array();

                $query = $this->db->prepare("
                    select pc.id, costo, date_format(pc.fecha, '%d/%m/%Y') as fecha, time_format(pc.fecha, '%h:%i:%s %p') as hora
                    from Producto_Costo as pc
                    where eliminado=0 and producto=:pid
                    order by pc.fecha desc
                ");

                $query->execute(array(
                    ":pid" => $productos[$i]['id']
                ));

                $productos[$i]['historial_costos'] = $query->fetchAll();

                /* Materiales */
                $productos[$i]['materiales'] = array();

                $query = $this->db->prepare("
                    select m.id as material, pm.cantidad as cantidad
                    from Producto_Material as pm, Material as m
                    where pm.material=m.id and pm.producto=:pid
                ");

                $query->execute(array(
                    ":pid" => $productos[$i]['id']
                ));

                $materiales = $query->fetchAll();

                foreach ($materiales as $p)
                {
                    $nuevo = array();

                    $nuevo['material'] = $p['material'];
                    $nuevo['cantidad'] = intval($p['cantidad']);

                    $productos[$i]['materiales'][] = $nuevo;
                }

                /* Guias */
                $productos[$i]['guias'] = array();

                $query = $this->db->prepare("
                    select g.id as guia
                    from Producto_Guia as pg, Guia as g
                    where pg.guia=g.id and pg.producto=:pid
                ");

                $query->execute(array(
                    ":pid" => $productos[$i]['id']
                ));

                $guias = $query->fetchAll();

                foreach ($guias as $p)
                {
                    $nuevo = array();

                    $nuevo['guia'] = $p['guia'];

                    $productos[$i]['guias'][] = $nuevo;
                }
            }

            return json_encode($productos);
        }

        public function cargar_departamentos($post)
        {
            $query = $this->db->prepare("
                select *
                from Departamento
                order by nombre asc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_materiales_asignados($post)
        {
            $query = $this->db->prepare("
                select sp.id as id, sp.personal, sp.material, sp.cantidad, sp.restante, concat(p.nombre, ' ', p.apellido) as personal_nombre, m.nombre as material_nombre, date_format(sp.fecha, '%d/%m/%Y') as fecha, time_format(sp.fecha, '%h:%i:%s %p') as hora
                from Stock_Personal as sp, Personal as p, Material as m
                where sp.personal=p.id and sp.material=m.id and sp.eliminado=0
                order by p.nombre asc
            ");

            $query->execute();

            $ret = $query->fetchAll();

            for ($i = 0; $i < count($ret); $i++)
            {
                /* Departamentos */
                $ret[$i]["departamentos"] = array();

                $query = $this->db->prepare("
                    select pd.departamento as departamento, d.nombre as departamento_nombre
                    from Personal_Departamento as pd, Departamento as d
                    where pd.departamento=d.id and pd.personal=:usuario
                ");

                $query->execute(array(
                    ":usuario" => $ret[$i]['personal']
                ));

                $departamentos = $query->fetchAll();

                foreach ($departamentos as $p)
                    $ret[$i]["departamentos"][] = $p['departamento_nombre'];
            }

            return json_encode($ret);
        }

        public function cargar_mis_materiales_asignados($post)
        {
            @session_start();

            $query = $this->db->prepare("
                select sp.id as id, sp.personal, sp.material, sp.cantidad, sp.restante, concat(p.nombre, ' ', p.apellido) as personal_nombre, m.nombre as material_nombre, date_format(sp.fecha, '%d/%m/%Y') as fecha, time_format(sp.fecha, '%h:%i:%s %p') as hora
                from Stock_Personal as sp, Personal as p, Material as m
                where sp.personal=p.id and sp.material=m.id and sp.eliminado=0
                    and p.usuario=:usuario
                order by p.nombre asc
            ");

            $query->execute(array(
                ":usuario" => isset($_SESSION['login_username']) ? $_SESSION['login_username'] : 'root'
            ));

            return json_encode($query->fetchAll());
        }

        public function cargar_materiales_danados($post)
        {
            @session_start();

            $query = $this->db->prepare("
                select spd.id as id, spd.cantidad, sp.restante, concat(p.nombre, ' ', p.apellido) as personal_nombre, m.nombre as material_nombre, date_format(spd.fecha, '%d/%m/%Y') as fecha, time_format(spd.fecha, '%h:%i:%s %p') as hora
                from Stock_Personal as sp, Personal as p, Material as m, Stock_Personal_Danado as spd
                where sp.personal=p.id and sp.material=m.id and spd.stock=sp.id
                    and p.usuario=:usuario
                order by p.nombre asc
            ");

            $query->execute(array(
                ":usuario" => isset($_SESSION['login_username']) ? $_SESSION['login_username'] : 'root'
            ));

            return json_encode($query->fetchAll());
        }

        public function cargar_departamentos_ucab($post)
        {
            $query = $this->db->prepare("
                select *
                from Departamento_UCAB
                order by nombre asc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_tipos_guias($post)
        {
            $query = $this->db->prepare("
                select *
                from Tipo_Guia
                order by id asc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_profesores($post)
        {
            $query = $this->db->prepare("
                select id, nombre, apellido, cedula, telefono, concat(nombre, ' ', apellido) as nombre_completo, estado, email
                from Profesor
                order by nombre asc;
            ");
            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_ordenes($post)
        {
            $query = $this->db->prepare("
                select o.id as id, o.numero as numero, d.nombre as dependencia, o.observaciones as observaciones, o.estado as estado, d.id as did, (select (case when sum(precio_total) is not null then sum(precio_total) else 0 end) as total from Orden_Producto where orden=o.id) as costo_total, date_format(o.fecha_modificada, '%d/%m/%Y') as fecha_modificada, date_format(o.fecha_anadida, '%d/%m/%Y') as fecha_anadida, o.procesada as procesada, o.fecha as fecha, date_format(o.fecha, '%d/%m/%Y') as fecha_str
                from Orden as o, Dependencia as d
                where o.dependencia=d.id
                order by o.id desc
            ");
            $query->execute();

            $ordenes = $query->fetchAll();

            for ($i = 0; $i < count($ordenes); $i++)
            {
                /* Productos */
                $ordenes[$i]['productos'] = array();

                $query = $this->db->prepare("
                    select p.id as producto, op.nro_copias as nro_copias, op.nro_originales as nro_originales, (select costo from Producto_Costo where producto=p.id and eliminado=0 order by fecha desc limit 1) as costo_unitario, op.precio_unitario as costo_unitario_facturado, op.precio_total as costo_total_facturado, op.id as opid, date_format(op.fecha_anadido, '%d/%m/%Y') as fecha_anadido, p.nombre as producto_nombre
                    from Orden_Producto as op, Producto as p
                    where op.producto=p.id
                    and op.orden=:orden
                ");

                $query->execute(array(
                    ":orden" => $ordenes[$i]['id']
                ));

                $productos = $query->fetchAll();

                foreach ($productos as $p)
                {
                    $nuevo = array();

                    $nuevo['producto'] = $p['producto'];
                    $nuevo['producto_nombre'] = $p['producto_nombre'];
                    $nuevo['opid'] = $p['opid'];
                    $nuevo['fecha_anadido'] = $p['fecha_anadido'];
                    $nuevo['copias'] = intval($p['nro_copias']);
                    $nuevo['originales'] = intval($p['nro_originales']);
                    $nuevo['costo_unitario'] = floatval($p['costo_unitario']);
                    $nuevo['costo_unitario_facturado'] = floatval($p['costo_unitario_facturado']);
                    $nuevo['costo_total_facturado'] = floatval($p['costo_total_facturado']);

                    $ordenes[$i]['productos'][] = $nuevo;
                }
            }

            return json_encode($ordenes);
        }

        public function cargar_pedidos($post)
        {
            $this->cancelar_pedidos_expirados();

            $query = $this->db->prepare("
                select R.*, cp.id as cond_pago, cp.nombre as metodo_pago
                from (
                    select o.id as id, o.numero as numero, o.observaciones as observaciones, o.estado as estado, (select (case when sum(precio_total) is not null then sum(precio_total) else 0 end) as total from Pedido_Producto where pedido=o.id) as costo_total, date_format(o.fecha_modificada, '%d/%m/%Y') as fecha_modificada, date_format(o.fecha_anadida, '%d/%m/%Y') as fecha_anadida, o.procesada as procesada, c.id as cliente, c.nombre as cliente_nombre, c.ni as cliente_ni, o.cond_pago as cond_pago_, concat(p.nombre, ' ', p.apellido) as creado_por, TIMESTAMPDIFF(SECOND, o.fecha_anadida, now()) as tiempo_restante, (case when o.departamento is null then 'Administrador' else (select nombre from Departamento where id=o.departamento) end) as departamento
                    from Pedido as o, Cliente as c, Personal as p
                    where o.cliente=c.id and o.creado_por=p.id and o.procesada=0
                    order by o.id desc
                ) R left join Condicion_Pago as cp
                on R.cond_pago_=cp.id
            ");
            $query->execute();

            $pedidos = $query->fetchAll();

            for ($i = 0; $i < count($pedidos); $i++)
            {
                /* Productos */
                $pedidos[$i]['productos'] = array();

                $query = $this->db->prepare("
                    select p.id as producto, op.nro_copias as nro_copias, op.nro_originales as nro_originales, (select costo from Producto_Costo where producto=p.id and eliminado=0 order by fecha desc limit 1) as costo_unitario, op.precio_unitario as costo_unitario_facturado, op.precio_total as costo_total_facturado, op.id as opid, date_format(op.fecha_anadido, '%d/%m/%Y') as fecha_anadido, p.nombre as producto_nombre, p.descripcion as descripcion, p.exento_iva as exento_iva
                    from Pedido_Producto as op, Producto as p
                    where op.producto=p.id
                    and op.pedido=:pedido
                ");

                $query->execute(array(
                    ":pedido" => $pedidos[$i]['id']
                ));

                $productos = $query->fetchAll();

                foreach ($productos as $p)
                {
                    $nuevo = array();

                    $nuevo['producto'] = $p['producto'];
                    $nuevo['producto_nombre'] = $p['producto_nombre'];
                    $nuevo['descripcion'] = $p['descripcion'];
                    $nuevo['exento_iva'] = $p['exento_iva'];
                    $nuevo['opid'] = $p['opid'];
                    $nuevo['fecha_anadido'] = $p['fecha_anadido'];
                    $nuevo['copias'] = intval($p['nro_copias']);
                    $nuevo['originales'] = intval($p['nro_originales']);
                    $nuevo['costo_unitario'] = floatval($p['costo_unitario']);
                    $nuevo['costo_unitario_facturado'] = floatval($p['costo_unitario_facturado']);
                    $nuevo['costo_total_facturado'] = floatval($p['costo_total_facturado']);

                    $pedidos[$i]['productos'][] = $nuevo;
                }
            }

            return json_encode($pedidos);
        }

        public function cargar_factura($post)
        {
            $query = $this->db->prepare("
                select 
                    o.id as id,
                    date_format(pp.fecha_creado, '%d/%m/%Y') as fecha, 
                    c.nombre as cliente, 
                    c.ni as cliente_ni,
                    cp.nombre as metodo_pago,
                    pp.monto as monto,
                    pp.cambio as cambio,
                    pp.subtotal as subtotal,
                    pp.iva as iva,
                    pp.total as total,
                    pp.nro_factura as nro_factura
                from Pedido as o, Cliente as c, Condicion_Pago as cp, Pago_Pedido as pp
                where o.cliente=c.id and pp.metodo_pago=cp.id and pp.pedido=o.id and pp.nro_factura=:factura
                order by o.id desc
            ");

            $query->execute(array(
                ":factura" => $post['factura']
            ));

            if ($query->rowCount() == 0)
            {
                $json = array();
                $json["error"] = "Número de factura inexistente.";
                return json_encode($json);
            }

            $pedido = $query->fetchAll();
            $pedido = $pedido[0];

            /* Productos */
            $pedido['productos'] = array();

            $query = $this->db->prepare("
                select p.id as producto, op.nro_copias as nro_copias, op.nro_originales as nro_originales, (select costo from Producto_Costo where producto=p.id and eliminado=0 order by fecha desc limit 1) as costo_unitario, op.precio_unitario as costo_unitario_facturado, op.precio_total as costo_total_facturado, op.id as opid, date_format(op.fecha_anadido, '%d/%m/%Y') as fecha_anadido, p.nombre as producto_nombre, p.descripcion as descripcion, p.exento_iva as exento_iva
                from Pedido_Producto as op, Producto as p
                where op.producto=p.id
                and op.pedido=:pedido
            ");

            $query->execute(array(
                ":pedido" => $pedido['id']
            ));

            $productos = $query->fetchAll();

            foreach ($productos as $p)
            {
                $nuevo = array();

                $nuevo['producto'] = $p['producto'];
                $nuevo['producto_nombre'] = $p['producto_nombre'];
                $nuevo['descripcion'] = $p['descripcion'];
                $nuevo['exento_iva'] = $p['exento_iva'];
                $nuevo['fecha_anadido'] = $p['fecha_anadido'];
                $nuevo['copias'] = intval($p['nro_copias']);
                $nuevo['originales'] = intval($p['nro_originales']);
                $nuevo['costo_unitario'] = floatval($p['costo_unitario']);
                $nuevo['costo_unitario_facturado'] = floatval($p['costo_unitario_facturado']);
                $nuevo['costo_total_facturado'] = floatval($p['costo_total_facturado']);

                $pedido['productos'][] = $nuevo;
            }

            return $pedido;
        }

        public function cargar_dependencias($post)
        {
            $query = $this->db->prepare("
                select * from Dependencia
            ");
            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_familias($post)
        {
            $query = $this->db->prepare("
                select * from Producto_Familia
            ");
            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_periodos($post)
        {
            $query = $this->db->prepare("
                select cp.id as id, (case when p.numero=99 then 'Otro' else p.numero end) as periodo, p.tipo as tipo
                from Car_Per as cp, Periodo as p
                where cp.periodo=p.id and cp.carrera=:cid
                order by p.numero asc
            ");

            $query->execute(array(
                ":cid" => $post['cid']
            ));

            return json_encode($query->fetchAll());
        }

        public function cargar_personal($post)
        {
            $query = $this->db->prepare("
                select *, concat(nombre, ' ', apellido) as nombre_completo
                from Personal
                order by nombre asc
            ");

            $query->execute();
            $ret = $query->fetchAll();

            /* Permisos */
            for ($i = 0; $i < count($ret); $i++)
            {
                $ret[$i]["permisos"] = "";
                $ret[$i]["snombre"] = $ret[$i]["segundo_nombre"];
                $ret[$i]["sapellido"] = $ret[$i]["segundo_apellido"];

                $query = $this->db->prepare("
                    select p.id as id
                    from Permiso_Asignado as pa, Permiso as p
                    where pa.permiso=p.id and usuario=:usuario
                ");

                $query->execute(array(
                    ":usuario" => $ret[$i]['id']
                ));

                $permisos = $query->fetchAll();

                foreach ($permisos as $p)
                    $ret[$i]["permisos"] .= "[" . $p['id'] . "]";


                /* Departamentos */
                $ret[$i]["departamentos"] = array();
                $ret[$i]["departamentos_str"] = array();

                $query = $this->db->prepare("
                    select pd.departamento as departamento, d.nombre as departamento_nombre
                    from Personal_Departamento as pd, Departamento as d
                    where pd.departamento=d.id and pd.personal=:usuario
                ");

                $query->execute(array(
                    ":usuario" => $ret[$i]['id']
                ));

                $departamentos = $query->fetchAll();

                foreach ($departamentos as $p)
                {
                    $ret[$i]["departamentos"][] = $p['departamento'];
                    $ret[$i]["departamentos_str"][] = $p['departamento_nombre'];
                }
            }

            return json_encode($ret);
        }

        public function cargar_permisos($post)
        {
            $query = $this->db->prepare("
                select p.id as id, p.nombre as nombre, p.descripcion as descripcion, p.riesgo as riesgo, pc.nombre as categoria
                from Permiso as p, Permiso_Categoria as pc
                where p.categoria=pc.id
                order by pc.id asc
            ");
            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_profesor($id)
        {
            $query = $this->db->prepare("call obtener_profesor(:id)");

            $query->execute(array(
                ":id" => $id
            ));

            $d = $query->fetchAll();
            $d = $d[0];

            return $d;
        }

        public function cargar_personal_($id)
        {
            $query = $this->db->prepare("call obtener_personal_id(:id)");
            
            $query->execute(array(
                ":id" => $id
            ));

            $d = $query->fetchAll();
            $d = $d[0];

            return $d;
        }

        public function obtener_carrera_desde_materia($id_materia)
        {
            $query = $this->db->prepare("
                select c.id as id, c.nombre as nombre
                from Materia as m, Car_Per as cp, Carrera as c
                where m.dictada_en=cp.id and cp.carrera=c.id and m.id=:id
            ");
            
            $query->execute(array(
                ":id" => $id_materia
            ));

            $d = $query->fetchAll();
            $d = $d[0];

            return $d;
        }

        public function cargar_guias_web($post)
        {
            $query = $this->db->prepare("
                select *, date_format(fecha, '%d/%m/%Y') as fecha_arreglada, time_format(fecha, '%h:%i:%s %p') as hora
                from Guia_Web
                where revisada=0
                order by fecha desc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_planes_de_estudio($post)
        {
            $query = $this->db->prepare("
                select date_format(pe.fecha, '%d/%m/%Y') as fecha, time_format(pe.fecha, '%h:%i:%s %p') as hora, pe.id as id, pe.titulo as titulo, c.id as carrera_id, c.nombre as carrera, pe.mencion as mencion_id, pe.materia as materia_id, pe.tipo as tipo, pe.pdf as pdf, pe.paginas as paginas, pe.hojas as hojas
                from Plan_de_Estudio as pe, Carrera as c
                where pe.carrera=c.id
                order by pe.fecha desc
            ");

            $query->execute();
            $planes = $query->fetchAll();

            for ($i = 0; $i < count($planes); $i++)
            {
                // Pego la mencion
                $m = array();

                if ($planes[$i]['mencion_id'] != null)
                {
                    $query = $this->db->prepare("
                        select nombre
                        from Mencion
                        where id=:mid
                    ");

                    $query->execute(array(
                        ":mid" => $planes[$i]['mencion_id']
                    ));

                    $m = $query->fetchAll();
                }

                $planes[$i]['mencion'] = count($m) > 0 ? $m[0]['nombre'] : null;

                // Pego la materia
                $m = array();
                
                if ($planes[$i]['materia_id'] != null)
                {
                    $query = $this->db->prepare("
                        select m.nombre as nombre
                        from Materia as m, Car_Per as cp, Carrera as c
                        where m.dictada_en=cp.id and cp.carrera=c.id
                        and c.id=:cid
                    ");

                    $query->execute(array(
                        ":cid" => $planes[$i]['carrera_id']
                    ));

                    $m = $query->fetchAll();
                }

                $planes[$i]['materia'] = count($m) > 0 ? $m[0]['nombre'] : null;
            }

            return json_encode($planes);
        }

        public function cargar_guias($post)
        {
            $query = $this->db->prepare("
                select g.*, date_format(g.fecha_anadida, '%d/%m/%Y') as fecha, time_format(g.fecha_anadida, '%h:%i:%s %p') as hora, m.nombre as materia_nombre, p.numero as periodo, m.id as materia_id, (select costo from Producto_Costo where producto=g.idmaterial and eliminado=0 order by fecha desc limit 1) * (select cantidad from Producto_Material where producto=g.idproducto) as precio
                from Guia as g, Materia as m, Car_Per as cp, Periodo as p
                where g.materia=m.id and cp.id=m.dictada_en and cp.periodo=p.id and g.status=:status
                order by g.id desc
            ");

            $query->execute(array(
                ":status" => $post["status"]
            ));

            $gs = $query->fetchAll();
            $guias = array();

            foreach ($gs as $g)
            {
                $row = $g;
                // Guardo los ID
                $row["entregada_por_id"] = $row["entregada_por"];
                $row["profesor_id"] = $row["profesor"];
                $row["recibida_por_id"] = $row["recibida_por"];
                $carrera = $this->obtener_carrera_desde_materia($g['materia']);
                $row["carrera_id"] = $carrera['id'];
                $row["carrera_nombre"] = $carrera['nombre'];

                // Reemplazo por los valores de la foranea
                $row["entregada_por"] = $this->cargar_profesor($row["profesor"]);
                $row["profesor"] = $row["entregada_por"];
                $row["recibida_por"] = $this->cargar_personal_($row["recibida_por"]);

                if ($row["status"] == -1)
                    $row["status_str"] = "rechazada";
                else if ($row["status"] == 0)
                    $row["status_str"] = "pendiente";
                else if ($row["status"] == 1)
                    $row["status_str"] = "aprobada";
                else if ($row["status"] == 2)
                    $row["status_str"] = "inactiva";

                $productos = json_decode($this->cargar_productos(array()), true);

                foreach ($productos as $p)
                    if ($p['id'] == $g['idmaterial'])
                    {
                        $row['producto'] = array();
                        $row['producto']['id'] = $p['id'];
                        $row['producto']['cantidad'] = $p['materiales'][0]['cantidad'];
                    }

                $guias[] = $row;
            }

            return json_encode($guias);
        }

        public function cargar_guia($post)
        {
            $query = $this->db->prepare("
                select g.*, date_format(g.fecha_anadida, '%d/%m/%Y') as fecha, time_format(g.fecha_anadida, '%h:%i:%s %p') as hora
                from Guia as g
                where g.codigo=:codigo
                order by g.id desc
            ");

            $query->execute(array(
                ":codigo" => $post["codigo"]
            ));

            $gs = $query->fetchAll();
            $guias = array();

            foreach ($gs as $g)
            {
                $row = $g;
                // Guardo los ID
                $row["entregada_por_id"] = $row["entregada_por"];
                $row["profesor_id"] = $row["profesor"];
                $row["recibida_por_id"] = $row["recibida_por"];
                $carrera = $this->obtener_carrera_desde_materia($g['materia']);
                $row["carrera_id"] = $carrera['id'];
                $row["carrera_nombre"] = $carrera['nombre'];

                // Reemplazo por los valores de la foranea
                $row["entregada_por"] = $this->cargar_profesor($row["profesor"]);
                $row["profesor"] = $row["entregada_por"];
                $row["recibida_por"] = $this->cargar_personal_($row["recibida_por"]);

                if ($row["status"] == -1)
                    $row["status_str"] = "rechazada";
                else if ($row["status"] == 0)
                    $row["status_str"] = "pendiente";
                else if ($row["status"] == 1)
                    $row["status_str"] = "aprobada";
                else if ($row["status"] == 2)
                    $row["status_str"] = "inactiva";

                $productos = json_decode($this->cargar_productos(array()), true);

                foreach ($productos as $p)
                    if ($p['id'] == $g['idmaterial'])
                    {
                        $row['producto'] = array();
                        $row['producto']['id'] = $p['id'];
                        $row['producto']['cantidad'] = $p['materiales'][0]['cantidad'];
                    }

                $guias[] = $row;
            }

            return json_encode($guias[0]);
        }

        public function cargar_clientes($post)
        {
            $query = $this->db->prepare("
                select * from Cliente
            ");
            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_cajeros($post)
        {
            $query = $this->db->prepare("
                select p.id as id, concat(p.nombre, ' ', p.apellido) as nombre_completo
                from Personal as p, Pago_Pedido as pp
                where pp.creado_por=p.id
                group by p.id
                order by p.nombre asc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_cajeros_activos($post)
        {
            $query = $this->db->prepare("
                select p.id as id, concat(p.nombre, ' ', p.apellido) as nombre_completo
                from Personal as p, Personal_Departamento as pd, Departamento as d
                where pd.personal=p.id and pd.departamento=d.id and d.nombre='Caja'
                order by p.nombre asc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function reporte_libro_de_ventas($post)
        {
            @session_start();

            $cajero = isset($post['cajero']) ? $post['cajero'] : $_SESSION['cajero'];

            $query = $this->db->prepare("
                select o.id as id, o.numero as numero, o.observaciones as observaciones, o.estado as estado, (select (case when sum(precio_total) is not null then sum(precio_total) else 0 end) as total from Pedido_Producto where pedido=o.id) as costo_total, date_format(o.fecha_modificada, '%d/%m/%Y') as fecha_modificada, date_format(o.fecha_anadida, '%d/%m/%Y') as fecha_anadida, o.procesada as procesada, c.id as cliente, c.nombre as cliente_nombre, c.ni as cliente_ni, o.cond_pago as cond_pago_, concat(p.nombre, ' ', p.apellido) as creado_por, TIMESTAMPDIFF(SECOND, o.fecha_anadida, now()) as tiempo_restante, (case when o.departamento is null then 'Administrador' else o.departamento end) as departamento, pp.total as total, pp.subtotal as subtotal, pp.iva as iva, pp.id as id_factura, mp.nombre as metodo_pago, pp.nro_factura as nro_factura
                from Pedido as o, Cliente as c, Personal as p, Pago_Pedido as pp, Condicion_Pago as mp
                where o.cliente=c.id and o.creado_por=p.id and pp.pedido=o.id and pp.metodo_pago=mp.id
                    ".($cajero != "-1" ? "and pp.creado_por=" . $cajero . " " : "")."
                    and date(o.fecha_anadida) between :desde and :hasta
                order by o.id desc
            ");

            $query->execute(array(
                ":desde" => isset($post['desde_']) ? $post['desde_'] : $_SESSION['desde_'],
                ":hasta" => isset($post['hasta_']) ? $post['hasta_'] : $_SESSION['hasta_']
            ));

            $pedidos = $query->fetchAll();

            for ($i = 0; $i < count($pedidos); $i++)
            {
                /* Productos */
                $pedidos[$i]['productos'] = array();

                $query = $this->db->prepare("
                    select p.id as producto, op.nro_copias as nro_copias, op.nro_originales as nro_originales, (select costo from Producto_Costo where producto=p.id and eliminado=0 order by fecha desc limit 1) as costo_unitario, op.precio_unitario as costo_unitario_facturado, op.precio_total as costo_total_facturado, op.id as opid, date_format(op.fecha_anadido, '%d/%m/%Y') as fecha_anadido, p.nombre as producto_nombre, p.descripcion as descripcion, p.exento_iva as exento_iva
                    from Pedido_Producto as op, Producto as p
                    where op.producto=p.id
                    and op.pedido=:pedido
                ");

                $query->execute(array(
                    ":pedido" => $pedidos[$i]['id']
                ));

                $productos = $query->fetchAll();

                foreach ($productos as $p)
                {
                    $nuevo = array();

                    $nuevo['producto'] = $p['producto'];
                    $nuevo['producto_nombre'] = $p['producto_nombre'];
                    $nuevo['descripcion'] = $p['descripcion'];
                    $nuevo['exento_iva'] = $p['exento_iva'];
                    $nuevo['opid'] = $p['opid'];
                    $nuevo['fecha_anadido'] = $p['fecha_anadido'];
                    $nuevo['copias'] = intval($p['nro_copias']);
                    $nuevo['originales'] = intval($p['nro_originales']);
                    $nuevo['costo_unitario'] = floatval($p['costo_unitario']);
                    $nuevo['costo_unitario_facturado'] = floatval($p['costo_unitario_facturado']);
                    $nuevo['costo_total_facturado'] = floatval($p['costo_total_facturado']);

                    $pedidos[$i]['productos'][] = $nuevo;
                }

                /* Nota de credito */
                $pedidos[$i]['nota_credito'] = array();

                $query = $this->db->prepare("
                    select sum(subtotal) as subtotal, sum(iva) as iva, sum(total) as total
                    from Nota_Credito
                    where nro_factura=:nro_factura
                ");

                $query->execute(array(
                    ":nro_factura" => $pedidos[$i]['nro_factura']
                ));

                $nota_credito = $query->fetchAll();

                if (count($nota_credito) > 0)
                {
                    $pedidos[$i]['nota_credito']['subtotal'] = $nota_credito[0]['subtotal'];
                    $pedidos[$i]['nota_credito']['iva'] = $nota_credito[0]['iva'];
                    $pedidos[$i]['nota_credito']['total'] = $nota_credito[0]['total'];
                }
            }

            return json_encode($pedidos);
        }

        public function reporte_cuadre_ventas_diarias($post)
        {
            @session_start();

            $query = $this->db->prepare("
                select 
                    concat(p.nombre, ' ', p.apellido) as nombre_completo, 
                    sum(pp.total) as total_facturado, 
                    0 as devoluciones, 
                    (case when (select sum(total) from Nota_Credito where nro_factura=pp.nro_factura) is not null then (-1)*(select sum(total) from Nota_Credito where nro_factura=pp.nro_factura) else 0 end) as nota_de_credito, 
                    (case when (select sum(monto) from Retiro_Caja where personal=p.id and date(fecha)=date(pp.fecha_creado)) is not null then (select sum(monto) from Retiro_Caja where personal=p.id and date(fecha)=date(pp.fecha_creado)) else 0 end) as retiro_de_caja
                from Pago_Pedido as pp, Personal as p
                where pp.creado_por=p.id and date(pp.fecha_creado)=:dia
                group by p.id
            ");

            $query->execute(array(
                ":dia" => isset($post['dia_']) ? $post['dia_'] : $_SESSION['dia_']
            ));

            $data = $query->fetchAll();

            return json_encode($data);
        }

        public function reporte_venta_productos($post)
        {
            @session_start();

            $query = $this->db->prepare("
                select 
                    sum(ppr.precio_total) as total,
                    sum(ppr.cantidad) as cantidad,
                    p.nombre as nombre, 
                    concat(pf.id, p.id) as codigo,
                    p.exento_iva as exento_iva,
                    p.familia as familia,
                    p.id as producto_id
                from Pago_Pedido as pp, Pedido_Producto as ppr, Producto as p, Producto_Familia as pf, Pedido as o
                where pp.pedido=ppr.pedido and pp.pedido=o.id and ppr.producto=p.id and p.familia=pf.id and date(o.fecha_anadida) between :desde and :hasta
                group by p.id, p.familia
            ");

            $query->execute(array(
                ":desde" => isset($post['desde_']) ? $post['desde_'] : $_SESSION['desde_'],
                ":hasta" => isset($post['hasta_']) ? $post['hasta_'] : $_SESSION['hasta_']
            ));

            $data = $query->fetchAll();

            return json_encode($data);
        }

        public function cargar_retiros_de_caja($post)
        {
            $query = $this->db->prepare("
                select 
                    rc.id as id,
                    rc.monto as monto, 
                    rc.concepto as concepto, 
                    date_format(rc.fecha, '%d/%m/%Y') as fecha,
                    a.usuario as administrador,
                    concat(a.nombre, ' ', a.apellido) as admin_nombre,
                    c.id as cajero,
                    concat(c.nombre, ' ', c.apellido) as cajero_nombre
                from Retiro_Caja as rc, Personal as a, Personal as c
                where rc.creado_por=a.id and rc.personal=c.id
                order by rc.fecha desc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function reporte_corte_de_caja($post)
        {
            @session_start();

            $query = $this->db->prepare("
                select 
                    concat(p.nombre, ' ', p.apellido) as nombre_completo,
                    date_format(now(), '%d/%m/%Y') as fecha, 
                    time_format(now(), '%h:%i:%s %p') as hora,
                    sum(pp.total) as total_facturado,
                    (case when (select sum(total) from Nota_Credito where nro_factura=pp.nro_factura) is not null then (select sum(total) from Nota_Credito where nro_factura=pp.nro_factura) else 0 end) as total_notas_credito,
                    0 as total_devoluciones,
                    (case when (select sum(monto) from Retiro_Caja where personal=p.id and date(fecha)=date(pp.fecha_creado)) is not null then (select sum(monto) from Retiro_Caja where personal=p.id and date(fecha)=date(pp.fecha_creado)) else 0 end) as total_retiros
                from Personal as p, Pago_Pedido as pp
                where p.id=:cajero and pp.creado_por=p.id and date(pp.fecha_creado)=date(now())
                group by p.id
            ");

            $query->execute(array(
                ":cajero" => isset($post['cajero']) ? $post['cajero'] : $_SESSION['cajero']
            ));

            $data = $query->fetchAll();

            if (count($data) == 0)
            {
                $query = $this->db->prepare("
                    select 
                        concat(p.nombre, ' ', p.apellido) as nombre_completo,
                        date_format(now(), '%d/%m/%Y') as fecha, 
                        time_format(now(), '%h:%i:%s %p') as hora,
                        0 as total_facturado,
                        0 as total_notas_credito,
                        0 as total_devoluciones,
                        0 as total_retiros
                    from Personal as p
                    where p.id=:cajero
                ");

                $query->execute(array(
                    ":cajero" => isset($post['cajero']) ? $post['cajero'] : $_SESSION['cajero']
                ));

                $data = $query->fetchAll();
            }
            else
                for ($i = 0; $i < count($data); $i++)
                {
                    /* Aseguro los tipos de dato */
                    $data[$i]['total_facturado'] = floatval($data[$i]['total_facturado']);
                    $data[$i]['total_notas_credito'] = floatval($data[$i]['total_notas_credito']);
                    $data[$i]['total_devoluciones'] = floatval($data[$i]['total_devoluciones']);
                    $data[$i]['total_retiros'] = floatval($data[$i]['total_retiros']);

                    /* Cargo los cobros */
                    $data[$i]['cobros'] = array();

                    $query = $this->db->prepare("
                        select *
                        from Condicion_Pago
                    ");

                    $query->execute();

                    $tipo_pago = $query->fetchAll();

                    foreach ($tipo_pago as $t)
                    {
                        $query = $this->db->prepare("
                            select 
                                tp.nombre as tipo_pago,
                                count(tp.id) as cantidad,
                                sum(pp.total) as monto
                            from Condicion_Pago as tp, Pago_Pedido as pp
                            where pp.metodo_pago=tp.id and tp.id=:tid and date(pp.fecha_creado)=date(now())
                            group by tp.id
                        ");

                        $query->execute(array(
                            ":tid" => $t['id']
                        ));

                        $row = $query->fetchAll();

                        if ($query->rowCount() > 0)
                            $data[$i]['cobros'][] = $row[0];
                    }

                    /* Cargo los retiros */
                    $data[$i]['retiros'] = array();

                    $query = $this->db->prepare("
                        select 
                            concat(a.nombre, ' ', a.apellido) as autorizado_por,
                            rc.concepto as concepto,
                            date_format(rc.fecha, '%d/%m/%Y') as fecha, 
                            time_format(rc.fecha, '%h:%i:%s %p') as hora,
                            rc.monto as monto
                        from Retiro_Caja as rc, Personal as a, Personal as p
                        where rc.creado_por=a.id and rc.personal=p.id and rc.personal=:cajero and date(rc.fecha)=date(now())
                    ");

                    $query->execute(array(
                        ":cajero" => isset($post['cajero']) ? $post['cajero'] : $_SESSION['cajero']
                    ));

                    $data[$i]['retiros'] = $query->fetchAll();
                }

            return count($data) > 0 ? json_encode($data[0]) : json_encode(array());
        }
	}
?>