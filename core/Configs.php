<?php

function Cfg ($filename)
{
	$cfg_file = __CFG__ . "/{$filename}.php";
	$Loaded = false;
	if (file_exists ($cfg_file) && !empty (file_get_contents ($cfg_file)))
		$Loaded = (object)require $cfg_file;
	
	return $Loaded;
}

?>