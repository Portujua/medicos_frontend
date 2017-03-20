(function(){
	var Perfil = function($scope, $http, $location, $routeParams, $timeout, $window, AlertService, RESTService, LoginService)
	{		
		$scope.$on('$routeChangeSuccess', () => {
			if (!LoginService.isLoggedIn()) {
				$location.path("/");
			}

			this.cargar_pacientes();
			this.cargar_tsuscripciones();
			this.cargar_areas();
			this.cargar_medicos();
		});

		this.reloadDashboardTime = 60 * 1000;
		$scope.editar = $routeParams.cedula;

		this.cargar_pacientes = () => {
			RESTService.get('pacientes').then((response) => {
				this.pacientes = response.data;
				this.hayMensajesNoLeidos = false;

				for (let i = 0; i < this.pacientes.length; i++) {
					if ($routeParams.paciente && LoginService.getCurrentUser().es_medico) {
						if (this.pacientes[i].usuario.toUpperCase() == $routeParams.paciente.toUpperCase()) {
							this.paciente = this.pacientes[i];
						}
					}

					RESTService.get('mensajes/pendientes', {
						medico: LoginService.getCurrentUser().id,
						paciente: this.pacientes[i].id,
						usuario: LoginService.getCurrentUser().usuario
					}).then((response) => {
						this.pacientes[i].mensajes_pendientes = parseInt(response.data.cantidad);
						this.pacientes[i].ultimo_mensaje = response.data.ultimo;

						if (parseInt(response.data.cantidad)) {
							this.hayMensajesNoLeidos = true;
						}
					})
				}
			})

			$timeout(() => {
				this.cargar_pacientes();
			}, this.reloadDashboardTime)
		}

		this.cargar_tsuscripciones = () => {
			RESTService.getTSuscripcion($scope);
		}

		this.cargar_areas = () => {
			RESTService.get('areas')
				.then((response) => {
					this.areas = response.data;
				})
		}

		this.cargar_medicos = () => {
			RESTService.get('medicos').then((response) => {
				this.medicos = response.data;
				this.hayMensajesNoLeidos = false;

				for (let i = 0; i < this.medicos.length; i++) {
					if ($routeParams.medico && !LoginService.getCurrentUser().es_medico) {
						if (this.medicos[i].usuario.toUpperCase() == $routeParams.medico.toUpperCase()) {
							this.medico = this.medicos[i];
						}
					}

					RESTService.get('mensajes/pendientes', {
						medico: this.medicos[i].id,
						paciente: LoginService.getCurrentUser().id,
						usuario: LoginService.getCurrentUser().usuario
					}).then((response) => {
						this.medicos[i].mensajes_pendientes = parseInt(response.data.cantidad);
						this.medicos[i].ultimo_mensaje = response.data.ultimo;

						if (parseInt(response.data.cantidad)) {
							this.hayMensajesNoLeidos = true;
						}
					})
				}
			})

			$timeout(() => {
				this.cargar_medicos();
			}, this.reloadDashboardTime)
		}

		this.cargar_paciente = (cedula) => {
			RESTService.get(`paciente/${cedula}`)
				.then((response) => {
					this.paciente = response.data;
				})
		}

		this.actualizar = () => {
			RESTService.put(LoginService.getCurrentUser().es_medico ? 'medico' : 'paciente', this.paciente)
				.then((response) => {
					LoginService.reload();
				})
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

		$scope.comprar_suscripcion = function(tiposuscripcion){
			$http({
				method: 'POST',
				url: "php/run.php?fn=agregar_suscripcion",
				data: $.param({tsuscripcion: tiposuscripcion , usuario: LoginService.getCurrentUser().id}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then((response) => {
				console.log(response)

				if (response.data.ok) {
					LoginService.login({username: LoginService.getCurrentUser().usuario, password: LoginService.getCurrentUser().contrasena});
				}
				else {
					AlertService.showError("Ha ocurrido un error");
					console.log(response.data.msg);
				}
			})
		}

		if ($routeParams.cedula)
		{
			$scope.cargar_paciente($routeParams.cedula);
		}
	};

	angular.module("medicos").controller("Perfil", Perfil);
}());