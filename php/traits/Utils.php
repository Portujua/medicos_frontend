<?php
	trait Utils {
		public function cancelar_pedidos_expirados()
        {
            // Cambio el estado
            $query = $this->db->prepare("
                update Pedido set
                    procesada=-1
                where TIMESTAMPDIFF(SECOND, fecha_anadida, now())>:duracion_pedido and procesada=0
            ");

            $query->execute(array(
                ":duracion_pedido" => $this->duracion_pedido
            ));

            // Borro del Stock_Temp todo lo que no este pendiente
            $query = $this->db->prepare("
                delete from Stock_Temp where pedido not in (select id from Pedido where procesada=0)
            ");

            $query->execute();
        }

        public function generar_tokens_guia($id)
        {
            $query = $this->db->prepare("
                select g.*, date_format(g.fecha_anadida, '%d/%m/%Y') as fecha, time_format(g.fecha_anadida, '%h:%i:%s %p') as hora, m.nombre as materia_nombre, p.numero as periodo
                from Guia as g, Materia as m, Car_Per as cp, Periodo as p
                where g.materia=m.id and cp.id=m.dictada_en and cp.periodo=p.id and g.id=:id
                order by g.id desc
            ");

            $query->execute(array(
                ":id" => $id
            ));

            $gs = $query->fetchAll();
            $guias = array();
            $tokens = "";

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
                $row["profesor"] = $this->cargar_profesor($row["profesor"]);
                $row["recibida_por"] = $this->cargar_personal_($row["recibida_por"]);

                $tokens .= "Titulo: " . $g['titulo'] . " ";
                $tokens .= "Codigo: " . $g['codigo'] . " ";
                $tokens .= "Carrera: " . $row['carrera_nombre'] . " ";
                $tokens .= "Materia: " . $g['materia_nombre'] . " ";
                $tokens .= "Profesor: " . $row['profesor']['nombre_completo'] . " ";
                $tokens .= "Periodo: " . $g['periodo'] . " ";                
                $tokens .= "Recibida por: " . $row['recibida_por']['nombre_completo'] . " ";
                $tokens .= "# Hojas: " . $g['numero_hojas'] . " ";
                $tokens .= "# Paginas: " . $g['numero_paginas'] . " ";
                $tokens .= "Seccion: " . $g['seccion'] . " ";
            }

            return $tokens;
        }

        public function puede_ver_guias($username)
        {
            $query = $this->db->prepare("
                select *
                from Personal as p, Permiso_Asignado as pa, Permiso as pe
                where pa.usuario=p.id and pa.permiso=pe.id
                and p.usuario=:username and pe.nombre='buscar_guias'
            ");

            $query->execute(array(
                ":username" => $username
            ));

            return $query->rowCount() > 0;
        }

        public function check_nro_orden($post)
        {
            $query = $this->db->prepare("
                select *
                from Orden
                where numero=:nro
            ");

            $query->execute(array(
                ":nro" => $post['nro']
            ));

            $json = array();
            $json['existe'] = $query->rowCount() > 0 ? true : false;
            $json['esValido'] = $query->rowCount() == 0 ? true : false;

            return json_encode($json);
        }

        public function check_factura($post)
        {
            $query = $this->db->prepare("
                select *
                from Pago_Pedido
                where nro_factura=:nro
            ");

            $query->execute(array(
                ":nro" => $post['nro']
            ));

            $json = array();
            $json['existe'] = $query->rowCount() > 0 ? false : true;
            $json['esValido'] = $query->rowCount() == 0 ? false : true;

            return json_encode($json);
        }

        public function check_nro_pedido($post)
        {
            $query = $this->db->prepare("
                select *
                from Pedido
                where numero=:nro
            ");

            $query->execute(array(
                ":nro" => $post['nro']
            ));

            $json = array();
            $json['existe'] = $query->rowCount() > 0 ? true : false;
            $json['esValido'] = $query->rowCount() == 0 ? true : false;

            return json_encode($json);
        }

        public function check_usuario($post)
        {
            $query = $this->db->prepare("
                select *
                from Personal
                where usuario=:username
            ");

            $query->execute(array(
                ":username" => $post['username']
            ));

            $json = array();
            $json['existe'] = $query->rowCount() > 0 ? true : false;
            $json['esValido'] = $query->rowCount() == 0 ? true : false;

            return json_encode($json);
        }

        public function check_producto($post)
        {
            $query = $this->db->prepare("
                select *
                from Producto
                where upper(nombre)=upper(:nombre)
            ");

            $query->execute(array(
                ":nombre" => $post['nombre']
            ));

            $json = array();
            $json['existe'] = $query->rowCount() > 0 ? true : false;
            $json['esValido'] = $query->rowCount() == 0 ? true : false;

            return json_encode($json);
        }

        public function check_cuentaabierta($post)
        {
            $query = $this->db->prepare("
                select *
                from CuentaAbierta
                where upper(nombre)=upper(:nombre)
            ");

            $query->execute(array(
                ":nombre" => $post['nombre']
            ));

            $json = array();
            $json['existe'] = $query->rowCount() > 0 ? true : false;
            $json['esValido'] = $query->rowCount() == 0 ? true : false;

            return json_encode($json);
        }

        public function chequear_disponibilidad($post)
        {
            $json = array();
            $json["errores"] = array(); // Arreglo de materiales que no hay
            $json["data"] = array();

            // Obtenemos los materiales necesarios para este producto
            $query = $this->db->prepare("
                select pm.material as material, pm.cantidad as cantidad, m.nombre as material_nombre
                from Producto_Material as pm, Material as m
                where pm.producto=:pid and pm.material=m.id
            ");

            $query->execute(array(
                ":pid" => $post['id']
            ));

            $materiales = $query->fetchAll();

            foreach ($materiales as $m)
            {
                $query = $this->db->prepare("
                    select 
                        (td.total - tr.total - ta.total) as cantidad_disponible,
                        tm.total cantidad_disponible_mia,
                        (select d.nombre from Personal as u, Personal_Departamento as pd, Departamento as d where u.usuario=:usuario and pd.departamento=d.id and pd.personal=u.id limit 1) as dpto
                    from (
                        select sum(s.cantidad_disponible) as total
                        from Stock as s
                        where s.material=:mid and s.eliminado=0 and s.cantidad_disponible>0
                    ) td, (
                        select (case when sum(st.cantidad) is not null then sum(st.cantidad) else 0 end) as total
                        from Stock_Temp as st
                        where st.material=:mid
                    ) tr, (
                        select (case when sum(st.restante) is not null then sum(st.restante) else 0 end) as total
                        from Stock_Personal as st
                        where st.material=:mid and st.personal!=(select id from Personal where usuario=:usuario)
                    ) as ta, (
                        select (case when sum(st.restante) is not null then sum(st.restante) else 0 end) as total
                        from Stock_Personal as st
                        where st.material=:mid and st.personal=(select id from Personal where usuario=:usuario)
                    ) as tm
                    where (td.total - tr.total - ta.total)>=:cantidad
                ");

                $query->execute(array(
                    ":mid" => $m['material'],
                    ":cantidad" => $post['cantidad'],
                    ":usuario" => $post['usuario']
                ));

                $data = $query->fetchAll();

                if ($query->rowCount() == 0)
                    $json['errores'][] = array(
                        "material" => $m['material_nombre'],
                        "producto" => $post['id']
                    );
                else
                {
                    $data = $data[0];

                    if (
                            ($data['dpto'] == null || $data['dpto'] == "Caja")
                            &&
                            (intval($data['cantidad_disponible']) < intval($post['cantidad']))
                        )
                        $json['errores'][] = array(
                            "material" => $m['material_nombre'],
                            "producto" => $post['id']
                        );
                    else if (
                            ($data['dpto'] != null && $data['dpto'] != "Caja")
                            &&
                            (intval($data['cantidad_disponible_mia']) < intval($post['cantidad']))
                        )
                        $json['errores'][] = array(
                            "material" => $m['material_nombre'],
                            "producto" => $post['id']
                        );
                }

                $json['data'][] = $data;
            }

            return json_encode($json);
        }

        public function csv_productos()
        {
            $csv = array();
            $csv[] = array("Código", "Nombre", "Descripción", "Familia", "Costo de venta", "Costo unitario", "Disponibles", "¿Incluye IVA?", "Estado");

            $productos = json_decode($this->cargar_productos(array()), true);
            
            foreach ($productos as $p)
                $csv[] = array($p['codigo'], 
                    $p['nombre'], 
                    $p['descripcion'], 
                    $p['familia_nombre'],
                    "Bs. " . number_format($p['costo_unitario'], 2, ",", ""),
                    "Bs. " . number_format($p['costo_materiales'], 2, ",", ""),
                    $p['disponibles'] . " unidades",
                    $p['exento_iva'] == '1' ? "Si" : "No",
                    $p['estado'] == '1' ? "Habilitado" : "Deshabilitado"
                );

            return $csv;
        }

        public function csv_inventario()
        {
            $csv = array();
            $csv[] = array("Nombre", "Cantidad disponible", "Fecha de último ingreso", "Estado");

            $inventario = json_decode($this->cargar_inventario(array()), true);
            
            foreach ($inventario as $p)
            {
                $str_asignado = "";

                foreach ($p['inventario_asignado'] as $i)
                    $str_asignado .= $i['personal'] . " " . $i['cantidad'] . "\r\n";

                $csv[] = array($p['nombre'], 
                    (intval($p['cantidad']) - intval($p['cantidad_asignada'])) . " disponible para asignar\r\n" . intval($p['cantidad_asignada']) . " asignado\r\n" . intval($p['cantidad']) . " en total\r\n\r\n\r\n" . $str_asignado, 
                    isset($p['fecha_ultimo_ingreso']) ? $p['fecha_ultimo_ingreso'] : 'Nunca',
                    $p['estado'] == '1' ? "Habilitado" : "Deshabilitado"
                );
            }

            return $csv;
        }

        public function autorizacion_admin($post)
        {
            $query = $this->db->prepare("
                select u.id as id
                from Personal as u
                where u.usuario in ".$this->admin_usernames." and u.contrasena=:password
                limit 1
            ");

            $query->execute(array(
                ":password" => $post['password']
            ));

            $u = $query->fetchAll();

            $json = array();
            $json['resultado'] = $query->rowCount() > 0 ? true : false;

            return json_encode($json);
        }

        public function obtener_iva()
        {
            $query = $this->db->prepare("
                select valor
                from IVA
                order by fecha desc
                limit 1
            ");

            $query->execute();

            $u = $query->fetchAll();

            return floatval($u[0]['valor']);
        }

        public function csv_reporte_libro_de_ventas()
        {
            $csv = array();
            $csv[] = array("# Pedido", "# Factura", "Fecha", "Nombre", "Cedula/RIF", "Subtotal", "IVA", "Total");

            $data = json_decode($this->reporte_libro_de_ventas(array()), true);
            
            foreach ($data as $d)
                $csv[] = array(
                    $d['id'],
                    $d['nro_factura'],
                    $d['fecha_anadida'],
                    $d['cliente_nombre'],
                    $d['cliente_ni'],
                    "Bs. " . number_format(floatval($d['subtotal']), 2, ",", "."),
                    "Bs. " . number_format(floatval($d['iva']), 2, ",", "."),
                    "Bs. " . number_format(floatval($d['total']), 2, ",", ".")
                );

            return $csv;
        }

        public function csv_reporte_cuadre_ventas_diarias()
        {
            $csv = array();
            $csv[] = array("Usuario", "Monto Facturado", "Devoluciones", "Notas de Credito", "Retiros de Caja", "Diferencia en Caja", "Total Venta");

            $data = json_decode($this->reporte_cuadre_ventas_diarias(array()), true);
            
            foreach ($data as $d)
                $csv[] = array(
                    $d['nombre_completo'],
                    "Bs. " . number_format(floatval($d['total_facturado']), 2, ",", "."),                    
                    "Bs. " . number_format(floatval($d['devoluciones']), 2, ",", "."),
                    "Bs. " . number_format(floatval($d['nota_de_credito']), 2, ",", "."),
                    "Bs. " . number_format(floatval($d['retiro_de_caja']), 2, ",", "."),                    
                    "Bs. " . number_format(floatval($d['retiro_de_caja']) - floatval($d['total_facturado']), 2, ",", "."),
                    "Bs. " . number_format(floatval($d['retiro_de_caja']), 2, ",", ".")
                );

            return $csv;
        }

        public function csv_reporte_venta_productos()
        {
            $csv = array();
            $csv[] = array("Codigo", "Producto", "Cantidad Vendida", "SubTotal", "IVA", "Total");

            $data = json_decode($this->reporte_venta_productos(array()), true);

            $iva = $this->obtener_iva();
            
            foreach ($data as $d)
                $csv[] = array(
                    $d['codigo'],
                    $d['nombre'],
                    $d['cantidad'],
                    "Bs. " . number_format(floatval($d['total']) / ($d['exento_iva'] == '1' ? 1 : (1.00 + $iva)), 2, ",", "."),
                    "Bs. " . number_format(floatval($d['total']) - (floatval($d['total']) / ($d['exento_iva'] == '1' ? 1 : (1.00 + $iva))), 2, ",", "."),
                    "Bs. " . number_format(floatval($d['total']), 2, ",", ".")
                );

            return $csv;
        }
	}
?>