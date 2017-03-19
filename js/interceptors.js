angular.module("medicos")
	.config(($httpProvider) => {
    // $httpProvider.defaults.transformResponse.push((response) => {
    //   convertDateStringsToDates(response);
    //   return response;
    // });

    $httpProvider.interceptors.push(['$q', '$location', '$injector', ($q, $location, $injector) => {
      return {
        response: (response) => {
          let toastr = $injector.get('toastr');
          
          for (let i = 0; i < response.data.length; i++) {
            for (let key in response.data[i]) {
              if (response.data[i].hasOwnProperty(key)) {
                if (/^[0-9]+$/.test(response.data[i][key])) {
                  response.data[i][key] = parseInt(response.data[i][key]);
                }
              }
            }
          }

          if (_.isObject(response.data)) {
            if (response.data.msg) {
              if (response.data.ok) {
                toastr.success(response.data.msg);
              }
              else {
                toastr.error(response.data.msg);
              }
            }
          }

          return response;
        },
      };
    }]);
  });
