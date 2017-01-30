<?php
	require_once("databasehandler.php");
	$dbh = new DatabaseHandler();

	$dbh->actualizar_hora_sesion();

	$send = count($_GET) > 1 ? $_GET : $_POST;

	$fn = strval($_GET['fn']);
	echo $dbh->$fn($send);
?>