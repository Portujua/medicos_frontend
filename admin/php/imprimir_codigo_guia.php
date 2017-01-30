<?php
	if (!isset($_GET['codigo'])) 
		die();

	include_once("databasehandler.php");
	$dbh = new DatabaseHandler();
	$guia = json_decode($dbh->cargar_guia(array("codigo" => $_GET['codigo'])), true);
?>

<style type="text/css">
	@media print {
		a {
			display: none;
		}
	}
</style>

<h2>Código: <?php echo $guia['codigo']; ?></h2>
<h3>Título: <?php echo $guia['titulo']; ?></h3>
<p>Fecha: <?php echo $guia['fecha']; ?></p>
<p>Profesor: <?php echo $guia['profesor']['nombre_completo']; ?></p>

<a href="" onclick="window.print();">Imprimir</a> <a href="../#/buscarguias">Volver atrás</a>