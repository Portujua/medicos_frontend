<!DOCTYPE html>
<html ng-app="medicos">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible"/>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta charset="utf-8">
		<link id="favicon" rel="shortcut icon" href="img/logo.png">
		<meta http-equiv='cleartype' content='on'>

		<!--<base href="/soincopy/">-->

		<meta name="description" content="">
		<meta name='keywords' content=''>
		<meta name='copyright' content=''>
		<meta name='language' content='ES'>
		<meta name='robots' content=''>
		<meta name='Classification' content='Business'>
		<meta name='author' content='Eduardo Lorenzo, ejlorenzo19@gmail.com'>
		<meta name='rating' content='General'>
		<meta name='revisit-after' content='7 days'>
		<meta name='subtitle' content=''>
		<meta name='target' content='all'>

		<title id="website_title">Médicos</title>

		<?php
			$folder_includes = array('./css/');//, './js/lib/first/', './js/', './js/lib/', './js/services/', './js/directives/', './js/controllers/');

			foreach ($folder_includes as $folder)
			{
				$files = array_diff(scandir($folder), array('.', '..'));

				echo '<!-- '.strtoupper(str_replace(array('.', '/'), '', $folder)).' Includes -->';

				foreach ($files as $f)
				{
					$ext = explode('.', $f);
					$ext = $ext[count($ext) - 1];

					$path = $folder . $f;

					if ($ext == 'css')
						echo '<link rel="stylesheet" type="text/css" href="'.$path.'" />';
					elseif ($ext == 'js')
						echo '<script type="text/javascript" src="'.$path.'"></script>';
				}

				echo '<!-- End '.strtoupper(str_replace(array('.', '/'), '', $folder)).' Includes -->';
			}
		?>

		<!-- jQuery -->
		<script type="text/javascript" src="js/lib/jquery-2.1.1.min.js"></script>

		<!-- underscore -->
		<script type="text/javascript" src="js/lib/underscore-min.js"></script>

		<!-- Bootstrap -->
		<script type="text/javascript" src="js/lib/bootstrap.js"></script>

		<!-- jQuery Confirm -->
		<script type="text/javascript" src="js/lib/jquery-confirm.min.js"></script>

		<!-- jQuery UI -->
		<script type="text/javascript" src="js/lib/jquery-ui-1.9.2.custom.min.js"></script>

		<!-- AngularJS -->		
		<script type="text/javascript" src="js/lib/angular.js"></script>
		<script type="text/javascript" src="js/lib/angular-route.js"></script>
		<script type="text/javascript" src="js/app-config.js"></script>
		<script type="text/javascript" src="js/route-config.js"></script>
		<script type="text/javascript" src="js/interceptors.js"></script>
		<script type="text/javascript" src="js/run.js"></script>
		<script type="text/javascript" src="js/lib/angular-filter-min.js"></script>
		<script type="text/javascript" src="js/lib/angular-animate.min.js"></script>
		<script type="text/javascript" src="js/angular-file-upload.min.js"></script>

		<!-- Services -->
		<script type="text/javascript" src="js/services/RESTService.js"></script>
		<script type="text/javascript" src="js/services/AlertService.js"></script>
		<script type="text/javascript" src="js/services/LoginService.js"></script>

		<!-- Directives -->
		<script type="text/javascript" src="js/directives/UniqueBD.js"></script>
		<script type="text/javascript" src="js/directives/ts-select-fix.js"></script>
		<script type="text/javascript" src="js/directives/ExistsBD.js"></script>

		<!-- Angular Toastr -->
		<link rel="stylesheet" type="text/css" href="css/angular-toastr.min.css" />
		<script type="text/javascript" src="js/lib/angular-toastr.min.js"></script>
		<script type="text/javascript" src="js/lib/angular-toastr.tpls.min.js"></script>

		<!-- Controllers -->
		<script type="text/javascript" src="js/controllers/MainController.js"></script>
		<script type="text/javascript" src="js/controllers/Paciente.js"></script>
		<script type="text/javascript" src="js/controllers/Perfil.js"></script>


		<!-- Angular Loading Bar -->
		<script type="text/javascript" src="js/lib/loading-bar.js"></script>

		<!-- Angular Storage -->
		<script type="text/javascript" src="js/lib/ngStorage.min.js"></script>

		<!-- Boostrap Select -->
		<script type="text/javascript" src="js/lib/bootstrap-select.js"></script>

		<!-- Angular datepicker -->
		<script type="text/javascript" src="js/lib/bootstrap-datepicker.js"></script>

		<!-- Angular upload -->
		<script type="text/javascript" src="js/lib/angular-upload.js"></script>



		<script type="text/javascript" src="js/utils.js"></script>


		<!-- Chat directive -->
		<script type="text/javascript" src="js/directives/chat/chat.directive.js"></script>
		<script type="text/javascript" src="js/directives/chat/chat.controller.js"></script>

		<link rel="stylesheet" href="css/estiloAdmin.css">
	</head>

	<body ng-controller="MainController">
		<div ng-include="'views/navbar.html'" ng-if="!session.isLoggedIn()"></div>
		<div ng-include="'views/sidebar.html'" ng-if="session.isLoggedIn()"></div>

		<div ng-view></div>

		<div ng-include="'views/footer.html'" ng-if="!session.isLoggedIn()"></div>
	</body>
</html>