<?php

	global $funcs, $Control, $db;

/* Информация о команде */
    $name = 'баланс';
    $funcs [$name]['params'] 			 = 0;					// Кол-во параметров
    $funcs [$name]['description'] 		 = "Показывает баланс";	// Описание команды
	$funcs [$name]['conversations'] 	 = true; 				// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = false; 				// Использовать команду возможно только в беседах. (true: Да / false: Нет)
    $funcs [$name]['hide'] 				 = false; 				// Скрыть команду


/* Работа команды */
    $funcs [$name]['func'] = function (array $info) use ($Control, $db): void
    {
		$Control->printm ('&#128179; Ваш баланс в текущий момент: ' . $info [2]['money'] . ' руб.');
    };

?>