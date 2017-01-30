(function(){
	var Paciente = function($scope, $http, $location, $routeParams, $timeout, $window, AlertService, RESTService, LoginService)
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

		$scope.registrar_paciente = function(){
			$.confirm({
				title: 'Confirmar acción',
				content: '¿Está seguro que desea añadir a <strong>' + $scope.paciente.nombre + ' ' + $scope.paciente.apellido + '</strong>?',
				confirm: function(){
					var post = $scope.paciente;

					var nac = post.fecha_nacimiento.split('/');
					post.nacimiento = nac[2] + "-" + nac[1] + "-" + nac[0];

					var fn = "agregar_paciente";
					var msg = "Paciente añadido con éxito";

					if ($routeParams.cedula)
					{
						fn = "editar_paciente";
						msg = "Paciente modificado con éxito";
					}

					$http({
						method: 'POST',
						url: "php/run.php?fn=" + fn,
						data: $.param(post),
						headers: {'Content-Type': 'application/x-www-form-urlencoded'}
					}).then(function(obj){
						console.log(obj)
						if (obj.data.ok)
						{
							AlertService.showSuccess(obj.data.msg);
					    	$location.path("/");
					    }
					    else
					    	console.log(obj.data);
					});
				},
				cancel: function(){}
			});
		}

		$scope.cambiar_estado = function(id, estado){
			$http({
				method: 'POST',
				url: "php/run.php?fn=cambiar_estado_paciente",
				data: $.param({pid:id, estado:estado}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then(function(obj){
				console.log(obj)
				if (obj.data.ok)
				{
					AlertService.showSuccess(obj.data.msg);
			    	$scope.cargar_pacientes();
			    	$scope.p_ = null;
			    }
			    else
			    	console.log(obj.data);
			});
		}

		$scope.seleccionar = function(p){
			$scope.p_ = p;
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
				if (response.data.ok) {
					AlertService.showSuccess(response.data.msg);
				}
				else {
					AlertService.showError("Ha ocurrido un error");
					console.log(response.data.msg);
				}
			})
		}

		$scope.go_chat = function(info, status = true){
			$scope.isChatting = status;

			if (!$scope.isChatting) return;

			$scope.chat_cargar_mensajes({
				medico: info.medico, 
				paciente: LoginService.getCurrentUser().id,
				n: 10,
				offset: 0
			}).then((response) => {
				console.log(response.data)

				$timeout(() => {
					$scope.go_chat(info, true);
				}, 3000);
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

		if ($routeParams.cedula)
		{
			$scope.cargar_paciente($routeParams.cedula);
		}
	};

	angular.module("medicos").controller("Paciente", Paciente);
}());