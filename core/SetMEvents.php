<?php

function SetMEvents (int $user_id, array $data): void
{
	$GLOBALS ['db']->query ("UPDATE MikeDb SET MEvents = '". base64_encode (json_encode ($data)) ."' WHERE user_id = '{$user_id}'", 'q');
}

?>