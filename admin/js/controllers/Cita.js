(function(){
	var Cita = function($scope, $http, $location, $routeParams, $timeout, $window, AlertService, RESTService)
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

		$scope.cargar_citas = function(){
			RESTService.getCitas($scope);
		}

		$scope.cargar_cita = function(id){
			$.ajax({
			    url: "api/cita/" + id,
			    type: "POST",
			    data: {},
			    beforeSend: function(){},
			    success: function(data){
			        $scope.safeApply(function(){
			        	var json = $.parseJSON(data);
			        	$scope.cita = json;
			        })
			    }
			});
		}

		$scope.registrar_cita = function(){
			$.confirm({
				title: 'Confirmar acción',
				content: '¿Está seguro que desea añadir a <strong>' + $scope.cita.nombre + ' ' + $scope.cita.apellido + '</strong>?',
				confirm: function(){
					var post = $scope.cita;

					var nac = post.fecha_nacimiento.split('/');
					post.nacimiento = nac[2] + "-" + nac[1] + "-" + nac[0];

					var fn = "agregar_cita";
					var msg = "Cita añadida con éxito";

					if ($routeParams.id)
					{
						fn = "editar_cita";
						msg = "Cita modificada con éxito";
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
					    	$location.path("/citas");
					    }
					    else
					    	console.log(obj.data);
					});
				},
				cancel: function(){}
			});
		}

		$scope.seleccionar = function(p){
			$scope.p_ = p;
		}

		if ($routeParams.id)
		{
			$scope.cargar_cita($routeParams.id);
		}
	};

	angular.module("adminapp").controller("Cita", Cita);
}());