<?php

/* Портируем команду и подключаем класс для отправки сообщений */
	global $funcs, $Control, $ServerControl;

/* Информация о команде */
    $name = 'giveitem';
    $funcs [$name]['params'] 			 = 0;					// Кол-во параметров
    $funcs [$name]['description'] 		 = "Добавляет SteamId в блоклист";	// Описание команды
	$funcs [$name]['conversations'] 	 = true; 				// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = false; 				// Использовать команду возможно только в беседах. (true: Да / false: Нет)
    $funcs [$name]['hide'] 				 = true; 				// Скрыть команду


/* Работа команды */
    $funcs [$name]['func'] = function (array $info) use ($Control, $ServerControl, $name): void
    {
		if (IsAdmin)
		{
			$SteamId = @base64_decode ($info [2]['steamid']);
			$iParams = CountArgs();
			
			if ($iParams == 2)
			{
				$Category = CmdArgs(1);
				$ItemName = CmdArgs(2);
				
				if (empty ($SteamId))
				{
					$Control->printm ("&#127918; Привяжите свой аккаунт Steam к боту, написав команду <<" . prefix . "Стим [Ваш SteamId]>>.\n\n❗ После чего повторите попытку еще раз!");
					return;
				}
			}
			elseif ($iParams > 2)
			{
				$SteamId  = CmdArgs(1);
				$Category = CmdArgs(2);
				$ItemName = CmdArgs(3);
			}
			else
			{
				$Control->printm ("❗ Ошибка, пожалуйста, укажите 2 или более параметров.\nПример использования: " . prefix . mb_ucfirst ($name) . " [**SteamId] [*Category] [*ItemName].");
				return;
			}
			
			$buffer   = ' {green}Вам выдали какую-то вещь в инвентарь.';
			
			$errors = 0;
			for ($i = 0; $i < count ($ServerControl->servers); $i++)
			{
				switch ($ServerControl->send ("sm_mike_giveitems \"{$SteamId}\" \"{$Category}\" \"{$ItemName}\" \"1\" \"{$buffer}\";", ($i+1)))
				{
					case OK:
						$Control->printm ("👑 Вы успешно выдали вещь.");
						return;
					break;
					
					case ALREADY_IS:
						$Control->printm ('❗️ Ошибка, похоже у игрока уже имеется эта вещь в инвентаре.');
						return;
					break;
					
					case PLAYER_NOT_FOUND:
						// Player not found;
					break;
					
					default:
						$errors++;
					break;
				}
			}
			
			$Control->printm ('❗️ Ошибка, игрока нету на сервере' . ($errors >= 1 ? ' или сервер на котором играет игрок сейчас недоступен' : '') . '.');
		}
		else
			$Control->printm ('❗ Ошибка, у Вас нету доступа к этой команде.');
    };

?>