<?php

function Call (string $name, $info = null)
{
	if (array_key_exists ($name, @$GLOBALS ['funcs']))
		call_user_func (@$GLOBALS ['funcs'][$name]['func'], $info);
}

?>