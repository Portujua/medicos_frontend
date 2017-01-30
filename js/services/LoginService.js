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
						$localStorage.session_key = $localStorage.now_key ? $localStorage.now_key : Math.random();
						$localStorage.last_session_date = new Date().getTime();
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
			}
		};
	})
}());