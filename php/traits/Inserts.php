<?php
	trait Inserts {
		public function agregar_guia($post)
        {
            $json = array();

            $query = $this->db->prepare("
                insert into Guia (titulo, seccion, comentario, profesor, materia, entregada_por, recibida_por, fecha_anadida, tipo)
                values (:titulo, :seccion, :comentario, :profesor, :materia, (select id from Personal where usuario=:recibida_por), (select id from Personal where usuario=:recibida_por), now(), :tipo);
            ");

            $query->execute(array(
                ":titulo" => $post['titulo'],
                ":seccion" => $post['seccion'],
                ":comentario" => isset($post['comentario']) ? $post['comentario'] : null,
                ":profesor" => $post['profesor'],
                ":materia" => $post['materia'],
                ":entregada_por" => $post['recibida_por'],
                ":recibida_por" => $post['recibida_por'],
                ":tipo" => isset($post['tipo']) ? $post['tipo'] : null
            ));

            $json['status'] = "ok";
            $json['id_guia'] = $this->db->lastInsertId();

            $tokens = $this->generar_tokens_guia($json['id_guia']);

            // Le asigno el ID al codigo
            $query = $this->db->prepare("
                update Guia set 
                    codigo=:id,
                    tokens=:tokens
                where id=:id
            ");

            $query->execute(array(
                ":id" => $json['id_guia'],
                ":tokens" => $tokens
            ));

            return json_encode($json);
        }

        public function agregar_profesor($post)
        {
            try 
            {
                $query = $this->db->prepare("call agregar_profesor(:nombre, :snombre, :apellido, :sapellido, :cedula, :tlfs, :email)");

                $query->execute(array(
                    ":nombre" => $post['nombre'],
                    ":apellido" => $post['apellido'],
                    ":snombre" => isset($post['snombre']) ? $post['snombre'] : null,
                    ":sapellido" => isset($post['sapellido']) ? $post['sapellido'] : null,
                    ":cedula" => isset($post['cedula']) ? $post['cedula'] : null,
                    ":tlfs" => isset($post['tlfs']) ? $post['tlfs'] : null,
                    ":email" => isset($post['email']) ? $post['email'] : null
                ));

                return "ok";
            }
            catch (Exception $e)
            {
                return "error";
            }
        }

        public function agregar_plan_de_estudio($post)
        {
            $mencion_campo = "";
            $mencion_valor = "";

            if (isset($post['mencion']))
                if ($post['mencion'] != -1)
                {
                    $mencion_campo = ", mencion";
                    $mencion_valor = ", '".$post['mencion']."'";
                }

            $query = $this->db->prepare("
                insert into Plan_de_Estudio (titulo, carrera, materia, tipo, comentario, pdf, paginas, hojas, para, fecha".$mencion_campo.")
                values (:titulo, :carrera, :materia, :tipo, :comentario, :pdf, :paginas, :hojas, :para, now()".$mencion_valor.")
            ");

            $query->execute(array(
                ":titulo" => $post['titulo'],
                ":carrera" => $post['carrera'],
                ":tipo" => $post['tipo'],
                ":comentario" => $post['comentario'],
                ":pdf" => $post['pdf'],
                ":paginas" => $post['paginas'],
                ":hojas" => $post['hojas'],
                ":para" => $post['para'],
                ":materia" => isset($post['materia']) ? $post['materia'] : null
            ));

            return "ok";
        }

        public function agregar_dependencia($post)
        {
            $query = $this->db->prepare("insert into Dependencia (nombre) values (:nombre)");

            $query->execute(array(
                ":nombre" => $post['nombre']
            ));

            return "ok";
        }

        public function agregar_material_danado($post)
        {
            $query = $this->db->prepare("insert into Stock_Personal_Danado (stock, cantidad, motivo, fecha) values (:stock, :cantidad, :motivo, now())");

            $query->execute(array(
                ":stock" => $post['stock'],
                ":cantidad" => $post['cantidad'],
                ":motivo" => $post['motivo']
            ));

            // Actualizo el restante
            $query = $this->db->prepare("
                update Stock_Personal set
                    restante=:restante
                where id=:stock
            ");

            $query->execute(array(
                ":stock" => $post['stock'],
                ":restante" => intval($post['restante']) - intval($post['cantidad'])
            ));

            return "ok";
        }

        public function agregar_proveedor($post)
        {
            $query = $this->db->prepare("
                insert into Proveedor (nombre, ni, direccion) 
                values (:nombre, :ni, :direccion)
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":ni" => $post['ni'],
                ":direccion" => $post['direccion']
            ));

            return "ok";
        }

        public function agregar_familia($post)
        {
            $query = $this->db->prepare("insert into Producto_Familia (nombre) values (:nombre)");

            $query->execute(array(
                ":nombre" => $post['nombre']
            ));

            return "ok";
        }

        public function agregar_material($post)
        {
            $query = $this->db->prepare("
                insert into Material (nombre) values (:nombre)
            ");

            $query->execute(array(
                ":nombre" => $post['nombre']
            ));

            return "ok";
        }

        public function agregar_stock($post)
        {
            $query = $this->db->prepare("
                insert into Stock (cantidad, fecha_anadido, costo, material, proveedor, cantidad_disponible) 
                values (:cantidad, now(), :costo, :material, :proveedor, :cantidad)
            ");

            $query->execute(array(
                ":cantidad" => $post['cantidad'],
                ":costo" => $post['costo'],
                ":material" => $post['material'],
                ":proveedor" => $post['proveedor']
            ));

            return "ok";
        }

        public function agregar_carrera($post)
        {
            try 
            {
                $query = $this->db->prepare("call agregar_carrera(:nombre, :tipo)");

                $query->execute(array(
                    ":nombre" => $post['nombre'],
                    ":tipo" => $post['tipo']
                ));

                return "ok";
            }
            catch (Exception $e)
            {
                return "error";
            }
        }

        public function agregar_departamento_ucab($post)
        {
            $query = $this->db->prepare("
                insert into Departamento_UCAB (nombre)
                values (:nombre)
            ");

            $query->execute(array(
                ":nombre" => $post['nombre']
            ));

            return "ok";
        }

        public function agregar_orden($post)
        {
            @session_start();

            $query = $this->db->prepare("
                insert into Orden (numero, dependencia, observaciones, creado_por, fecha_anadida, fecha_modificada, fecha)
                values (:numero, :dependencia, :observaciones, (select id from Personal where usuario=:usuario), now(), now(), :fecha)
            ");

            $query->execute(array(
                ":numero" => $post['numero'],
                ":dependencia" => $post['dependencia'],
                ":fecha" => isset($post['fecha_']) ? $post['fecha_'] : null,
                ":observaciones" => isset($post['observaciones']) ? $post['observaciones'] : null,
                ":usuario" => $_SESSION['login_username']
            ));

            $oid = $this->db->lastInsertId();

            /* Añado los productos */
            if (isset($post['productos']))
                foreach ($post['productos'] as $p)
                {
                    $precio_unitario = "(select costo from Producto_Costo where producto=:producto and eliminado=0 order by fecha desc limit 1)";
                    $precio_total = "(select costo from Producto_Costo where producto=:producto and eliminado=0 order by fecha desc limit 1) * :cantidad";

                    if (isset($p['costo_unitario_facturado']))
                    {
                        $precio_unitario = ":costo_unitario_facturado";
                        $precio_total = ":costo_unitario_facturado * :cantidad";
                    }

                    $query = $this->db->prepare("
                        insert into Orden_Producto (orden, producto, cantidad, nro_copias, nro_originales, precio_unitario, precio_total, fecha_anadido)
                        values (
                            :orden,
                            :producto,
                            :cantidad,
                            :nro_copias,
                            :nro_originales,
                            ".$precio_unitario.",
                            ".$precio_total.",
                            now()
                        )
                    ");

                    $query->execute(array(
                        ":orden" => $oid,
                        ":producto" => $p['producto'],
                        ":cantidad" => intval($p['nro_copias']) * intval($p['nro_originales']),
                        ":nro_copias" => intval($p['nro_copias']),
                        ":nro_originales" => intval($p['nro_originales']),
                        ":costo_unitario_facturado" => isset($p['costo_unitario_facturado']) ? floatval($p['costo_unitario_facturado']) : 0
                    ));
                }

            return "ok";
        }

        public function agregar_materia($post)
        {
            $query = $this->db->prepare("
                insert into Materia (nombre, tipo, dictada_en) 
                values (:nombre, :tipo, :periodo)
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":periodo" => $post['periodo'],
                ":tipo" => $post['tipo']
            ));

            return "ok";
        }

        public function agregar_cuentaabierta($post)
        {
            $query = $this->db->prepare("
                insert into CuentaAbierta (nombre, inicia, vence)
                values (:nombre, :inicia, :vence)
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":inicia" => isset($post['inicia_']) ? $post['inicia_'] : null,
                ":vence" => isset($post['vence_']) ? $post['vence_'] : null
            ));

            $cid = $this->db->lastInsertId();

            /* Añado las personas autorizadas */
            if (isset($post['personas']))
                foreach ($post['personas'] as $p)
                {
                    $query = $this->db->prepare("
                        insert into Persona_Autorizada (cuentaabierta, nombre_completo, cedula)
                        values (:cuentaabierta, :nombre, :cedula)
                    ");

                    $query->execute(array(
                        ":cuentaabierta" => $cid,
                        ":nombre" => $p['nombre'],
                        ":cedula" => $p['cedula']
                    ));
                }

            /* Añado los productos */
            if (isset($post['productos']))
                foreach ($post['productos'] as $p)
                {
                    $precio_unitario = "(select costo from Producto_Costo where producto=:producto and eliminado=0 order by fecha desc limit 1)";
                    $precio_total = "(select costo from Producto_Costo where producto=:producto and eliminado=0 order by fecha desc limit 1) * :cantidad";

                    if (isset($p['costo_unitario_facturado']))
                    {
                        $precio_unitario = ":costo_unitario_facturado";
                        $precio_total = ":costo_unitario_facturado * :cantidad";
                    }

                    $query = $this->db->prepare("
                        insert into CuentaAbierta_Producto (cuentaabierta, producto, cantidad, nro_copias, nro_originales, precio_unitario, precio_total, fecha_anadido)
                        values (
                            :cuentaabierta,
                            :producto,
                            :cantidad,
                            :nro_copias,
                            :nro_originales,
                            ".$precio_unitario.",
                            ".$precio_total.",
                            now()
                        )
                    ");

                    $query->execute(array(
                        ":cuentaabierta" => $cid,
                        ":producto" => $p['producto'],
                        ":cantidad" => intval($p['nro_copias']) * intval($p['nro_originales']),
                        ":nro_copias" => intval($p['nro_copias']),
                        ":nro_originales" => intval($p['nro_originales']),
                        ":costo_unitario_facturado" => isset($p['costo_unitario_facturado']) ? floatval($p['costo_unitario_facturado']) : 0
                    ));
                }

            return "ok";
        }

        public function agregar_producto($post)
        {
            if (!isset($post['tokens']))
                $post['tokens'] = $post['nombre'];

            foreach ($post['departamentos'] as $departamento)
            {
                $query = $this->db->prepare("
                    insert into Producto (nombre, descripcion, departamento, familia, fecha_creado, exento_iva, tokens, estado)
                    values (:nombre, :descripcion, :departamento, :familia, now(), :exento_iva, :tokens, :estado)
                ");

                $query->execute(array(
                    ":nombre" => $post['nombre'],
                    ":descripcion" => isset($post['descripcion']) ? $post['descripcion'] : "",
                    ":familia" => $post['familia'],
                    ":departamento" => $departamento,
                    ":tokens" => $post['tokens'],
                    ":exento_iva" => $post['exento_iva'] ? $post['exento_iva'] : 0,
                    ":estado" => $post['estado'] ? $post['estado'] : 1
                ));

                $pid = $this->db->lastInsertId();

                /* Agrego el costo */
                $query = $this->db->prepare("
                    insert into Producto_Costo (producto, costo, fecha)
                    values (:pid, :costo, now())
                ");

                $query->execute(array(
                    ":pid" => $pid,
                    ":costo" => $post['costo']
                ));

                /* Agrego los materiales */
                if (isset($post['materiales']))
                    foreach ($post['materiales'] as $m)
                    {
                        $query = $this->db->prepare("
                            insert into Producto_Material (producto, material, cantidad, creado_por, fecha_creado)
                            values (:producto, :material, :cantidad, :creado_por, now())
                        ");

                        $query->execute(array(
                            ":producto" => $pid,
                            ":material" => $m['material'],
                            ":cantidad" => $m['cantidad'],
                            ":creado_por" => isset($_SESSION['login_username']) ? $_SESSION['login_username'] : ''
                        ));
                    }

                /* Agrego los guias */
                if (isset($post['guias']))
                    foreach ($post['guias'] as $m)
                    {
                        $query = $this->db->prepare("
                            insert into Producto_Guia (producto, guia, creado_por, fecha_creado)
                            values (:producto, :guia, :creado_por, now())
                        ");

                        $query->execute(array(
                            ":producto" => $pid,
                            ":guia" => isset($m['guia']) ? $m['guia'] : $m['codigo'],
                            ":creado_por" => isset($_SESSION['login_username']) ? $_SESSION['login_username'] : ''
                        ));
                    }
            }

            return "ok";
        }

        public function agregar_mencion($post)
        {
            $query = $this->db->prepare("
                insert into Mencion (nombre, carrera) 
                values (:nombre, :carrera)
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":carrera" => $post['carrera']
            ));

            return "ok";
        }

        public function agregar_personal($post)
        {
            $query = $this->db->prepare("
                insert into Personal (nombre, segundo_nombre, apellido, segundo_apellido, cedula, telefono, email, usuario, contrasena, fecha_creado)
                values (:nombre, :snombre, :apellido, :sapellido, :cedula, :telefono, :email, :usuario, :contrasena, now())
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":apellido" => $post['apellido'],
                ":snombre" => $post['snombre'],
                ":sapellido" => $post['sapellido'],
                ":cedula" => $post['cedula'],
                ":telefono" => $post['telefono'],
                ":email" => $post['email'],
                ":usuario" => $post['usuario'],
                ":contrasena" => $post['contrasena']
            ));

            $uid = $this->db->lastInsertId();

            // Lo asigno a los departamentos
            if (isset($post['departamentos']))
                foreach ($post['departamentos'] as $p)
                {
                    $query = $this->db->prepare("
                        insert into Personal_Departamento (departamento, personal)
                        values (:departamento, :personal)
                    ");

                    $query->execute(array(
                        ":departamento" => $p,
                        ":personal" => $uid
                    ));
                }

            // Añado los permisos
            if (isset($post['permisos']))
            {
                $permisos = explode("]", $post['permisos']);

                foreach ($permisos as $p_)
                {
                    $p = str_replace("[", "", $p_);

                    if (strlen($p) == 0) continue;

                    $query = $this->db->prepare("
                        insert into Permiso_Asignado (permiso, usuario)
                        values (:pid, :uid)
                    ");

                    $query->execute(array(
                        ":pid" => $p,
                        ":uid" => $uid
                    ));
                }
            }

            return "ok";
        }

        public function agregar_pedido($post)
        {
            @session_start();

            $query = $this->db->prepare("
                insert into Pedido (cliente, cond_pago, observaciones, creado_por, fecha_anadida, fecha_modificada, departamento)
                values (:cliente, :cond_pago, :observaciones, (select id from Personal where usuario=:usuario), now(), now(), (select id from Departamento where nombre=:departamento))
            ");

            $query->execute(array(
                ":cliente" => $post['cliente'],
                ":cond_pago" => isset($post['cond_pago']) ? $post['cond_pago'] : null,
                ":observaciones" => isset($post['observaciones']) ? $post['observaciones'] : null,
                ":usuario" => $post['username'],
                ":departamento" => isset($post['departamento']) ? $post['departamento'] : null
            ));

            $oid = $this->db->lastInsertId();

            /* Añado los productos */
            if (isset($post['productos']))
                foreach ($post['productos'] as $p)
                {
                    $precio_unitario = "(select costo from Producto_Costo where producto=:producto and eliminado=0 order by fecha desc limit 1)";
                    $precio_total = "(select costo from Producto_Costo where producto=:producto and eliminado=0 order by fecha desc limit 1) * :cantidad";

                    if (isset($p['costo_unitario_facturado']))
                    {
                        $precio_unitario = ":costo_unitario_facturado";
                        $precio_total = ":costo_unitario_facturado * :cantidad";
                    }

                    $query = $this->db->prepare("
                        insert into Pedido_Producto (pedido, producto, cantidad, nro_copias, nro_originales, precio_unitario, precio_total, fecha_anadido, espiral, tapa)
                        values (
                            :pedido,
                            :producto,
                            :cantidad,
                            :nro_copias,
                            :nro_originales,
                            ".$precio_unitario.",
                            ".$precio_total.",
                            now(),
                            :espiral,
                            :tapa
                        )
                    ");

                    $query->execute(array(
                        ":pedido" => $oid,
                        ":producto" => isset($p['idproducto']) ? $p['idproducto'] : $p['producto'],
                        ":cantidad" => intval($p['nro_copias']) * intval($p['nro_originales']),
                        ":nro_copias" => intval($p['nro_copias']),
                        ":nro_originales" => intval($p['nro_originales']),
                        ":costo_unitario_facturado" => isset($p['costo_unitario_facturado']) ? floatval($p['costo_unitario_facturado']) : 0,
                        ":espiral" => isset($p['espiral']) ? $p['espiral'] : null,
                        ":tapa" => isset($p['tapa']) ? $p['tapa'] : null
                    ));

                    /* Pongo los materiales necesarios en hold */
                    $query = $this->db->prepare("
                        select pm.material as material, pm.cantidad as cantidad
                        from Producto_Material as pm
                        where pm.producto=:pid
                    ");

                    $query->execute(array(
                        ":pid" => isset($p['idproducto']) ? $p['idproducto'] : $p['producto']
                    ));

                    $materiales = $query->fetchAll();

                    foreach ($materiales as $m)
                    {
                        $query = $this->db->prepare("
                            insert into Stock_Temp (pedido, material, cantidad)
                            values (:pedido, :material, :cantidad)
                        ");

                        $query->execute(array(
                            ":pedido" => $oid,
                            ":cantidad" => intval($p['nro_copias']) * intval($p['nro_originales']),
                            ":material" => $m['material']
                        ));
                    }
                }

            return "ok";
        }

        public function agregar_cliente($post)
        {
            $query = $this->db->prepare("
                insert into Cliente (nombre, ni, email, tlf, direccion)
                values (:nombre, :ni, :email, :tlf, :direccion)
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":ni" => $post['ni'],
                ":email" => $post['email'],
                ":tlf" => $post['tlf'],
                ":direccion" => $post['direccion']
            ));

            return "ok";
        }

        public function asignar_material($post)
        {
            $query = $this->db->prepare("
                insert into Stock_Personal (cantidad, personal, material, fecha, asignado_por, restante) values (:cantidad, :personal, :material, now(), :asignado_por, :cantidad)
            ");

            $query->execute(array(
                ":cantidad" => $post['cantidad'],
                ":personal" => $post['personal'],
                ":material" => $post['material'],
                ":asignado_por" => $_SESSION['login_username']
            ));

            return "ok";
        }

        public function registrar_vista_guia($username, $archivo, $resultado, $errores)
        {
            try 
            {
                $query = $this->db->prepare("
                    insert into Log_Vista_Guias (fecha, username, resultado, errores, archivo)
                    values (now(), :username, :resultado, :errores, :archivo)
                ");

                $query->execute(array(
                    ":username" => $username,
                    ":resultado" => $resultado,
                    ":errores" => $errores,
                    ":archivo" => $archivo
                ));

                return "ok";
            }
            catch (Exception $e)
            {
                return "error";
            }
        }

        public function procesar_pago($post)
        {
            /* Paso del stock_temp al stock_salida */
            /* Obtengo el stock_temp asociado a este pedido */
            $query = $this->db->prepare("
                select *
                from Stock_Temp as st
                where st.pedido=:pedido
            ");

            $query->execute(array(
                ":pedido" => $post['pedido']
            ));

            $stock_temp = $query->fetchAll();

            foreach ($stock_temp as $st)
            {
                /* Obtengo todos los stocks y voy reduciendo de cada uno hasta completar la cantidad */
                $cantidad_restante = intval($st['cantidad']);

                while ($cantidad_restante > 0)
                {
                    /* Voy reduciendo del stock aplicando FIFO */
                    $query = $this->db->prepare("
                        select (s.cantidad - (select (case when sum(cantidad) is not null then sum(cantidad) else 0 end) from Stock_Salida where stock=s.id)) as cantidad, s.id as id
                        from Stock as s
                        where s.material=:material and s.eliminado=0 and (s.cantidad - (select (case when sum(cantidad) is not null then sum(cantidad) else 0 end) from Stock_Salida where stock=s.id))>0
                        order by s.fecha_anadido asc
                        limit 1
                    ");

                    $query->execute(array(
                        ":material" => $st['material']
                    ));

                    $stock = $query->fetchAll();
                    $stock = $stock[0];

                    /* Calculo lo que voy a restar de este stock */
                    $cantidad_restar = $cantidad_restante > intval($stock['cantidad']) ? intval($stock['cantidad']) : $cantidad_restante;

                    /* Añado el registro de stock saliente con esa cantidad */
                    $query = $this->db->prepare("
                        insert into Stock_Salida (stock, pedido, cantidad, fecha)
                        values (:stock, :pedido, :cantidad, now())
                    ");

                    $query->execute(array(
                        ":stock" => $stock['id'],
                        ":pedido" => $post['pedido'],
                        ":cantidad" => $cantidad_restar
                    ));

                    /* Actualizo la cantidad_disponible para no cambiar todos los query */
                    $query = $this->db->prepare("
                        update Stock set cantidad_disponible=(cantidad_disponible - :cantidad) where id=:stock
                    ");

                    $query->execute(array(
                        ":stock" => $stock['id'],
                        ":cantidad" => $cantidad_restar
                    ));

                    /* Por ultimo resto la cantidad que deduje del stock para seguir el ciclo */
                    $cantidad_restante -= $cantidad_restar;
                }

                /* Lo descuento del material asignado */
                $cantidad_restante = intval($st['cantidad']);

                $query = $this->db->prepare("
                    select *
                    from Stock_Personal as sp
                    where personal=(select id from Personal where usuario=:usuario) and sp.restante>0 and sp.eliminado=0
                    order by sp.fecha asc
                ");

                $query->execute(array(
                    ":usuario" => $post['usuario']
                ));

                $stock_personal = $query->fetchAll();

                foreach ($stock_personal as $stock_asignado)
                {
                    if ($cantidad_restante <= 0) break;

                    $cantidad_restar = intval($stock_asignado['restante']) - $cantidad_restante >= 0 ? $cantidad_restante : intval($stock_asignado['restante']);

                    $query = $this->db->prepare("
                        update Stock_Personal 
                        set 
                            restante=:restante
                        where personal=(select id from Personal where usuario=:usuario)
                    ");

                    $query->execute(array(
                        ":usuario" => $post['usuario'],
                        ":restante" => $cantidad_restar
                    ));

                    $cantidad_restante -= $cantidad_restar;
                }
            }

            /* Registro el pago */
            $query = $this->db->prepare("
                insert into Pago_Pedido (pedido, creado_por, fecha_creado, monto, cambio, subtotal, iva, total, metodo_pago)
                values (:pedido, (select id from Personal where usuario=:usuario), now(), :monto, :cambio, :subtotal, :iva, :total, :metodo_pago)
            ");

            $query->execute(array(
                ":pedido" => $post['pedido'],
                ":usuario" => $post['usuario'],
                ":monto" => $post['monto'],
                ":cambio" => floatval($post['monto']) - floatval($post['total']),
                ":subtotal" => $post['subtotal'],
                ":iva" => $post['iva'],
                ":total" => $post['total'],
                ":metodo_pago" => $post['metodo_pago']
            ));

            // Asigno el nro de factura igual al id
            $query = $this->db->prepare("
                update Pago_Pedido set nro_factura=:id where id=:id
            ");

            $nro_factura = $this->db->lastInsertId();

            $query->execute(array(
                ":id" => $nro_factura
            ));

            // Apruebo el pedido
            $query = $this->db->prepare("
                update Pedido set procesada=1 where id=:id
            ");

            $query->execute(array(
                ":id" => $post['pedido']
            ));

            $json = array();
            $json['status'] = "ok";
            $json['factura'] = $nro_factura;

            return json_encode($json);
        }

        public function agregar_retiro_caja($post)
        {
            $json = array();

            // Chequeo el usuario y la clave
            $query = $this->db->prepare("
                select * from Personal where usuario=:administrador and contrasena=:contrasena
            ");

            $query->execute(array(
                ":administrador" => $post['administrador'],
                ":contrasena" => $post['contrasena']
            ));

            if ($query->rowCount() > 0)
            {
                $query = $this->db->prepare("
                    insert into Retiro_Caja (creado_por, personal, fecha, monto, concepto) 
                    values ((select id from Personal where usuario=:administrador),
                            :cajero, now(), :monto, :concepto)
                ");

                $query->execute(array(
                    ":administrador" => $post['administrador'],
                    ":cajero" => $post['cajero'],
                    ":monto" => floatval($post['monto']),
                    ":concepto" => $post['concepto']
                ));

                $json["msg"] = "Retiro de Caja añadido con éxito";
                $json["success"] = 1;
            }
            else
            {
                $json["msg"] = "Los datos del administrador son inválidos";
                $json["error"] = 1;
            }

            return json_encode($json);
        }

        public function agregar_nota_credito($post)
        {
            $json = array();

            $iva = $this->obtener_iva();

            $query = $this->db->prepare("
                insert into Nota_Credito (creado_por, nro_factura, nro_control, subtotal, iva, total)
                values ((select id from Personal where usuario=:creado_por),
                        :nro_factura,
                        :nro_control,
                        :subtotal,
                        :iva,
                        :total)
            ");

            $total = floatval($post['total']) < 0.00 ? (-1)*floatval($post['total']) : floatval($post['total']);

            $query->execute(array(
                ":creado_por" => $post['creado_por'],
                ":nro_factura" => $post['nro_factura'],
                ":nro_control" => $post['nro_control'],
                ":subtotal" => $total / (1.00 + $iva),
                ":iva" => $total - ($total / (1.00 + $iva)),
                ":total" => $total
            ));

            $json["msg"] = "Nota de Crédito añadida con éxito";
            $json["success"] = 1;

            return json_encode($json);
        }
	}
?>