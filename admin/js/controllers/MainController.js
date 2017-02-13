(function(){
	var MainController = function($scope, $http, $location, $routeParams, $interval, $timeout, $window, LoginService, AlertService)
	{		
		$scope.safeApply = function(fn) {
		    var phase = this.$root.$$phase;
		    if(phase == '$apply' || phase == '$digest') {
		        if(fn && (typeof(fn) === 'function')) {
		          fn();
		        }
		    } else {
		       this.$apply(fn);
		    }
		};

		$scope.loginService = LoginService;
		$scope.enInicio = true;
		$scope.express = window.location.hash.indexOf('express') != -1;

		$scope.url = window.location;

		$scope.nroResultados = 10; 

		$scope.login_form = {
			username: "root",
			password: "root"
		};

		if (LoginService.getCurrentUser() == null && window.location.hash.indexOf("recuperar") == -1)
			$location.path("/login");

		$scope.paginationCount = function(n, total){
			var k = Math.ceil(total/n);
			var a = [];

			for (var i = 0; i < k; i++)
				a.push(i);

			return a;
		}

		$scope.cerrar_seccion = function(){
			if (window.location.hash.indexOf("editar") != -1 || window.location.hash.indexOf("agregar") != -1)
				$.confirm({
					title: "Confirmar acción",
					content: "Todo los cambios serán descartados, <strong>¿está seguro que desea cerrar esta ventana?</strong>",
					confirm: function(){
						$location.path("/inicio");
					}
				})
			else
				$location.path("/inicio");
		}

		$scope.login = function(){
			LoginService.login($scope.login_form);
		}

		$scope.logout = function(){
			$.confirm({
				title: '',
				content: '¿Está seguro que desea salir del sistema?',
				confirm: function(){
					LoginService.logout();
				},
				cancel: function(){}
			});
		}

		$scope.unset_session = function(){
			LoginService.logout();
		}

		$scope.enviar_correo_recuperacion = function(){
			var post = {
				usuario: $scope.username_forgot_password
			}

			$.ajax({
			    url: "php/run.php?fn=sendEmail",
			    type: "POST",
			    data: post,
			    beforeSend: function(){},
			    success: function(data){
			    	console.log(data)
			        $scope.safeApply(function(){
			        	var json = $.parseJSON(data);
			        	
			        	if (json.error)
			        		$scope.error_olvido_clave = json.msg;
			        	else
			        		$scope.msg_clave_olvidada = json.msg;
			        })
			    }
			});
		}

		$scope.cambiar_contrasena = function(){
			var post = {
				contrasena: $scope.nueva_contrasena,
				usuario: $routeParams.usuario
			}

			$.ajax({
			    url: "php/run.php?fn=cambiar_contrasena",
			    type: "POST",
			    data: post,
			    beforeSend: function(){},
			    success: function(data){
			    	console.log(data)
			        $scope.safeApply(function(){
			        	var json = $.parseJSON(data);
			        	
			        	if (json.ok)
			        		AlertService.showSuccess(json.msg);
			        	else
			        		AlertService.showError(json.msg)

			        	$location.path("/login");
			        })
			    }
			});
		}
	};

	angular.module("adminapp").controller("MainController", MainController);
}());