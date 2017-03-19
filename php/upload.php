<?php
	if ( !empty( $_FILES ) ) 
	{
			include_once("utils.php");
	    $tempPath = $_FILES[ 'file' ][ 'tmp_name' ];
	    $uploadSemiPath = 'chat_images/' . rand(1, 100000) . getToken() . ".image";
	    $uploadPath = '../' . $uploadSemiPath;
	    move_uploaded_file( $tempPath, $uploadPath );
	    $answer = array( 'answer' => 'File transfer completed', 'ok' => true );
	    $json = json_encode( $answer );
	    print_r($_GET);

	    include_once("databasehandler.php");
	    $dbh = new DatabaseHandler();

	    $data = array(
	    	"paciente" => $_GET['paciente'],
        "medico" => $_GET['medico'],
        "mensaje" => "<a href='".$uploadSemiPath."' target='_blank'><img src='" . $uploadSemiPath . "' /></a>",
        "owner" => $_GET['owner'],
        "owner_name" => $_GET['owner_name']
	    );

	    $dbh->adjuntar_imagen($data);
	} 
	else 
	{
		$answer = array( 'answer' => 'No files', 'error' => true );
	    $json = json_encode( $answer );
	}

	echo $json;
?>