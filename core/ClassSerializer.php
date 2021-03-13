<?php

function __l($var1, $var2 = null)
{
	$_ret = null;
	if (is_string ($var1))
	{
		if (empty ($var2))
			$_ret = unserialize ($var1);
		else
		{
			$_var1 = $var1;
			$_var2 = null;
			
			if (is_object ($var2) || is_array ($var2)) $_var2 = serialize ($var2);
			else if (is_string ($var2)) 			   $_var2 = $var2;
			
			$_ret = !empty ($_var2);
			
			if ($_ret) define ($_var1, $_var2);
		}
	}
	
	return $_ret;
}

?>