<?php
	require_once("databasehandler.php");
	$dbh = new DatabaseHandler();

	$dbh->actualizar_hora_sesion();

	parse_str(file_get_contents("php://input"), $send); 

	if (count($_GET) > 1) {
		$send = $_GET;
	}

	$fn = strtolower($_SERVER['REQUEST_METHOD'])."_".strval($_GET['fn']);
	echo $dbh->$fn($send);
?>