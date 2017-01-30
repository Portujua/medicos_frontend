(function(){
	angular.module("medicos").config(function($routeProvider, $locationProvider){
		$routeProvider
			.when("/", {
				templateUrl : "views/inicio.html"
			})

			.when("/perfil", {
				templateUrl : "views/perfil.html"
			})

			.otherwise({redirectTo : "/"});
	});
}());