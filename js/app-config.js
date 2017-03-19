(function(){
	var app = angular.module("medicos", ["ngRoute", 'ngAnimate', "angular.filter", 'angular-loading-bar', 'ngStorage', 'toastr', 'lr.upload']);

	app.filter('quitarDeshabilitados', function () {
	    return function (input) {
	    	if (!input) return null;
	    	
	    	var out = [];

	    	for (var i = 0; i < input.length; i++)
	    		if (input[i].estado == 1)
	    			out.push(input[i]);

	    	return out;
	    };
	});

	app.filter('capitalize', function() {
  return function(input, all = true) {
    var reg = (all) ? /([^\W_]+[^\s-]*) */g : /([^\W_]+[^\s-]*)/;
    return (!!input) ? input.replace(reg, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();}) : '';
  }});

	app.filter('quitarGuias', function () {
	    return function (input) {
	    	if (!input) return null;
	    	
	    	var out = [];

	    	for (var i = 0; i < input.length; i++)
	    		if (!(/^Guía "(.+)" \(Código: .+\)/).test(input[i].nombre))
	    			out.push(input[i]);

	    	return out;
	    };
	});

	app.filter('reverse', function() {
	  return function(items) {
	  	if (!items) return null;
	    return items.slice().reverse();
	  };
	});

	app.filter('timeago', function() {
	  return function(date) {
	  	if (!date) {
	  		return null;
	  	}
	    
	    let m = /^(\d\d):(\d\d):(\d\d) (AM|PM) (\d\d)\/(\d\d)\/(\d\d\d\d)$/.exec(date);

	    if (m == null) {
	    	return date;
	    }

	    let mDate = new Date(parseInt(m[7]), parseInt(m[6]) - 1, parseInt(m[5]), (m[4] == 'AM' && m[1] == '12' ? 0 : parseInt(m[1])) + (m[4] == 'PM' ? 12 : 0), parseInt(m[2]), parseInt(m[3]), 0);
	    let now = new Date();

	    let diff = now.getTime() - mDate.getTime();

	    let timeago = {
	    	seg: parseInt(Math.floor(diff / 1000)),
	    	min: parseInt(Math.floor(diff / (1000 * 60))),
	    	hours: parseInt(Math.floor(diff / (1000 * 60 * 60))),
	    	days: parseInt(Math.floor(diff / (1000 * 60 * 60 * 24))),
	    }

	    if (timeago.seg < 60) {
	    	return `${timeago.seg} segundo${timeago.seg > 1 ? 's' : ''}`;
	    }

	    if (timeago.min < 60) {
	    	return `${timeago.min} minuto${timeago.min > 1 ? 's' : ''}`;
	    }

	    if (timeago.hours < 24) {
	    	return `${timeago.hours} hora${timeago.hours > 1 ? 's' : ''}`;
	    }

	    return `${timeago.days} día${timeago.days > 1 ? 's' : ''}`;
	  };
	});

	app.filter('cut', function () {
      return function (value, wordwise, max, tail) {
          if (!value) return '';

          max = parseInt(max, 10);
          if (!max) return value;
          if (value.length <= max) return value;

          value = value.substr(0, max);
          if (wordwise) {
              var lastspace = value.lastIndexOf(' ');
              if (lastspace !== -1) {
                //Also remove . and , so its gives a cleaner result.
                if (value.charAt(lastspace-1) === '.' || value.charAt(lastspace-1) === ',') {
                  lastspace = lastspace - 1;
                }
                value = value.substr(0, lastspace);
              }
          }

          return value + (tail || ' …');
      };
  });

	app.filter('noImagenes', function() {
	  return function(html, max = 32) {
	  	if (!html) return null;

	  	if (html.indexOf('<a href') > -1) {
	  		let m = /href='(.+)' target/.exec(html);
	  		let url = m[1];
	  		return `<a href="${url}" target="_blank"><strong>Imagen (haz click para ver la imagen)</strong></a>`
	  	}

      if (!max) return html;
      if (html.length <= max) return html;

      html = html.substr(0, max);
      
      var lastspace = html.lastIndexOf(' ');
      if (lastspace !== -1) {
        //Also remove . and , so its gives a cleaner result.
        if (html.charAt(lastspace-1) === '.' || html.charAt(lastspace-1) === ',') {
          lastspace = lastspace - 1;
        }
        html = html.substr(0, lastspace);
      }

      return html + ' …';
	  };
	});

	app.filter('soloCategorias', function () {
	    return function (items_) {
	    	if (!items_) return null;

	    	var items = [];

			for (var i = 0; i < items_.length; i++)
				if ($.inArray(items_[i].categoria, items) == -1)
					items.push(items_[i].categoria);

			return items;
	    };
	});

	app.filter('calcularTotalBsDeProductos', function () {
	    return function (productos) {
	    	if (!productos) return null;

	    	var total = 0.0;

			for (var i = 0; i < productos.length; i++)
				total += (productos[i].costo_unitario_facturado ? parseFloat(productos[i].costo_unitario_facturado) : parseFloat(productos[i].costo_unitario)) * parseFloat(parseInt(productos[i].nro_copias) * parseInt(productos[i].nro_originales));

			return total;
	    };
	});

	app.filter('paginar', function () {
	    return function (_items, args) {
	    	/* El formato seria paginar:'resultadosPorPagina|PaginaActual' */
	    	if (!_items) return null;

	    	var items = [];
	    	var nroResultados = parseInt(args.split('|')[0]);
	    	var actual = parseInt(args.split('|')[1]);

	    	if (nroResultados >= _items.length)
	    		return _items;
	    	
	    	for (var i = actual*nroResultados; i < actual*nroResultados+nroResultados; i++)
	    		items.push(_items[i]);

			return items;
	    };
	});

	app.filter('area', function () {
	    return function (_items, args) {
	    	if (!_items) return null;

	    	var items = [];
	    	
	    	for (var i = 0; i < _items.length; i++) {
	    		var areas = String(_items[i].areas).split(',');
	    		if (areas.contains(args)) {
	    			items.push(_items[i]);
	    		}
	    	}

			return items;
	    };
	});

	app.config(function(toastrConfig, $sceProvider) {
		angular.extend(toastrConfig, {
			autoDismiss: false,
			containerId: 'toast-container',
			closeButton: true,
			closeHtml: '<button>&times;</button>',
			maxOpened: 2,    
			newestOnTop: true,
			positionClass: 'toast-top-right',
			timeOut: 3500,
			extendedTimeOut: 1000,
			tapToDismiss: true,
			progressBar: true,
			preventOpenDuplicates: false,
			target: 'body'
		});

		$sceProvider.enabled(false);
	});
}());