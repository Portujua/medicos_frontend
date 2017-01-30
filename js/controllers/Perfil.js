(function(){
	var Perfil = function($scope, $http, $location, $routeParams, $timeout, $window, AlertService, RESTService, LoginService)
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

		$scope.editar = $routeParams.cedula;

		if (!LoginService.isLoggedIn())
			$location.path("/");

		$scope.cargar_pacientes = function(){
			RESTService.getPacientes($scope);
		}

		$scope.cargar_areas = function(){
			RESTService.getAreas($scope);
		}

		$scope.cargar_medicos = function(){
			RESTService.getMedicos($scope);
		}

		$scope.cargar_paciente = function(cedula){
			$.ajax({
			    url: "api/paciente/" + cedula,
			    type: "POST",
			    data: {},
			    beforeSend: function(){},
			    success: function(data){
			        $scope.safeApply(function(){
			        	var json = $.parseJSON(data);
			        	$scope.paciente = json;
			        })
			    }
			});
		}

		$scope.cargar_lugares = function(){
			RESTService.getLugares($scope);
		}

		$scope.cargar_parroquias = function(){
			RESTService.getParroquias($scope);
		}

		$scope.cargar_tipos_telefonos = function(){
			RESTService.getTiposTelefonos($scope);
		}

		$scope.autocomplete_lugares = function(){
			var availableTags = [];
			var json = $scope.lugares;

			for (var i = 0; i < json.length; i++)
				if (json[i].tipo == "parroquia")
					availableTags.push({
						label: json[i].nombre_completo,
						value: json[i].nombre_completo
					})

			$( "input[name=lugar]" ).autocomplete({
				source: function(request, response) {
			        var results = $.ui.autocomplete.filter(availableTags, request.term);

			        response(results.slice(0, 10));
			    },
				minLength: 4,
				delay: 0,
				select: function(event, ui){
					$scope.safeApply(function(){
						$scope.paciente.lugar = ui.item.value;
					})
				}
			});
		}

		$scope.anadir_telefono = function(){
			$scope.paciente.telefonos.push({
				tipo: $scope.telefono.tipo,
				tlf: $scope.telefono.tlf
			});

			$scope.telefono = null;
		}

		$scope.eliminar_telefono = function(index){
			var aux = [];

			for (var i = 0; i < $scope.paciente.telefonos.length; i++)
				if (i != index)
					aux.push($scope.paciente.telefonos[i]);

			$scope.paciente.telefonos = aux;
		}

		$scope.comprar_suscripcion = function(dias){
			$http({
				method: 'POST',
				url: "php/run.php?fn=agregar_suscripcion",
				data: $.param({dias: dias, usuario: LoginService.getCurrentUser().id}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then((response) => {
				console.log(response)

				if (response.data.ok) {
					AlertService.showSuccess(response.data.msg);
				}
				else {
					AlertService.showError("Ha ocurrido un error");
					console.log(response.data.msg);
				}
			})
		}

		$scope.cargar_mas = function(){
			if ($scope.chat.mensajes.length == 0) return;

			var hid = $scope.chat.mensajes[0].id;

			for (var i = 0; i < $scope.chat.mensajes.length; i++)
				hid = $scope.chat.mensajes[i].id < hid ? $scope.chat.mensajes[i].id : hid;

			$scope.chat_cargar_mensajes({
					medico: $scope.chat_info.medico, 
					paciente: LoginService.getCurrentUser().id,
					n: 10,
					last: hid
			}).then((response) => {
				for (var i = 0; i < $scope.chat.mensajes.length; i++)
					response.data.mensajes.push($scope.chat.mensajes[i]);

				$scope.chat.mensajes = response.data.mensajes;
			});
		}

		$scope.sumar_mensajes = function(ms){
			var aux = [];

			for (var k = 0; k < ms.length; k++) {
				var c = true;

				for (var i = 0; i < $scope.chat.mensajes.length; i++)
					if ($scope.chat.mensajes[i].html == ms[k].html && $scope.chat.mensajes[i].hora_str == ms[k].hora_str)
						c = false;

				if (c)
					$scope.chat.mensajes.push(ms[k]);
			}

			// Remuevo los 'ahora'
			for (var i = 0; i < $scope.chat.mensajes.length; i++)
				if ($scope.chat.mensajes[i].hora_str)
					aux.push($scope.chat.mensajes[i]);

			$scope.chat.mensajes = aux;
		}

		$scope.go_chat = function(info, status = true){
			$scope.isChatting = status && !$scope.isClosed;

			if (!$scope.isChatting) return;

			$scope.isClosed = false;

			$scope.chat_cargar_mensajes({
				medico: info.medico, 
				paciente: LoginService.getCurrentUser().id,
				n: 10,
				last: -1
			}).then((response) => {
				if ($scope.chat)
					$scope.sumar_mensajes(response.data.mensajes);
				else
					$scope.chat = response.data;
				
				$timeout(() => {
					$scope.autoscroll();
				}, 100);

				$timeout(() => {
					$scope.go_chat(info);
				}, 10000);
			});
		}

		$scope.chat_cargar_mensajes = function(info){
			return $http({
				method: 'POST',
				url: "php/run.php?fn=cargar_mensajes",
				data: $.param(info),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			});
		}

		$scope.enviar_mensaje = function(event){
			if (event) {
				if (event.keyCode == 13)
					$scope.enviar_mensaje();

				return;
			}

			$http({
				method: 'POST',
				url: "php/run.php?fn=agregar_mensaje",
				data: $.param({
					medico: $scope.chat_info.medico,
					paciente: LoginService.getCurrentUser().id,
					mensaje: $scope.chat_info.mensaje,
					owner: LoginService.getCurrentUser().usuario,
					owner_name: LoginService.getCurrentUser().nombre + " " + LoginService.getCurrentUser().apellido
				}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then((response) => {
				if (response.data.ok) {
					$scope.chat.mensajes.push({
						owner: LoginService.getCurrentUser().usuario,
						html: $scope.chat_info.mensaje
					});

					$scope.chat_info.mensaje = '';
					
					$timeout(() => {
						$scope.autoscroll();
					}, 100);
				}
				else
					console.log(response.data)
			});
		}

		$scope.autoscroll = function(){
			//Auto-scroll			
			var newscrollHeight = parseInt($(".mensajes").css("height")) - 10;
			
			$(".mensajes").animate({ scrollTop: newscrollHeight }, 0); //Autoscroll to bottom of div
		}

		if ($routeParams.cedula)
		{
			$scope.cargar_paciente($routeParams.cedula);
		}
	};

	angular.module("medicos").controller("Perfil", Perfil);
}());