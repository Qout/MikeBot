<?php

/* Портируем команду и подключаем класс для отправки сообщений */
	global $funcs, $Control, $ServerControl;

/* Информация о команде */
    $name = 'бан';
    $funcs [$name]['params'] 			 = 0;					// Кол-во параметров
    $funcs [$name]['description'] 		 = "Выдает бан игроку";	// Описание команды
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
				$ban = false;
				$errors = 0;
				
				for ($i = 0; $i < count ($ServerControl->servers); $i++)
				{
					$_server = $ServerControl->send ('status', ($i+1));
					
					if (!empty ($_server))
					{
						foreach (explode ("\n", explode ('# userid', $_server) [1]) as $key => $value)
						{
							if (strpos (mb_strtolower ($value), mb_strtolower ('STEAM')) > 0
								&& strpos (mb_strtolower ($value), mb_strtolower ($info [0][0])) > 0)
							{
								$name = trim (explode ('" STEAM_1:', explode (' "', $value) [1]) [0]);
								$userid = explode (' ', $value) [1];
								$time = str_replace (['пермач', 'навсегда'], 0, $info [0][1]);
								
								unset ($info [0][0], $info [0][1]);
								$reason = trim (implode (' ', $info [0]));
								
								if (empty ($reason))
									$reason = 'Причина не указана';
								
								if (IsAdmin || (array_key_exists (('access_v' . ($i+1)), $info [2]) ? $info [2]['access_v' . ($i+1)] : false))
								{
									$ban = true;
									$ServerControl->send ("sm_ban #{$userid} {$time} {$reason}", ($i+1));
									
									$Control->printm ("Игрок: <<{$name}>> был наказан на <<{$time}>> минут, по причине: <<{$reason}>>.\n\nВыдал наказание: <<[id" . $info [2]['user_id'] . '|' . $info [2]['fname'] . ' ' . $info [2]['lname'] . ']>>.');
									break 2;
								}
								else
								{
									$Control->printm ("❗ Ошибка, у Вас нету доступа для администрирования сервером " . $ServerControl->servers [$i]['title'] . '.');
									break 2;
								}
							}
						}
					}
					else
						$errors++;
				}
				
				if ($errors == count ($ServerControl->servers))
					$Control->printm ('❗ Ошибка, все сервера недоступны.');
				elseif (!$ban && $errors == 0)
					$Control->printm ('❗ Ошибка, игрок не найден.');
				elseif (!$ban && $errors > 0)
					$Control->printm ('❗ Ошибка, возможно сервер на котором играет игрок в данный момент недоступен.');
			}
			else
				$Control->printm ("❗ Ошибка, пожалуйста, укажите 2 или более параметров.\nПример использования: " . prefix . mb_ucfirst ($name) . " [*Name/UserId (only int)/SteamId] [*Time] [Reason].");
		}
		else
			$Control->printm ('❗ Ошибка, у Вас нету доступа к этой команде.');
    };

?>