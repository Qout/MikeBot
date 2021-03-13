<?php

	global $funcs, $Control, $ServerControl;

	$name = strtolower (basename(__FILE__, '.php'));
	$funcs [$name]['params'] 			 = 0;										// Кол-во параметров
	$funcs [$name]['description'] 		 = "Выполнение Rcon-команд на серверах";	// Описание команды
	$funcs [$name]['conversations'] 	 = true; 									// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = false; 									// Использовать команду возможно только в беседах. (true: Да / false: Нет)
	$funcs [$name]['hide'] 				 = true; 									// Скрыть команду


	$funcs [$name]['func'] = function (array $info) use ($Control, $ServerControl, $name): void
	{
		if (IsAdmin)
		{
			if (count ($info [0]) >= 2)
			{
				$ServerId = $info [0][0];
				
				if (is_numeric ($ServerId))
				{
					$command = explode (' ', $info [1]->message->text);
					unset ($command [0], $command [1]);
					
					$command = implode (' ', $command);
					
					$response = @$ServerControl->send (trim ($command), $ServerId);
					if (!empty ($response))
					{
						$Control->printm (print_r ($response, true));
						$Control->printm ('✅ Команда выполнена успешно!');
					}
					else
						$Control->printm ('❗ Ошибка, возможно сейчас сервер недоступен.');
				}
				else $Control->printm ("❗ Ошибка, пожалуйста, укажите нормально Id-сервера.");
			}
			else $Control->printm ("❗ Ошибка, пожалуйста, укажите 2 или более параметров.\nПример использования: " . prefix . mb_ucfirst ($name) . " [*ServerId] [*Command].");
		}
		else $Control->printm ("❗ Ошибка, у Вас недостаточно прав для использования данной команды.");
	};
?>