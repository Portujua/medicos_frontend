(function(){
	angular.module("medicos").config(function($routeProvider, $locationProvider){
		$routeProvider
			.when("/", {
				templateUrl : "views/inicio.html"
			})

			.otherwise({redirectTo : "/"});
	});
}());