(function(){
	angular.module("adminapp").config(function($routeProvider, $locationProvider){
		$routeProvider
			.when("/login", {
				templateUrl : "views/login.html"
			})
			.when("/inicio", {
				templateUrl : "views/inicio.html"
			})
			.when("/", {
				templateUrl : "views/login.html"
			})



			// Admin
			.when("/medicos", {
				templateUrl : "views/admin/medicos/medicos.html"
			})
			.when("/medicos/agregar", {
				templateUrl : "views/admin/medicos/agregar.html"
			})
			.when("/medicos/editar/:cedula", {
				templateUrl : "views/admin/medicos/agregar.html"
			})



			// Admin
			.when("/pacientes", {
				templateUrl : "views/admin/pacientes/pacientes.html"
			})
			.when("/pacientes/agregar", {
				templateUrl : "views/admin/pacientes/agregar.html"
			})
			.when("/pacientes/editar/:cedula", {
				templateUrl : "views/admin/pacientes/agregar.html"
			})

			.when("/areas", {
				templateUrl : "views/admin/areas/areas.html"
			})
			.when("/areas/agregar", {
				templateUrl : "views/admin/areas/agregar.html"
			})

			.when("/areas/editar/:id", {
				templateUrl : "views/admin/areas/agregar.html"
			})

			.when("/suscripciones", {
				templateUrl : "views/admin/suscripciones/suscripciones.html"
			})
			.when("/suscripciones/editar/:id", {
				templateUrl : "views/admin/suscripciones/agregar.html"
			})
			.when("/suscripciones/agregar", {
				templateUrl : "views/admin/suscripciones/agregar.html"
			})
			.when("/citas", {
				templateUrl : "views/admin/citas/citas.html"
			})
			.when("/citas/agregar", {
				templateUrl : "views/admin/citas/agregar.html"
			})
			.when("/citas/editar/:id", {
				templateUrl : "views/admin/citas/agregar.html"
			})




			.when("/recuperar/:usuario", {
				templateUrl : "views/admin/recuperar.html"
			})


			.otherwise({redirectTo : "/login"});
	});
}());