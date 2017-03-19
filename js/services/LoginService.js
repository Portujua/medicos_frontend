(function(){
	angular.module("medicos").factory('LoginService', function($http, $location, AlertService, $localStorage, $interval, RESTService){
		//session.$reset();
		if (!$localStorage.medicos) {
			$localStorage.medicos = {};
		}
		else {
			if ($localStorage.medicos.user) {
				RESTService.post('reload', { username: $localStorage.medicos.user.usuario })
					.then((response) => {
						$localStorage.medicos.user = response.data;
					})
			}
		}

		var session = $localStorage.medicos;

		return {
			isLoggedIn: function(){
				return typeof session.user != 'undefined';
			},
			logout: function(){
				$http.get("php/unset.php").then(function(){
					delete session.user;
					window.location.reload(true);
				});
			},
			updateSessionTime: function(){
				$http.get("php/run.php?fn=actualizar_hora_sesion");
			},
			login: function(loginData){
				delete session.user;

				RESTService.post('login', {username:loginData.username, password:loginData.password})
					.then((response) => {
						var data = response.data;
						
						if (data.error)
							AlertService.showError("Usuario o contraseña inválida");
						else
						{
							session.user = data;
							session.session_key = session.now_key ? session.now_key : Math.random();
							session.last_session_date = new Date().getTime();

							$localStorage.user = data;

							console.log(session)

							$location.path("/resumen");
						}
					})
			},
			getCurrentUser: function(){
				return session.user;
			},
			resetIdle: function(){
				window.onmousemove = function(){ session.idle_time = 0; session.last_date_idle = new Date().getTime(); };
				window.onkeypress = function(){ session.idle_time = 0; session.last_date_idle = new Date().getTime(); };
			},
			reload: () => {
				RESTService.post('reload', { username: session.user.usuario })
					.then((response) => {
						session.user = response.data;
					})
				},
			startTimer: function(){
				this.resetIdle();
				return;
				var loginService = this;

				$interval(function(){
					if (session.user)
						if (session.user.es_admin) 
							return;
					
					session.idle_time++;

					if ($(".jconfirm").length == 0)
						if (
							(session.idle_time > session.session_time && loginService.isLoggedIn()) || 
							(session.now_key != session.session_key && window.location.hash.indexOf("login") == -1)
							)
						{
							session.now_key = session.session_key;

							$.confirm({
								title: "Confirmar contraseña",
								content: '<p>Has estado un tiempo inactivo o bien has refrescado la página, por favor introduce tu clave de nuevo para desbloquear el sistema.</p><div class="form-group"><input autofocus type="password" id="password" placeholder="Contraseña" class="form-control"></div><p>Tiene ' + (3 - session.password_attempts) + ' intentos restantes antes que sea expulsado del sistema</p>',
								keyboardEnabled: true,
								backgroundDismiss: false,
								confirm: function(){
									var pwd = this.$b.find("input").val();
									
									if (pwd != session.user.password)
									{
										if (session.password_attempts >= 3)
											loginService.logout();
										else
										{
											session.now_key = Math.random();
											session.password_attempts++;
										}
									}
									else
										loginService.updateSessionTime();
								},
								cancel: function(){
									if (session.password_attempts >= 3)
										loginService.logout();
									else
									{
										session.now_key = Math.random();
										session.password_attempts++;
									}
								}
							});
						}
				}, 1000)
			}
		};
	})
}());