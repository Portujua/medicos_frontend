<?php
	if (!isset($_GET['token'])) {
		echo "Acceso denegado.";
		die();
	}

	if (strlen($_GET['token']) != 128) {
		echo "Token no válido.<br>Motivo: #1";
		die();
	}

	require_once("databasehandler.php");
	$dbh = new DatabaseHandler();

	if ($dbh->validarEmail($_GET['token'])) {
		echo "Email validado con éxito";
		die();
	}
	else {
		echo "Token no válido.<br>Motivo: #2";
		die();
	}
?>