<?php

/* Портируем команду и подключаем класс для отправки сообщений */
	global $funcs, $Api, $Control, $db, $ServerControl;

/* Информация о команде */
    $name = 'доступ';
    $funcs [$name]['params'] 			 = 0;												// Кол-во параметров
    $funcs [$name]['description'] 		 = "Выдает права пользователю на Бан, кик и мут";	// Описание команды
	$funcs [$name]['conversations'] 	 = true; 											// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = false; 											// Использовать команду возможно только в беседах. (true: Да / false: Нет)
    $funcs [$name]['hide'] 				 = true; 											// Скрыть команду


/* Работа команды */
    $funcs [$name]['func'] = function (array $info) use ($Api, $Control, $db, $ServerControl, $name): void
    {
		if (IsAdmin)
		{
			if (count ($info [0]) >= 2)
			{
				if (count ($info [0]) == 2 ? (is_numeric ($info [0][0]) 
					&& ($info [0][1] == '+' ? true : ($info [0][1] == '-' ? true : false))) : (is_numeric ($info [0][0]) 
					&& is_numeric ($info [0][1]) && ($info [0][2] == '+' ? true : ($info [0][2] == '-' ? true : false))))
				{
					$id = (count ($info [0]) == 2
					? (array_key_exists ('reply_message', (array)$info [1]->message) ? $info [1]->message->reply_message->from_id : $info [2]['user_id'])
					: $info [0][0]);
					
					$sid = (count ($info [0]) == 2
					? $info [0][0]
					: $info [0][1]);
					
					$set = (count ($info [0]) == 2
					? $info [0][1]
					: $info [0][2]);
					$set = ($set == '+' ? true : false);
					
					if ($sid > 0 && count ($ServerControl->servers) >= $sid)
					{
						if (($userInfo = $db->query ("SELECT * FROM MikeDb WHERE user_id = '{$id}'", 'array:assoc')))
							$userInfo = $userInfo [0];
						else
						{
							$info = $Api->Inquiry ('users.get', ['user_ids' => $id]);
							
							if (is_array ($info))
							{
								$db->query ("INSERT INTO MikeDb (user_id, fname, lname, MEvents, flood, ban, access, unickey, money, opencase) VALUES('{$id}', '{$info [0]['first_name']}', '{$info [0]['last_name']}', '', '".base64_encode(json_encode (['time' => 0, 'warning' => 0]))."', '".base64_encode(json_encode (['ban' => false, 'time' => 0, 'warning' => 0, 'description' => null]))."', '0', '', '0', '".(time () - 300)."')", 'q');
								
								$userInfo = $db->query ("SELECT * FROM MikeDb WHERE user_id = '{$id}'", 'array:assoc')[0];
							}
							else
							{
								$Control->printm ('❗ Ошибка, Вы делаете слишком много запросов.');
								return;
							}
						}
						
						$checkColumn = array_key_exists (('access_v' . $sid), $userInfo);
						if (!$checkColumn) $db->query ("ALTER TABLE MikeDb ADD COLUMN access_v{$sid} INTEGER DEFAULT 0 NOT NULL", 'q');
						
						if ($set != ($checkColumn ? $userInfo [('access_v' . $sid)] : false))
							$db->query ("UPDATE MikeDb SET access = '".($set ? ($userInfo ['access']+1) : ($userInfo ['access']-1))."' WHERE user_id = '{$id}'", 'q');
						
						$title = $ServerControl->servers [$sid-1]['title'];
						
						$db->query ("UPDATE MikeDb SET access_v{$sid} = '{$set}' WHERE user_id = '{$id}'", 'q');
						
						$Control->printm ("Пользователю [id{$id}|{$userInfo ['fname']} {$userInfo ['lname']}] был " . ($set ? 'выдан' : 'ограничен') . " доступ для администрирования сервером {$title}.");
					}
					else
						$Control->printm ('❗ Ошибка, данный сервер не найден.');
				}
				else
					$Control->printm ("❗ Ошибка, Вы неправильно задали какой-то параметр.");
			}
			else
				$Control->printm ("❗ Ошибка, пожалуйста, укажите 2 или более параметров.\nПример использования: " . prefix . mb_ucfirst ($name) . " [**VK-UserId] [*ServerId] [*+/-].");
		}
		else
			$Control->printm ("❗ Ошибка, у Вас нет доступа к этой команде.");
    };

?>