<?php
	function count_pages($pdfname) 
    {
        $pdftext = file_get_contents($pdfname);
        $num = preg_match_all("/\/Page\W/", $pdftext, $dummy);
        return $num;
    }

	include_once("databasehandler.php");
	$dbHandler = new DatabaseHandler();

    $filePath = "../../soincopy_files/guias/";

    if (isset($_GET['tipo']))
    {
        if ($_GET['tipo'] == "plan")
            $filePath = "../../soincopy_files/planesdeestudio/";
    }

    if ($_GET['action'] == "upload")
    {
    	$ret = array();
    	$completeFilePath = "";
    	$fileName = "";

    	foreach ($_FILES as $f)
    	{
            $fileName = $dbHandler->encrypt($f["name"] . $dbHandler->getTodayDateString() . rand(1,1000000));
            
            // Removemos los caracteres no permitidos del string
            $fileName = str_replace('/', '', $fileName);
            $fileName = str_replace('=', '', $fileName);
            $fileName = str_replace(':', '', $fileName);
            $fileName = str_replace('?', '', $fileName);
            $fileName = str_replace('+', '', $fileName);
            $fileName = str_replace('\'', '', $fileName);
            
            $completeFilePath = $filePath . $fileName . ".pdf";
            
            if (file_exists($completeFilePath)) 
            {
                // Archivo ya existe
                $ret["error"] = array("File already exists. Try uploading again.");
                echo json_encode($ret);
                die();
            } 
            else 
            {
                move_uploaded_file($f["tmp_name"], $completeFilePath);

                // Anadimos la imagen al arreglo para insertar en la BD
                //$images[] = $completeFilePath;
            }
    	}

    	/*$image = new Imagick();
    	$image->pingImage($completeFilePath);*/

    	$ret["success"] = array("1");
    	$ret["pages"] = count_pages($completeFilePath); //$image->getNumberImages();
    	$ret["pdf"] = $fileName . ".pdf";
    	echo json_encode($ret);
    	die();
    }
    else if ($_GET['action'] == "delete")
    {
        try {
            $dbHandler->eliminar_pdf($_POST['file']);
            unlink($filePath . $_POST['file']);
            echo "ok";
        }
        catch (Exception $e)
        {
            echo "error";
        }
        die();
    }
?>