;(() => {
angular.module('medicos')
  .directive('chat', ($compile) => ({
    templateUrl: 'js/directives/chat/chat.html',
    restrict: 'E',
    controller: 'ChatController',
    controllerAs: '$ctrl',
    replace: true,
    link: ($scope, element, attrs, api) => {
      
    },
    scope: {
      paciente: '=',
      medico: '='
    },
  }));
})();