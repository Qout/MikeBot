<?php

	global $funcs, $Control, $ServerControl;

	$name = strtolower (basename(__FILE__, '.php'));
	$funcs [$name]['params'] 			 = 0;										// Кол-во параметров
	$funcs [$name]['description'] 		 = "Выполнение PHP-кода внутри бота";	// Описание команды
	$funcs [$name]['conversations'] 	 = true; 									// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = false; 									// Использовать команду возможно только в беседах. (true: Да / false: Нет)
	$funcs [$name]['hide'] 				 = true; 									// Скрыть команду


	$funcs [$name]['func'] = function (array $info) use ($Control, $ServerControl, $name): void
	{
		if (IsAdmin)
		{
			if (count ($info [0]) >= 1)
			{
				$params = explode (' ', $info [1]->message->text);
				unset ($params [0]);
				
				$call = '';
				$Code = implode (' ', $params);
				@eval ("\$call = function (array \$info) use (\$Control, \$ServerControl, \$name){\n{$Code}\n};");
				
				if (!empty ($call))
				{
					$Control->printm ($call ($info));
					$Control->printm ('✅ Код был успешно выполнен!');
				}
				else
					$Control->printm ("❗ Произошла ошибка выполнения.");
			}
			else $Control->printm ("❗ Ошибка, пожалуйста, укажите 1 или более параметров.\nПример использования: " . prefix . mb_ucfirst ($name) . " [*Code].");
		}
		else $Control->printm ("❗ Ошибка, у Вас недостаточно прав для использования данной команды.");
	};
?>