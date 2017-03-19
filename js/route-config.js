(function(){
	angular.module("medicos").config(function($routeProvider, $locationProvider){
		$routeProvider
			.when("/", {
				templateUrl : "views/inicio.html"
			})

			.when("/perfil", {
				templateUrl : "views/perfil.html",
				controller: "Perfil",
				controllerAs: "$ctrl",
				resolve: {
		      access: ["LoginService", function (LoginService) { return LoginService.isLoggedIn(); }],
		    }
			})
			.when("/resumen", {
				templateUrl : "views/resumen.html",
				controller: "Perfil",
				controllerAs: "$ctrl",
				resolve: {
		      access: ["LoginService", function (LoginService) { return LoginService.isLoggedIn(); }],
		    }
			})
			.when("/suscripcion", {
				templateUrl : "views/suscripcion.html",
				controller: "Perfil",
				controllerAs: "$ctrl",
				resolve: {
		      access: ["LoginService", function (LoginService) { return LoginService.isLoggedIn(); }],
		    }
			})
			.when("/consulta", {
				templateUrl : "views/consulta.html",
				controller: "Perfil",
				controllerAs: "$ctrl",
				resolve: {
		      access: ["LoginService", function (LoginService) { return LoginService.isLoggedIn(); }],
		    }
			})
			.when("/consulta/:medico/:paciente", {
				templateUrl : "views/consulta.html",
				controller: "Perfil",
				controllerAs: "$ctrl",
				resolve: {
		      access: ["LoginService", function (LoginService) { return LoginService.isLoggedIn(); }],
		    }
			})

			.otherwise({redirectTo : "/"});
	});
}());