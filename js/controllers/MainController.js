(function(){
	var MainController = function($scope, $http, $location, $routeParams, $interval, $timeout, $window, LoginService, AlertService)
	{		
		$scope.session = LoginService;
		$scope.loginform = {
			username: '',
			password: ''
		}

		$scope.login = function(){
			LoginService.login($scope.loginform);
		}

		$scope.checkSession = function(){
			return LoginService.isLoggedIn();
		}

		$scope.isLocation = function(str) {
			return window.location.hash.indexOf(str) > -1;
		}

		$interval(() => {
			$scope.session = LoginService;
		}, 1000);
	};

	angular.module("medicos").controller("MainController", MainController);
}());