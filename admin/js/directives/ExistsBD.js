(function(){
	angular.module("adminapp").directive("existsBd", function($http){
		return {
			require: 'ngModel',
			link: function(scope, ele, attrs, c) {
				scope.$watch(attrs.ngModel, function(){
					var val = ele[0].value;
					var obj = attrs.existsBd;

					if (window.location.hash.indexOf("editar") == -1) {
						if (val.length > 0) {
							$http.get("api/check/" + obj + "/" + val)
								.success(function(data){
									c.$setValidity('unique', data.esValido);
								})
								.error(function(data){
									c.$setValidity('unique', false);
								})
							}
							else {
								c.$setValidity('unique', false);
							}
					}
					else {
						c.$setValidity('unique', true);
					}
				})
			}
		}
	})
}());