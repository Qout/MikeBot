<?php

function sendError (string $title, string $info): void
{
	file_put_contents ('errors_' . time () . '.txt', "{$title}:\n{$info}\n\n");
}

?>