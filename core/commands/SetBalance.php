<?php

	global $funcs, $Control, $db;

	$name = 'setmoney';
	$funcs [$name]['params'] 			 = 0;											// Кол-во параметров
	$funcs [$name]['description'] 		 = "Устанавливает или выдает баланс в боте";	// Описание команды
	$funcs [$name]['conversations'] 	 = true; 										// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = false; 										// Использовать команду возможно только в беседах. (true: Да / false: Нет)
	$funcs [$name]['hide'] 				 = true; 										// Скрыть команду


	$funcs [$name]['func'] = function (array $info) use ($Control, $db, $name): void
	{
		if (IsAdmin)
		{
			$iCountParams = count ($info [0]);
			$bDefId = true;
			$giveout = 0;
			
			$user_id = (array_key_exists ('reply_message', (array)$info [1]->message) ? $info [1]->message->reply_message->from_id : $info [2]['user_id']);
			$money   = $info [2]['money'];
			
			
			if ($iCountParams > 0)
			{
				$iBuffer = $info [0][0];
				
				if (is_numeric ($iBuffer))
				{
					$IsAdd   = @$iBuffer [0] == '+';
					$giveout = ($IsAdd ? @substr ($iBuffer, 1, strlen ($iBuffer)) : $iBuffer);
					$money   = ($IsAdd ? $money : 0) + $giveout;
				}
				
				// @Обрабатываем 2 параметр
				if (count ($info [0]) >= 2)
				{
					$iBuffer = $info [0][1];
					if (is_numeric ($iBuffer))$user_id = $iBuffer;
				}
				
				$bDefId = $user_id == $info [2]['user_id'];
				
				if ($bDefId || ($info = $db->query ("SELECT * FROM MikeDb WHERE user_id = '{$user_id}'", 'array:assoc')))
				{
					$db->query ("UPDATE MikeDb SET money = '{$money}' WHERE user_id = '{$user_id}'", 'q');
					$Control->printm ("Пользователю [id{$user_id}|". ($info [$bDefId*2]['fname'] . ' ' . $info [$bDefId*2]['lname']) ."] было выдано {$giveout} руб.");
				}
				else $Control->printm ("❗ Ошибка, данный Пользователь не найден в базе данных.");
			}
			else $Control->printm ("❗ Ошибка, пожалуйста, укажите 1 или более параметров.\nПример использования: " . prefix . mb_ucfirst ($name) . " [*Balance] [UserId].");
		}
		else
			$Control->printm ("❗ Ошибка, у Вас недостаточно прав для использования данной команды.");
	};
?>