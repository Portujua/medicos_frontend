<?php
	trait Updates {
		abstract public function generar_tokens_guia($post);

		public function cambiar_estado_personal($post)
        {
            $query = $this->db->prepare("
                update Personal set estado=:estado where id=:pid
            ");

            $query->execute(array(
                ":pid" => $post['pid'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_departamento_ucab($post)
        {
            $query = $this->db->prepare("
                update Departamento_UCAB set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_proveedor($post)
        {
            $query = $this->db->prepare("
                update Proveedor set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_mencion($post)
        {
            $query = $this->db->prepare("
                update Mencion set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_producto($post)
        {
            $query = $this->db->prepare("
                update Producto set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_dependencia($post)
        {
            $query = $this->db->prepare("
                update Dependencia set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_familia($post)
        {
            $query = $this->db->prepare("
                update Producto_Familia set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_material($post)
        {
            $query = $this->db->prepare("
                update Material set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_cuentaabierta($post)
        {
            $query = $this->db->prepare("
                update CuentaAbierta set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_orden($post)
        {
            $query = $this->db->prepare("
                update Orden set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_profesor($post)
        {
            $query = $this->db->prepare("
                update Profesor set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_materia($post)
        {
            $query = $this->db->prepare("
                update Materia set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_carrera($post)
        {
            $query = $this->db->prepare("
                update Carrera set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function editar_orden($post)
        {
            $query = $this->db->prepare("
                update Orden set 
                    numero=:numero,
                    dependencia=:dependencia,
                    observaciones=:observaciones,
                    fecha_modificada=now(),
                    fecha=:fecha
                where id=:id
            ");

            $query->execute(array(
                ":numero" => $post['numero'],
                ":dependencia" => $post['dependencia'],
                ":observaciones" => isset($post['observaciones']) ? $post['observaciones'] : null,
                ":fecha" => isset($post['fecha_']) ? $post['fecha_'] : null,
                ":id" => $post['id']
            ));

            /* Elimino los productos */
            if (isset($post['productos']))
            {
                // Veo los IDS actuales
                $ids_ahora = "";

                foreach ($post['productos'] as $p)
                    if (isset($p['opid']))
                        $ids_ahora .= (strlen($ids_ahora) > 0 ? ',' : '') . $p['opid'];

                if (strlen($ids_ahora) > 0)
                {
                    $query = $this->db->prepare("
                        delete from Orden_Producto where orden=:orden and id not in (".$ids_ahora.")
                    ");

                    $query->execute(array(
                        ":orden" => $post['id']
                    ));
                }
            }

            /* Añado los productos */
            if (isset($post['productos']))
                foreach ($post['productos'] as $p)
                {
                    /* Veo si debo agregarlo porque es nuevo o editarlo */
                    if (!isset($p['costo_unitario_facturado'])) // Es nuevo
                    {
                        $query = $this->db->prepare("
                            insert into Orden_Producto (orden, producto, cantidad, nro_copias, nro_originales, precio_unitario, precio_total, fecha_anadido)
                            values (
                                :orden,
                                :producto,
                                :cantidad,
                                :nro_copias,
                                :nro_originales,
                                (select costo from Producto_Costo where producto=:producto and eliminado=0 order by fecha desc limit 1),
                                (select costo from Producto_Costo where producto=:producto and eliminado=0 order by fecha desc limit 1) * :cantidad,
                                now()
                            )
                        ");

                        $query->execute(array(
                            ":orden" => $post['id'],
                            ":producto" => $p['producto'],
                            ":cantidad" => intval($p['nro_copias']) * intval($p['nro_originales']),
                            ":nro_copias" => intval($p['nro_copias']),
                            ":nro_originales" => intval($p['nro_originales'])
                        ));
                    }
                    else // Es viejo
                    {
                        $query = $this->db->prepare("
                            update Orden_Producto set
                                producto=:producto, 
                                cantidad=:cantidad, 
                                nro_copias=:nro_copias, 
                                nro_originales=:nro_originales,
                                precio_total=:precio_total,
                                precio_unitario=:precio_unitario
                            where
                                id=:opid
                        ");

                        $query->execute(array(
                            ":opid" => $p['opid'],
                            ":producto" => $p['producto'],
                            ":cantidad" => intval($p['nro_copias']) * intval($p['nro_originales']),
                            ":nro_copias" => intval($p['nro_copias']),
                            ":nro_originales" => intval($p['nro_originales']),
                            ":precio_total" => floatval(floatval($p['costo_unitario_facturado']) * floatval(intval($p['nro_copias']) * intval($p['nro_originales']))),
                            ":precio_unitario" => floatval($p['costo_unitario_facturado'])
                        ));
                    }
                }

            return "ok";
        }

        public function editar_mencion($post)
        {
            $query = $this->db->prepare("
                update Mencion set 
                    nombre=:nombre, 
                    carrera=:carrera
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":carrera" => $post['cid'],
                ":id" => $post['id']
            ));

            return "ok";
        }

        public function editar_producto($post)
        {
            if (!isset($post['tokens']))
                $post['tokens'] = $post['nombre'];

            $query = $this->db->prepare("
                update Producto set 
                    nombre=:nombre, 
                    descripcion=:descripcion,
                    familia=:familia,
                    departamento=:departamento,
                    exento_iva=:exento_iva,
                    tokens=:tokens
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":id" => $post['id'],
                ":descripcion" => $post['descripcion'],
                ":departamento" => $post['departamento'],
                ":familia" => $post['familia'],
                ":tokens" => $post['tokens'],
                ":exento_iva" => $post['exento_iva'] ? $post['exento_iva'] : 0
            ));

            // Veo si tiene nuevo precio
            if (isset($post['costo_nuevo']))
            {
                $query = $this->db->prepare("
                    select id from Producto where nombre=:nombre_viejo or nombre=:nombre
                ");

                $query->execute(array(
                    ":nombre_viejo" => isset($post['nombre_viejo']) ? $post['nombre_viejo'] : $post['nombre'],
                    ":nombre" => $post['nombre']
                ));

                $ps = $query->fetchAll();

                foreach ($ps as $pss)
                {
                    $query = $this->db->prepare("
                        insert into Producto_Costo (producto, costo, fecha)
                        values (:pid, :costo, now())
                    ");

                    $query->execute(array(
                        ":pid" => $pss['id'],
                        ":costo" => $post['costo_nuevo']
                    ));
                }
            }

            /* Elimino los materiales */
            $query = $this->db->prepare("
                delete from Producto_Material where producto=:pid
            ");

            $query->execute(array(
                ":pid" => $post['id']
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
                        ":producto" => $post['id'],
                        ":material" => $m['material'],
                        ":cantidad" => $m['cantidad'],
                        ":creado_por" => isset($_SESSION['login_username']) ? $_SESSION['login_username'] : ''
                    ));
                }

            /* Elimino las guias */
            $query = $this->db->prepare("
                delete from Producto_Guia where producto=:pid
            ");

            $query->execute(array(
                ":pid" => $post['id']
            ));

            /* Agrego las guias */
            if (isset($post['guias']))
                foreach ($post['guias'] as $m)
                {
                    $query = $this->db->prepare("
                        insert into Producto_Guia (producto, guia, creado_por, fecha_creado)
                        values (:producto, :guia, :creado_por, now())
                    ");

                    $query->execute(array(
                        ":producto" => $post['id'],
                        ":guia" => $m['guia'],
                        ":creado_por" => isset($_SESSION['login_username']) ? $_SESSION['login_username'] : ''
                    ));
                }

            /* Veo si es una HOJA para actualizar todas las guias */
            if (strpos($post['nombre'], 'Hoja') !== false && isset($post['costo_nuevo']))
            {
                $query = $this->db->prepare("
                    select idproducto
                    from Guia
                    where idmaterial=:producto
                ");

                $query->execute(array(
                    ":producto" => $post['id']
                ));

                $guias = $query->fetchAll();

                foreach ($guias as $g)
                {
                    $query = $this->db->prepare("
                        insert into Producto_Costo (producto, costo, fecha)
                        values (:pid, 
                        (
                            select sum(pm.cantidad)
                            from Producto as p, Producto_Material as pm
                            where pm.producto=p.id and p.id=:pid
                        ) * ".floatval($post['costo_nuevo']).", 
                        now())
                    ");

                    $query->execute(array(
                        ":pid" => $g['idproducto']
                    ));
                }
            }

            return "ok";
        }

        public function editar_cuentaabierta($post)
        {
            $query = $this->db->prepare("
                update CuentaAbierta set 
                    nombre=:nombre, 
                    vence=:vence,
                    inicia=:inicia
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":vence" => isset($post['vence_']) ? $post['vence_'] : null,
                ":inicia" => isset($post['inicia_']) ? $post['inicia_'] : null,
                ":id" => $post['id']
            ));

            /* Elimino las personas */
            $query = $this->db->prepare("
                delete from Persona_Autorizada where cuentaabierta=:cuentaabierta
            ");

            $query->execute(array(
                ":cuentaabierta" => $post['id']
            ));

            if (isset($post['personas']))
                foreach ($post['personas'] as $p)
                {
                    $query = $this->db->prepare("
                        insert into Persona_Autorizada (cuentaabierta, nombre_completo, cedula)
                        values (:cuentaabierta, :nombre, :cedula)
                    ");

                    $query->execute(array(
                        ":cuentaabierta" => $post['id'],
                        ":nombre" => $p['nombre'],
                        ":cedula" => $p['cedula']
                    ));
                }

            /* Elimino los productos */
            if (isset($post['productos']))
            {
                // Veo los IDS actuales
                $ids_ahora = "";

                foreach ($post['productos'] as $p)
                    if (isset($p['capid']))
                        $ids_ahora .= (strlen($ids_ahora) > 0 ? ',' : '') . $p['capid'];

                if (strlen($ids_ahora) > 0)
                {
                    $query = $this->db->prepare("
                        delete from CuentaAbierta_Producto where cuentaabierta=:cuentaabierta and id not in (".$ids_ahora.")
                    ");

                    $query->execute(array(
                        ":cuentaabierta" => $post['id']
                    ));
                }
            }

            /* Añado los productos */
            if (isset($post['productos']))
                foreach ($post['productos'] as $p)
                {
                    /* Veo si debo agregarlo porque es nuevo o editarlo */
                    if (!isset($p['costo_unitario_facturado'])) // Es nuevo
                    {
                        $query = $this->db->prepare("
                            insert into CuentaAbierta_Producto (cuentaabierta, producto, cantidad, nro_copias, nro_originales, precio_unitario, precio_total, fecha_anadido)
                            values (
                                :cuentaabierta,
                                :producto,
                                :cantidad,
                                :nro_copias,
                                :nro_originales,
                                (select costo from Producto_Costo where producto=:producto and eliminado=0 order by fecha desc limit 1),
                                (select costo from Producto_Costo where producto=:producto and eliminado=0 order by fecha desc limit 1) * :cantidad,
                                now()
                            )
                        ");

                        $query->execute(array(
                            ":cuentaabierta" => $post['id'],
                            ":producto" => $p['producto'],
                            ":cantidad" => intval($p['nro_copias']) * intval($p['nro_originales']),
                            ":nro_copias" => intval($p['nro_copias']),
                            ":nro_originales" => intval($p['nro_originales'])
                        ));
                    }
                    else // Es viejo
                    {
                        $query = $this->db->prepare("
                            update CuentaAbierta_Producto set
                                producto=:producto, 
                                cantidad=:cantidad, 
                                nro_copias=:nro_copias, 
                                nro_originales=:nro_originales,
                                precio_total=:precio_total,
                                precio_unitario=:precio_unitario
                            where
                                id=:capid
                        ");

                        $query->execute(array(
                            ":capid" => $p['capid'],
                            ":producto" => $p['producto'],
                            ":cantidad" => intval($p['nro_copias']) * intval($p['nro_originales']),
                            ":nro_copias" => intval($p['nro_copias']),
                            ":nro_originales" => intval($p['nro_originales']),
                            ":precio_total" => floatval(floatval($p['costo_unitario_facturado']) * floatval(intval($p['nro_copias']) * intval($p['nro_originales']))),
                            ":precio_unitario" => floatval($p['costo_unitario_facturado'])
                        ));
                    }
                }

            return "ok";
        }

        public function editar_departamento_ucab($post)
        {
            $query = $this->db->prepare("
                update Departamento_UCAB set 
                    nombre=:nombre
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":id" => $post['id']
            ));

            return "ok";
        }

        public function editar_dependencia($post)
        {
            $query = $this->db->prepare("
                update Dependencia set 
                    nombre=:nombre
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":id" => $post['id']
            ));

            return "ok";
        }

        public function editar_proveedor($post)
        {
            $query = $this->db->prepare("
                update Proveedor set 
                    nombre=:nombre,
                    ni=:ni,
                    direccion=:direccion
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":ni" => $post['ni'],
                ":direccion" => $post['direccion'],
                ":id" => $post['id']
            ));

            return "ok";
        }

        public function editar_familia($post)
        {
            $query = $this->db->prepare("
                update Producto_Familia set 
                    nombre=:nombre
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":id" => $post['id']
            ));

            return "ok";
        }

        public function editar_material($post)
        {
            $query = $this->db->prepare("
                update Material set 
                    nombre=:nombre
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":id" => $post['id']
            ));

            return "ok";
        }

        public function editar_materia($post)
        {
            try 
            {
                $query = $this->db->prepare("
                    update Materia set 
                        nombre=:nombre,
                        tipo=:tipo, 
                        dictada_en=:periodo
                    where id=:id
                ");

                $query->execute(array(
                    ":nombre" => $post['nombre'],
                    ":periodo" => $post['periodo_id'],
                    ":tipo" => $post['tipo_id'],
                    ":id" => $post['id']
                ));

                return "ok";
            }
            catch (Exception $e)
            {
                return "error";
            }
        }

        public function editar_carrera($post)
        {
            $query = $this->db->prepare("
                update Carrera set nombre=:nombre where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":id" => $post['id']
            ));
        }

        public function editar_profesor($post)
        {
            $query = $this->db->prepare("
                update Profesor set 
                    nombre=:nombre,
                    segundo_nombre=:segundo_nombre,
                    apellido=:apellido,
                    segundo_apellido=:segundo_apellido,
                    email=:email,
                    cedula=:cedula,
                    telefono=:telefono
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":segundo_nombre" => $post['segundo_nombre'],
                ":apellido" => $post['apellido'],
                ":segundo_apellido" => $post['segundo_apellido'],
                ":email" => $post['email'],
                ":cedula" => $post['cedula'],
                ":telefono" => $post['telefono'],
                ":id" => $post['id']
            ));
        }

        public function editar_personal($post)
        {
            $query = $this->db->prepare("
                update Personal set 
                    nombre=:nombre,
                    segundo_nombre=:snombre,
                    apellido=:apellido,
                    segundo_apellido=:sapellido,
                    cedula=:cedula,
                    telefono=:telefono,
                    email=:email,
                    usuario=:usuario,
                    contrasena=:contrasena
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":apellido" => $post['apellido'],
                ":snombre" => isset($post['snombre']) ? $post['snombre'] : null,
                ":sapellido" => isset($post['sapellido']) ? $post['sapellido'] : null,
                ":cedula" => isset($post['cedula']) ? $post['cedula'] : null,
                ":telefono" => isset($post['telefono']) ? $post['telefono'] : null,
                ":email" => isset($post['email']) ? $post['email'] : null,
                ":usuario" => $post['usuario'],
                ":contrasena" => $post['contrasena'],
                ":id" => $post['id']
            ));

            // Borro los permisos
            $query = $this->db->prepare("
                delete from Permiso_Asignado where usuario=:uid
            ");

            $query->execute(array(
                ":uid" => $post['id']
            ));

            // Añado los permisos
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
                    ":uid" => $post['id']
                ));
            }

            // Borro los departamentos a los que esta asociado
            $query = $this->db->prepare("
                delete from Personal_Departamento where personal=:uid
            ");

            $query->execute(array(
                ":uid" => $post['id']
            ));

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
                        ":personal" => $post['id']
                    ));
                }

            return "ok";
        }

        public function cambiar_estado($post)
        {
            if ($post['status'] == 1)
            {
                $query = $this->db->prepare("
                    select pdf from Guia where codigo=:codigo
                ");

                $query->execute(array(
                    ":codigo" => $post['codigo']
                ));

                $guia = $query->fetchAll();

                if ($guia[0]['pdf'] == null)
                {
                    $json = array();
                    $json['response'] = "Error, debe cargar primero un archivo PDF antes de aprobar la guía.";
                    $json['code'] = -1;

                    return json_encode($json);
                }
            }

            // Cambiar estado de la guia
            $query = $this->db->prepare("
                call cambiar_estado_guia(:status, :codigo);
            ");

            $query->execute(array(
                ":status" => $post['status'],
                ":codigo" => $post['codigo']
            ));

            // Pongo el estado del producto
            $query = null;
            
            $query = $this->db->prepare("
                update Producto set 
                    estado=(select (case when status=1 then 1 else 0 end) from Guia where codigo=:codigo)
                where id=(select idproducto from Guia where codigo=:codigo)
            ");

            $query->execute(array(
                ":codigo" => $post['codigo']
            ));

            $json = array();
            $json['response'] = "Estado de guía cambiado con éxito.";
            $json['code'] = 1;

            return json_encode($json);
        }

        public function modificar_guia($post)
        {
            $query = $this->db->prepare("
                update Guia set 
                    titulo=:titulo, 
                    seccion=:seccion, 
                    comentario=:comentario, 
                    pdf=:pdf, 
                    profesor=:profesor, 
                    materia=:materia, 
                    entregada_por=:entregada_por, 
                    recibida_por=:recibida_por, 
                    numero_hojas=:nro_hojas, 
                    numero_paginas=:nro_paginas,
                    idmaterial=:idmaterial
                where codigo=:codigo;

            ");

            $query->execute(array(
                ":codigo" => $post['codigo'],
                ":titulo" => $post['titulo'],
                ":seccion" => $post['seccion'],
                ":comentario" => isset($post['comentario']) ? $post['comentario'] : null,
                ":pdf" => $post['pdf'],
                ":profesor" => $post['profesor'],
                ":materia" => $post['materia'],
                ":entregada_por" => $post['entregada_por'],
                ":recibida_por" => $post['recibida_por'],
                ":nro_hojas" => $post['hojas'],
                ":nro_paginas" => $post['paginas'],
                ":idmaterial" => isset($post['producto']) ? $post['producto']['id'] : null
            ));

            $tokens = $this->generar_tokens_guia($post['codigo']);

            // Actualizo los tokens
            $query = $this->db->prepare("
                update Guia set 
                    tokens=:tokens
                where codigo=:codigo
            ");

            $query->execute(array(
                ":codigo" => $post['codigo'],
                ":tokens" => $tokens
            ));

            if (isset($post['producto']))
            {
                $query = $this->db->prepare("
                    select costo from Producto_Costo where producto=:pid and eliminado=0 order by fecha desc limit 1
                ");

                $query->execute(array(
                    ":pid" => $post['producto']['id']
                ));

                $res = $query->fetchAll();
                $costo = floatval($res[0]['costo']) * floatval($post['producto']['cantidad']);

                $post_producto = array();
                $post_producto['nombre'] = "Guía \"".$post['titulo']."\" (Código: ".$post['codigo'].") [".intval($post['producto']['cantidad'])." páginas]";
                $post_producto['descripcion'] = "";
                $post_producto['costo'] = $costo;
                $post_producto['costo_nuevo'] = $costo;
                $post_producto['departamento'] = 1;
                $post_producto['departamentos'] = array(1);
                $post_producto['familia'] = 1;
                $post_producto['exento_iva'] = 0;
                $post_producto['tokens'] = $tokens;
                $post_producto['estado'] = 0;
                $post_producto['id'] = isset($post['idproducto']) ? $post['idproducto'] : null;
                $post_producto['materiales'] = array();

                /* Material */
                $qmat = $this->db->prepare("
                    select m.id as material, pm.cantidad as cantidad
                    from Producto_Material as pm, Material as m
                    where pm.material=m.id and pm.producto=:pid
                ");

                $qmat->execute(array(
                    ":pid" => $post['producto']['id']
                ));

                $mat = $qmat->fetchAll();
                $mat = $mat[0];

                $material = array();
                $material['material'] = $mat['material'];
                $material['cantidad'] = $post['producto']['cantidad'];

                $post_producto['materiales'][] = $material;

                $post_producto['guias'] = array();

                $guia = array();
                $guia['guia'] = $post['id'];

                $post_producto['guias'][] = $guia;

                if (strlen($post['idproducto']) == 0)
                {
                    $this->agregar_producto($post_producto);

                    $query = $this->db->prepare("
                        update Guia set 
                            idproducto=(select id from Producto order by id desc limit 1)
                        where codigo=:codigo;
                    ");

                    $query->execute(array(
                        ":codigo" => $post['codigo']
                    ));
                }
                else
                {
                    $this->editar_producto($post_producto);
                }
            }

            return "ok";
        }

        public function editar_pedido($post)
        {
            $query = $this->db->prepare("
                update Pedido set 
                    cliente=:cliente,
                    cond_pago=:cond_pago,
                    observaciones=:observaciones,
                    fecha_modificada=now()
                where id=:id
            ");

            $query->execute(array(
                ":cliente" => $post['cliente'],
                ":cond_pago" => $post['cond_pago'],
                ":observaciones" => isset($post['observaciones']) ? $post['observaciones'] : null,
                ":id" => $post['id']
            ));

            /* Elimino los productos */
            if (isset($post['productos']))
            {
                // Veo los IDS actuales
                $ids_ahora = "";

                foreach ($post['productos'] as $p)
                    if (isset($p['opid']))
                        $ids_ahora .= (strlen($ids_ahora) > 0 ? ',' : '') . $p['opid'];

                if (strlen($ids_ahora) > 0)
                {
                    $query = $this->db->prepare("
                        delete from Pedido_Producto where pedido=:pedido and id not in (".$ids_ahora.")
                    ");

                    $query->execute(array(
                        ":pedido" => $post['id']
                    ));
                }
            }

            /* Añado los productos */
            if (isset($post['productos']))
                foreach ($post['productos'] as $p)
                {
                    /* Veo si debo agregarlo porque es nuevo o editarlo */
                    if (!isset($p['costo_unitario_facturado'])) // Es nuevo
                    {
                        $query = $this->db->prepare("
                            insert into Pedido_Producto (pedido, producto, cantidad, nro_copias, nro_originales, precio_unitario, precio_total, fecha_anadido)
                            values (
                                :pedido,
                                :producto,
                                :cantidad,
                                :nro_copias,
                                :nro_originales,
                                (select costo from Producto_Costo where producto=:producto and eliminado=0 order by fecha desc limit 1),
                                (select costo from Producto_Costo where producto=:producto and eliminado=0 order by fecha desc limit 1) * :cantidad,
                                now()
                            )
                        ");

                        $query->execute(array(
                            ":pedido" => $post['id'],
                            ":producto" => $p['producto'],
                            ":cantidad" => intval($p['nro_copias']) * intval($p['nro_originales']),
                            ":nro_copias" => intval($p['nro_copias']),
                            ":nro_originales" => intval($p['nro_originales'])
                        ));
                    }
                    else // Es viejo
                    {
                        $query = $this->db->prepare("
                            update Pedido_Producto set
                                producto=:producto, 
                                cantidad=:cantidad, 
                                nro_copias=:nro_copias, 
                                nro_originales=:nro_originales,
                                precio_total=:precio_total,
                                precio_unitario=:precio_unitario
                            where
                                id=:opid
                        ");

                        $query->execute(array(
                            ":opid" => $p['opid'],
                            ":producto" => $p['producto'],
                            ":cantidad" => intval($p['nro_copias']) * intval($p['nro_originales']),
                            ":nro_copias" => intval($p['nro_copias']),
                            ":nro_originales" => intval($p['nro_originales']),
                            ":precio_total" => floatval(floatval($p['costo_unitario_facturado']) * floatval(intval($p['nro_copias']) * intval($p['nro_originales']))),
                            ":precio_unitario" => floatval($p['costo_unitario_facturado'])
                        ));
                    }

                    /* Pongo los materiales necesarios en hold */
                    $query = $this->db->prepare("
                        select pm.material as material, pm.cantidad as cantidad
                        from Producto_Material as pm
                        where pm.producto=:pid
                    ");

                    $query->execute(array(
                        ":pid" => $p['producto']
                    ));

                    $materiales = $query->fetchAll();

                    foreach ($materiales as $m)
                    {
                        $query = $this->db->prepare("
                            insert into Stock_Temp (pedido, material, cantidad)
                            values (:pedido, :material, :material)
                        ");

                        $query->execute(array(
                            ":pedido" => $post['id'],
                            ":cantidad" => intval($p['nro_copias']) * intval($p['nro_originales']),
                            ":material" => $m['material']
                        ));
                    }
                }

            return "ok";
        }

        public function cambiar_estado_pedido($post)
        {
            $query = $this->db->prepare("
                update Pedido set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function editar_cliente($post)
        {
            $query = $this->db->prepare("
                update Cliente set 
                    nombre=:nombre, 
                    ni=:ni, 
                    email=:email, 
                    tlf=:tlf, 
                    direccion=:direccion
                where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":nombre" => $post['nombre'],
                ":ni" => $post['ni'],
                ":email" => $post['email'],
                ":tlf" => $post['tlf'],
                ":direccion" => $post['direccion']
            ));

            return "ok";
        }

        public function cambiar_estado_cliente($post)
        {
            $query = $this->db->prepare("
                update Cliente set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }
	}
?>