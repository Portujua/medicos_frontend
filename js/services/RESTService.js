(function(){
	angular.module("medicos").factory('RESTService', function($http, $timeout, LoginService){
		return {
			getCarreras: function(s){
				$http.get("api/carreras").then(function(obj){
					s.carreras = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getCarrera: function(s, id){
				$http.get("api/carreras").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							s.carrera = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},




			getProveedores: function(s){
				$http.get("api/proveedores").then(function(obj){
					s.proveedores = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getProveedor: function(s, id){
				$http.get("api/proveedores").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							s.proveedor = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},




			getMaterias: function(s, cid){
				if (cid == null)
					$http.get("api/materias").then(function(obj){
						s.materias = obj.data;
						$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
					});
				else
					$http.get("api/materias/" + cid).then(function(obj){
						s.materias = obj.data;
						$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
					});
			},
			getMateria: function(s, id){
				$http.get("api/materias").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							s.materia = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},




			getProfesores: function(s){
				$http.get("api/profesores").then(function(obj){
					s.profesores = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getProfesor: function(s, id){
				$http.get("api/profesores").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							s.profesor = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},




			getPersonal: function(s){
				$http.get("api/personal").then(function(obj){
					s.personal = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},

			getCajeros: function(s){
				$http.get("api/cajeros").then(function(obj){
					s.cajeros = [];

					if (window.location.hash.indexOf("reportes") != -1)
						s.cajeros.push({
							id: -1,
							nombre_completo: "Todos"
						});

					for (var i = 0; i < obj.data.length; i++)
						s.cajeros.push(obj.data[i]);

					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},

			getCajerosActivos: function(s){
				$http.get("api/cajeros/activos").then(function(obj){
					s.cajeros = [];

					for (var i = 0; i < obj.data.length; i++)
						s.cajeros.push(obj.data[i]);

					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},

			getInventarioDanado: function(s){
				$http.get("api/inventario/danado").then(function(obj){
					s.material_danado = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},




			getMenciones: function(s){
				$http.get("api/menciones").then(function(obj){
					s.menciones = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getMencion: function(s, id){
				$http.get("api/menciones").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							s.mencion = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},





			getGuias: function(s, status){
				$http.get("api/guias/" + status).then(function(obj){
					s.guias = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getGuiasWeb: function(s, status){
				$http.get("api/guias/web").then(function(obj){
					s.guias_web = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},



			getProductos: function(s){
				$http.get("api/productos").then(function(obj){
					s.productos = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getProductosVenta: function(s){
				var resturl = "api/productos/" + (LoginService.getCurrentUser().departamento ? LoginService.getCurrentUser().departamento : "1") + "/" + LoginService.getCurrentUser().username;

				$http.get(resturl).then(function(obj){
					s.productos = obj.data;
					console.log(obj.data)
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getProducto: function(s, id){
				$http.get("api/productos").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							json[i].exento_iva = json[i].exento_iva == 1 ? true : false;
							s.producto = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},
			getProductosOriginales: function(s){
				$http.get("api/productos/1").then(function(obj){
					s.productos = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},





			getDependencias: function(s){
				$http.get("api/dependencias").then(function(obj){
					s.dependencias = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getDependencia: function(s, id){
				$http.get("api/dependencias").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							s.dependencia = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},






			getClientes: function(s){
				$http.get("api/clientes").then(function(obj){
					s.clientes = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getCliente: function(s, id){
				$http.get("api/clientes").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							s.cliente = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},





			getFamilias: function(s){
				$http.get("api/productos/familias").then(function(obj){
					s.familias = [];

					if (window.location.hash.indexOf("reportes") != -1)
						s.familias.push({
							id: -1,
							nombre: "Todos"
						});

					for (var i = 0; i < obj.data.length; i++)
						s.familias.push(obj.data[i]);

					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getFamilia: function(s, id){
				$http.get("api/productos/familias").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							s.familia = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},






			getCuentaAbiertas: function(s){
				$http.get("api/cuentaabiertas").then(function(obj){
					s.cuentaabiertas = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getCuentaAbierta: function(s, id){
				$http.get("api/cuentaabiertas").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							if (json[i].inicia)
							{
								var date = json[i].inicia.split('-');
								json[i].inicia = new Date(parseInt(date[0]), parseInt(date[1])-1, parseInt(date[2]), 12, 0, 0, 0);
							}

							if (json[i].vence)
							{
								var date = json[i].vence.split('-');
								json[i].vence = new Date(parseInt(date[0]), parseInt(date[1])-1, parseInt(date[2]), 12, 0, 0, 0);
							}

							for (var k = 0; k < json[i].productos.length; k++)
							{
								json[i].productos[k].nro_copias = parseInt(json[i].productos[k].copias);
								json[i].productos[k].nro_originales = parseInt(json[i].productos[k].originales);
							}

							s.cuentaabierta = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},





			getCondicionesPago: function(s){
				$http.get("api/condicionesdepago").then(function(obj){
					s.condiciones_pago = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},





			getDepartamentos: function(s){
				$http.get("api/departamentos").then(function(obj){
					s.departamentos = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getDepartamento: function(s, id){
				$http.get("api/departamentos").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							s.departamento = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},





			getDepartamentosUCAB: function(s){
				$http.get("api/departamentos/ucab").then(function(obj){
					s.departamentos = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getDepartamentoUCAB: function(s, id){
				$http.get("api/departamentos/ucab").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							s.departamento = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},





			getRetirosCaja: function(s){
				$http.get("api/caja/retiros").then(function(obj){
					s.retiros = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getRetiroCaja: function(s, id){
				$http.get("api/caja/retiros").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							s.retiro = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},





			getInventario: function(s){
				$http.get("api/inventario").then(function(obj){
					s.inventario = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getInventarioAsignado: function(s){
				$http.get("api/inventario/asignado").then(function(obj){
					s.inventario_asignado = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getMiInventarioAsignado: function(s){
				$http.get("api/inventario/asignado/mio").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						json[i].restante = parseInt(json[i].restante);

					s.mi_inventario_asignado = json;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getMaterial: function(s, id){
				$http.get("api/inventario").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							s.material = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},
			getMaterialesGuias: function(s){
				$http.get("api/inventario/productos/guias").then(function(obj){
					s.productos = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},





			getOrdenes: function(s){
				$http.get("api/ordenes").then(function(obj){
					s.ordenes = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getOrden: function(s, id){
				$http.get("api/ordenes").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							/*var date = json[i].fecha_inicio.split('-');
							json[i].fecha_inicio = new Date(date[0], parseInt(date[1])-1, date[2], 12, 0, 0, 0);

							date = json[i].fecha_fin.split('-');
							json[i].fecha_fin = new Date(date[0], parseInt(date[1])-1, date[2], 12, 0, 0, 0);*/

							if (json[i].fecha)
							{
								var date = json[i].fecha.split('-');
								json[i].fecha = new Date(parseInt(date[0]), parseInt(date[1])-1, parseInt(date[2]), 12, 0, 0, 0);
							}

							json[i].dependencia = json[i].did;

							for (var k = 0; k < json[i].productos.length; k++)
							{
								json[i].productos[k].nro_copias = parseInt(json[i].productos[k].copias);
								json[i].productos[k].nro_originales = parseInt(json[i].productos[k].originales);
							}

							s.orden = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},



			getPedidos: function(s){
				$http.get("api/pedidos").then(function(obj){
					s.pedidos = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getPedido: function(s, id){
				$http.get("api/pedidos").then(function(obj){
					var json = obj.data;

					for (var i = 0; i < json.length; i++)
						if (json[i].id == id)
						{
							/*var date = json[i].fecha_inicio.split('-');
							json[i].fecha_inicio = new Date(date[0], parseInt(date[1])-1, date[2], 12, 0, 0, 0);

							date = json[i].fecha_fin.split('-');
							json[i].fecha_fin = new Date(date[0], parseInt(date[1])-1, date[2], 12, 0, 0, 0);*/

							if (json[i].fecha)
							{
								var date = json[i].fecha.split('-');
								json[i].fecha = new Date(parseInt(date[0]), parseInt(date[1])-1, parseInt(date[2]), 12, 0, 0, 0);
							}

							json[i].dependencia = json[i].did;

							for (var k = 0; k < json[i].productos.length; k++)
							{
								json[i].productos[k].nro_copias = parseInt(json[i].productos[k].copias);
								json[i].productos[k].nro_originales = parseInt(json[i].productos[k].originales);
							}

							s.pedido = json[i];
							$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
							return;
						}
				});
			},



			/*
			** s : Array = $scope
			** r : String = Nombre del reporte
			** f : Array = Filtros
			*/
			getReporte: function(s, r, f){
				if (f.desde)
					f.desde_ = f.desde.toJSON().slice(0,10);

				if (f.hasta)
					f.hasta_ = f.hasta.toJSON().slice(0,10);

				if (f.dia)
					f.dia_ = f.dia.toJSON().slice(0,10);

				$.ajax({
				    url: "api/reporte/" + r,
				    type: "POST",
				    data: f,
				    beforeSend: function(){},
				    success: function(data){
				    	try 
				    	{
				        	var json = $.parseJSON(data);

				        	for (var i = 0; i < json.length; i++)
				        	{
				        		if (json[i].total_facturado)
				        			json[i].total_facturado = parseFloat(json[i].total_facturado);

				        		if (json[i].devoluciones)
				        			json[i].devoluciones = parseFloat(json[i].devoluciones);

				        		if (json[i].nota_de_credito)
				        			json[i].nota_de_credito = parseFloat(json[i].nota_de_credito);

				        		if (json[i].retiro_de_caja)
				        			json[i].retiro_de_caja = parseFloat(json[i].retiro_de_caja);



				        		if (json[i].total_facturado)
				        			json[i].total_facturado = parseFloat(json[i].total_facturado);

				        		if (json[i].total_devoluciones)
				        			json[i].total_devoluciones = parseFloat(json[i].total_devoluciones);

				        		if (json[i].total_notas_credito)
				        			json[i].total_notas_credito = parseFloat(json[i].total_notas_credito);
				        	}

				        	s.data = json;
				        }
				        catch (ex)
				        {
				        	console.log(data)
				        }
				    }
				});
			},


		};
	})
}());