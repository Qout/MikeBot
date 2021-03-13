<?php

function CreateCode (int $uid = 1, int $length = 6, bool $string = true)
{
	// @Check Errors
	if ($uid <= 0
		|| $length < 4
		|| $length > ($string ? 32 : 6))return null;
	
	// @Secret Key for Generation
	$x = hash ('sha256', $uid + $length * ((int)$string+1));
	
	// @Microtime
	$microtime = substr((string)microtime(), 2, 8);
	
	
	// @Result
	return substr (($string
		? hash ('sha256',  @($x ^ "{$uid}-{$microtime}"))
		: @($x ^ ($uid * $microtime))
	), 0, $length);
}

?>