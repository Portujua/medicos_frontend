(function(){
	var Suscripcion = function($scope, $http, $location, $routeParams, $timeout, $window, AlertService, RESTService)
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

		$scope.editar = $routeParams.id;

		$scope.cargar_suscripciones = function(){
			RESTService.getSuscripciones($scope);
		}

		$scope.cargar_suscripcion = function(id){
			RESTService.getSuscripcion($scope, id);
		}

		$scope.registrar_suscripcion = function(){
			$.confirm({
				title: 'Confirmar acción',
				content: '¿Está seguro que desea añadir la suscripcion <strong>' + $scope.suscripcion.nombre + '</strong>?',
				confirm: function(){
					var post = $scope.suscripcion;

					var fn = "agregar_suscripcion";
					var msg = "Suscripcion añadida con éxito";
					
					if ($routeParams.id)
					{
						fn = "editar_suscripcion";
						msg = "Suscripcion modificada con éxito";
					}

					$http({
						method: 'POST',
						url: "php/run.php?fn=" + fn,
						data: $.param(post),
						headers: {'Content-Type': 'application/x-www-form-urlencoded'}
					}).then(function(obj){
						if (obj.data.ok)
						{
							AlertService.showSuccess(obj.data.msg);
					    	$location.path("/suscripcion");
					    }
					    else
					    	console.log(obj.data);
					});
				},
				cancel: function(){}
			});
		}

		$scope.cambiar_estado = function(id, estado){
			$.confirm({
				title: 'Eliminar suscripcion',
				content: '¿Está seguro que desea eliminar esta suscripcion?',
				confirm: function(){
					$http({
						method: 'POST',
						url: "php/run.php?fn=cambiar_estado_suscripcion",
						data: $.param({id:id, estado:estado}),
						headers: {'Content-Type': 'application/x-www-form-urlencoded'}
					}).then(function(obj){
						console.log(obj)
						if (obj.data.ok)
						{
							AlertService.showSuccess(obj.data.msg);
					    	$scope.cargar_suscripciones();
					    	$scope.p_ = null;
					    }
					    else
					    	console.log(obj.data);
					});
				}
			})
		}

		$scope.seleccionar = function(p){
			$scope.p_ = p;
		}

		if ($routeParams.id)
		{
			$scope.cargar_suscripcion($routeParams.id);
		}
	};

	angular.module("adminapp").controller("Suscripcion", Suscripcion);
}());