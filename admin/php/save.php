<?php
	@session_start();
	
	if (count($_POST) > 0)
		foreach ($_POST as $k => $v)
			$_SESSION[$k] = $v;
?>