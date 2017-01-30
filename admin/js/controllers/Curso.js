(function(){
	var Curso = function($scope, $http, $location, $routeParams, $timeout, $window, AlertService, RESTService)
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

		$scope.cargar_cursos = function(){
			RESTService.getCursos($scope);
		}

		$scope.cargar_curso = function(id){
			$.ajax({
			    url: "api/curso/" + id,
			    type: "POST",
			    data: {},
			    beforeSend: function(){},
			    success: function(data){
			        $scope.safeApply(function(){
			        	var json = $.parseJSON(data);
			        	$scope.curso = json;
			        })
			    }
			});
		}

		$scope.registrar_curso = function(){
			$.confirm({
				title: 'Confirmar acción',
				content: '¿Está seguro que desea añadir el curso <strong>' + $scope.curso.nombre + ' ' + $scope.curso.apellido + '</strong>?',
				confirm: function(){
					var post = $scope.curso;

					var fn = "agregar_curso";
					var msg = "Curso añadida con éxito";

					if ($routeParams.id)
					{
						fn = "editar_curso";
						msg = "Curso modificada con éxito";
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
					    	$location.path("/cursos");
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
				url: "php/run.php?fn=cambiar_estado_curso",
				data: $.param({pid:id, estado:estado}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then(function(obj){
				console.log(obj)
				if (obj.data.ok)
				{
					AlertService.showSuccess(obj.data.msg);
			    	$scope.cargar_cursos();
			    	$scope.p_ = null;
			    }
			    else
			    	console.log(obj.data);
			});
		}

		$scope.seleccionar = function(p){
			$scope.p_ = p;
		}

		if ($routeParams.id)
		{
			$scope.cargar_curso($routeParams.id);
		}
	};

	angular.module("adminapp").controller("Curso", Curso);
}());