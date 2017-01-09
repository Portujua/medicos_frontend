/**
 * Workaround for Angular (issue #1019)[https://github.com/angular/angular.js/issues/1019]
 * Apply like this:
 * <select ng-model="data.kindOfCheese">...</select>
 * -> <select ts-select-fix ng-model="data.kindOfCheese">...</select>
 */

(function(){
    angular.module('medicos')
    .directive('tsSelectFix', function ($timeout) {
        return {
            link: function (scope, element, attrs) {
                var model = scope;

                // only works with model references in this format: "data.test"
                /*attrs.ngModel.split('.').forEach(function (part) {
                    model = model[part];
                });*/

                scope.$watch(function() {
                    return element.children().length;
                }, function() {
                    scope.$evalAsync(function() {
                        // iterate through <option>s
                        Array.prototype.some.call(element.children(), function (child) {
                            if (child.value === model.toString()) {
                                child.setAttribute('selected', 'selected');
                            }

                            // prevent select from being stuck and remove phantom option.
                            // in a setTimeout to run after digest cycle.
                            setTimeout(function () {
                                element.triggerHandler('change');
                            }, 0);
                            return false;
                        });
                    });
                });
            }
        };
    });
}());