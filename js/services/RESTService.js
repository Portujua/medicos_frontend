(function(){
	angular.module("medicos").factory('RESTService', function($http, $timeout){
		return {
			get: (url, data = {}) => {
				let params = "";

				for (let key in data) {
		      if (data.hasOwnProperty(key)) {
		         params += (params.length > 0 ? '&' : '') + `${key}=${data[key]}`;
		      }
		    }

				return $http.get(`api/${url}?${params}`);
			},

			post: (url, data = {}) => {
				return $http({
					method: 'POST',
					url: `api/${url}`, 
					data: $.param(data),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				});
			},

			put: (url, data = {}) => {
				return $http({
					method: 'PUT',
					url: `api/${url}`, 
					data: $.param(data),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				});
			},

			delete: (url, data = {}) => {
				return $http({
					method: 'DELETE',
					url: `api/${url}`, 
					data: $.param(data),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				});
			},

			getMedicos: function(s){
				$http.get("api/medicos").then(function(obj){
					s.medicos = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},

			getTSuscripcion: function(s){
				$http.get("api/suscripcion/tipos").then(function(obj){
					s.tsuscripciones = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},
			getMedico: function(s, cedula){
				$http.get("api/medicos").then(function(obj){
					console.log(obj.data)
					for (var i = 0; i < obj.data.length; i++)
						if (obj.data[i].cedula == cedula)
						{
							console.log(obj.data[i])
							s.medico = obj.data[i];
						}
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},

			getPacientes: function(s){
				$http.get("api/pacientes").then(function(obj){
					s.pacientes = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},

			getTiposTelefonos: function(s){
				$http.get("api/telefonos/tipos").then(function(obj){
					s.tipos_telefonos = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},

			getLugares: function(s){
				$http.get("api/lugares").then(function(obj){
					s.lugares = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
					s.autocomplete_lugares();
				});
			},

			getParroquias: function(s){
				$http.get("api/lugares/parroquias").then(function(obj){
					s.parroquias = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},

			getAreas: function(s){
				$http.get("api/areas").then(function(obj){
					s.areas = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},

			getArea: function(s, id){
				$http.get("api/areas").then(function(obj){
					for (var i = 0; i < obj.data.length; i++){
						if (obj.data[i].id == id)
							s.area = obj.data[i];
					}
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},

			getCitas: function(s, medico){
				$http.get(`api/citas/${medico}`).then(function(obj){
					s.citas = obj.data;
					$timeout(function(){$('.selectpicker').selectpicker('refresh');}, 500);
				});
			},

		};
	})
}());