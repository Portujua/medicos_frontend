<?php
	include_once("databasehandler.php");
	$dbh = new DatabaseHandler();

	if ($dbh->session_expired())
		echo "1";
	else
		echo "0";
?>