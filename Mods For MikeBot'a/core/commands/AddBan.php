<?php

/* Портируем команду и подключаем класс для отправки сообщений */
	global $funcs, $Control, $ServerControl;

/* Информация о команде */
    $name = 'банлист';
    $funcs [$name]['params'] 			 = 0;					// Кол-во параметров
    $funcs [$name]['description'] 		 = "Добавляет SteamId в блоклист";	// Описание команды
	$funcs [$name]['conversations'] 	 = true; 				// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = true; 				// Использовать команду возможно только в беседах. (true: Да / false: Нет)
    $funcs [$name]['hide'] 				 = true; 				// Скрыть команду


/* Работа команды */
    $funcs [$name]['func'] = function (array $info) use ($Control, $ServerControl): void
    {
		if (IsAdmin || (array_key_exists ('access', $info [2]) && $info [2]['access'] > 0))
		{
			if (count ($info [0]) >= 2)
			{
				$addban = false;
				$errors = 0;
				
				for ($i = 0; $i < count ($ServerControl->servers); $i++)
				{
					$SteamId = $info [0][0];
					$Time = $info [0][1];
					
					if (StrContains($SteamId, 'STEAM'))
					{
						unset ($info [0][0], $info [0][1]);
						$reason = trim (implode (' ', $info [0]));
						
						if (empty ($reason))
							$reason = 'Причина не указана';
						
						if (IsAdmin || (array_key_exists (('access_v' . ($i+1)), $info [2]) ? $info [2]['access_v' . ($i+1)] : false))
						{
							$addban = true;
							$ServerControl->send ("sm_addban \"{$SteamId}\" {$Time} {$reason}", ($i+1));
							
							$Control->printm ("Игрок под SteamId: <<{$SteamId}>> был наказан на <<{$Time}>> минут, по причине: <<{$reason}>>.\n\nВыдал наказание: <<[id" . $info [2]['user_id'] . '|' . $info [2]['fname'] . ' ' . $info [2]['lname'] . ']>>.');
							break;
						}
						else
						{
							$Control->printm ("❗ Ошибка, у Вас нету доступа для администрирования сервером " . $ServerControl->servers [$i]['title'] . '.');
							return;
						}
					}
					else
					{
						$Control->printm ('❗ Ошибка, Вы указали неверный SteamId.');
						return;
					}
				}
				
				if (!$addban)$Control->printm ('❗ Ошибка, возможно Вы не подключили сервера.');
			}
			else
				$Control->printm ("❗ Ошибка, пожалуйста, укажите 2 или более параметров.\nПример использования: " . prefix . mb_ucfirst ($name) . " [*SteamId] [*Time] [Reason].");
		}
		else
			$Control->printm ('❗ Ошибка, у Вас нету доступа к этой команде.');
    };

?>