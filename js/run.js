angular.module("medicos")
	.run(($rootScope) => {
		$rootScope.$on('$routeChangeSuccess', function (event, current, previous) {
			// nothing
		});
	})