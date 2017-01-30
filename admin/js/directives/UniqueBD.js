(function(){
	angular.module("adminapp").directive("uniqueBd", function($http){
		return {
			require: 'ngModel',
			link: function(scope, ele, attrs, c) {
				scope.$watch(attrs.ngModel, function(){
					var val = ele[0].value;
					var obj = attrs.uniqueBd;

					if (window.location.hash.indexOf("editar") == -1)
						$http.get("api/check/" + obj + "/" + val)
							.success(function(data){
								c.$setValidity('unique', data.esValido);
							})
							.error(function(data){
								if (val.length == 0)
									c.$setValidity('unique', true);
								else
									c.$setValidity('unique', false);
							})
					else
						c.$setValidity('unique', true);
				})
			}
		}
	})
}());