(function(){
	var Area = function($scope, $http, $location, $routeParams, $timeout, $window, AlertService, RESTService)
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

		$scope.cargar_areas = function(){
			RESTService.getAreas($scope);
		}

		$scope.cargar_area = function(id){
			RESTService.getArea($scope, id);
		}

		$scope.registrar_area = function(){
			$.confirm({
				title: 'Confirmar acción',
				content: '¿Está seguro que desea añadir el area <strong>' + $scope.area.nombre + '</strong>?',
				confirm: function(){
					var post = $scope.area;

					var fn = "agregar_area";
					var msg = "Area añadida con éxito";

					if ($routeParams.id)
					{
						fn = "editar_area";
						msg = "Area modificada con éxito";
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
					    	$location.path("/areas");
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
				title: 'Eliminar area',
				content: '¿Está seguro que desea eliminar esta area?',
				confirm: function(){
					$http({
						method: 'POST',
						url: "php/run.php?fn=cambiar_estado_area",
						data: $.param({id:id, estado:estado}),
						headers: {'Content-Type': 'application/x-www-form-urlencoded'}
					}).then(function(obj){
						console.log(obj)
						if (obj.data.ok)
						{
							AlertService.showSuccess(obj.data.msg);
					    	$scope.cargar_areas();
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
			$scope.cargar_area($routeParams.id);
		}
	};

	angular.module("adminapp").controller("Area", Area);
}());