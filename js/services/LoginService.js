(function(){
	angular.module("medicos").factory('LoginService', function($http, $location, AlertService, $localStorage, $interval){
		return {
			isLoggedIn: function(){
				return typeof $localStorage.user != 'undefined';
			},
			logout: function(){
				$http.get("php/unset.php").then(function(){
					$localStorage.$reset();
					window.location.reload(true);
				});
			},
			updateSessionTime: function(){
				$http.get("php/run.php?fn=actualizar_hora_sesion");
			},
			login: function(loginData){
				$http({
					method: 'POST',
					url: "php/run.php?fn=login", 
					data: $.param({username:loginData.username, password:loginData.password}),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				}).then(function(obj){
					console.log(obj)
					var data = obj.data;
					if (data.error)
						AlertService.showError("Usuario o contraseña inválida");
					else
					{
						$localStorage.user = data;
						$localStorage.user.password = loginData.password;
						$localStorage.session_key = $localStorage.now_key ? $localStorage.now_key : Math.random();
						$localStorage.last_session_date = new Date().getTime();
				        $location.path("/inicio");
					}
				});
			},
			getCurrentUser: function(){
				return $localStorage.user;
			},
			resetIdle: function(){
				window.onmousemove = function(){ $localStorage.idle_time = 0; $localStorage.last_date_idle = new Date().getTime(); };
				window.onkeypress = function(){ $localStorage.idle_time = 0; $localStorage.last_date_idle = new Date().getTime(); };
			},
			startTimer: function(){
				this.resetIdle();
				return;
				var loginService = this;

				$interval(function(){
					if ($localStorage.user)
						if ($localStorage.user.es_admin) 
							return;
					
					$localStorage.idle_time++;

					if ($(".jconfirm").length == 0)
						if (
							($localStorage.idle_time > $localStorage.session_time && loginService.isLoggedIn()) || 
							($localStorage.now_key != $localStorage.session_key && window.location.hash.indexOf("login") == -1)
							)
						{
							$localStorage.now_key = $localStorage.session_key;

							$.confirm({
								title: "Confirmar contraseña",
								content: '<p>Has estado un tiempo inactivo o bien has refrescado la página, por favor introduce tu clave de nuevo para desbloquear el sistema.</p><div class="form-group"><input autofocus type="password" id="password" placeholder="Contraseña" class="form-control"></div><p>Tiene ' + (3 - $localStorage.password_attempts) + ' intentos restantes antes que sea expulsado del sistema</p>',
								keyboardEnabled: true,
								backgroundDismiss: false,
								confirm: function(){
									var pwd = this.$b.find("input").val();
									
									if (pwd != $localStorage.user.password)
									{
										if ($localStorage.password_attempts >= 3)
											loginService.logout();
										else
										{
											$localStorage.now_key = Math.random();
											$localStorage.password_attempts++;
										}
									}
									else
										loginService.updateSessionTime();
								},
								cancel: function(){
									if ($localStorage.password_attempts >= 3)
										loginService.logout();
									else
									{
										$localStorage.now_key = Math.random();
										$localStorage.password_attempts++;
									}
								}
							});
						}
				}, 1000)
			},
			menuGuias: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.anadir_guias || $localStorage.user.buscar_guias || $localStorage.user.modificar_guias;
			},
			menuOrdenes: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.ordenes_agregar || $localStorage.user.ver_ordenes_web || $localStorage.user.ordenes_editar || $localStorage.user.ordenes_deshabilitar || $localStorage.user.ordenes_ver || $localStorage.user.ordenes_ver_detalle;
			},
			menuPedidos: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.pedidos_agregar || $localStorage.user.ver_pedidos_web || $localStorage.user.pedidos_editar || $localStorage.user.pedidos_deshabilitar || $localStorage.user.pedidos_ver;
			},
			menuPensum: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.plandeestudios_agregar || $localStorage.user.plandeestudios_buscar || $localStorage.user.plandeestudios_modificar;
			},
			menuAdminPersonal: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.personal_ver || $localStorage.user.personal_agregar || $localStorage.user.personal_editar || $localStorage.user.personal_deshabilitar;
			},
			menuAdminProfesores: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.profesores_agregar || $localStorage.user.profesores_editar || $localStorage.user.profesores_deshabilitar;
			},
			menuAdminRetirosCaja: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.retiroscaja_agregar;
			},
			menuAdminNotasCredito: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.notascredito_agregar;
			},
			menuAdminCarreras: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.carreras_agregar || $localStorage.user.carreras_editar || $localStorage.user.carreras_deshabilitar;
			},
			menuAdminMaterias: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.materias_agregar || $localStorage.user.materias_editar || $localStorage.user.materias_deshabilitar;
			},
			menuAdminDepartamentosUCAB: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.dptoucab_agregar || $localStorage.user.dptoucab_editar || $localStorage.user.dptoucab_deshabilitar;
			},
			menuAdminDependencias: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.dependencias_agregar || $localStorage.user.dependencias_editar || $localStorage.user.dependencias_deshabilitar;
			},
			menuAdminProveedores: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.proveedores_agregar || $localStorage.user.proveedores_editar || $localStorage.user.proveedores_deshabilitar;
			},
			menuAdminCuentaAbiertas: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.cuentaabiertas_agregar || $localStorage.user.cuentaabiertas_editar || $localStorage.user.cuentaabiertas_deshabilitar || $localStorage.user.cuentaabiertas_ver_detalle;
			},
			menuAdminInventario: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.inventario_agregar_material || $localStorage.user.inventario_editar_material || $localStorage.user.inventario_deshabilitar_material || $localStorage.user.inventario_agregar_stock || $localStorage.user.inventario_editar_stock || $localStorage.user.inventario_eliminar_stock;
			},
			menuAdminProductos: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.productos_agregar || $localStorage.user.productos_editar || $localStorage.user.productos_deshabilitar || $localStorage.user.productos_nuevos_precios || $localStorage.user.productos_eliminar_precios;
			},
			menuAdminClientes: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.clientes_agregar || $localStorage.user.clientes_editar || $localStorage.user.clientes_deshabilitar;
			},
			menuAdmin: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return this.menuAdminPersonal() || this.menuAdminProfesores() || this.menuAdminCarreras() || this.menuAdminMaterias() || this.menuAdminDepartamentosUCAB() || this.menuAdminDependencias() || this.menuAdminCuentaAbiertas() || this.menuAdminInventario() || this.menuAdminProductos() || this.menuAdminProveedores() || this.menuAdminClientes() || this.menuAdminRetirosCaja();
			},
			permisos: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.permisos_agregar;
			},
			menuCaja: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.caja_realizarcobro;
			},
			menuReportes: function(){
				if (!this.isLoggedIn()) return false;

				if ($localStorage.user.es_admin) return true;

				return $localStorage.user.reportes_libro_de_ventas || $localStorage.user.reportes_cuadre_ventas_diarias || $localStorage.user.reportes_venta_productos;
			},
		};
	})
}());