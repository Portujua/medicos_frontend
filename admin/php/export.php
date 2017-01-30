<?php
	require_once("databasehandler.php");
	$dbh = new DatabaseHandler();

	if (!isset($_GET['t']))
		die();

	function array_to_csv_download($array, $filename = "export.csv", $delimiter=";")
    {
        header('Content-Type: application/csv; charset=utf-8');
        header('Content-Disposition: attachement; filename="'.$filename.'";');

        $f = fopen('php://output', 'w');

        foreach ($array as $line) {
            fputcsv($f, $line, $delimiter);
        }
    }

    $t = strval($_GET['t']);
    $fn = "csv_" . $t;
    array_to_csv_download($dbh->$fn(), $t . ".csv");
?>