<?php

	global $funcs, $Control, $KeyBoard, $db;

/* Информация о команде */
    $name = 'профиль';
    $funcs [$name]['params'] 			 = 0;					// Кол-во параметров
    $funcs [$name]['description'] 		 = "Показывает ваш профиль";	// Описание команды
	$funcs [$name]['conversations'] 	 = true; 				// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = false; 				// Использовать команду возможно только в беседах. (true: Да / false: Нет)
    $funcs [$name]['hide'] 				 = false; 				// Скрыть команду


/* Работа команды */
    $funcs [$name]['func'] = function (array $info) use ($Control, $KeyBoard, $db): void
    {
		$iCountParams = count ($info [0]);
		$user_id = (array_key_exists ('reply_message', (array)$info [1]->message) ? $info [1]->message->reply_message->from_id : $info [2]['user_id']);
		$balance = 0;
		
		if ($iCountParams > 0)
		{
			$iBuffer = $info [0][0];
			if (is_numeric ($iBuffer))$user_id = $iBuffer;
		}
		
		$UserInfo = @$db->query ("SELECT * FROM MikeDb WHERE user_id = '{$user_id}'", 'array:assoc') [0];
		if ($UserInfo)
		{
			$IsBanBot = $Control->IsBan ($UserInfo);
			
			$likes = @$UserInfo ['likes'];
			$likes = $likes == '' ? 0 : $likes;
			
			$dislikes = @$UserInfo ['dislikes'];
			$dislikes = $dislikes == '' ? 0 : $dislikes;
			
			$msg = [
			
				"┌👤 Профиль: [id{$user_id}|{$UserInfo ['fname']} {$UserInfo ['lname']}].",
				"├&#127380;: {$user_id}.",
				("├&#127918; SteamId: " . (!empty (trim (@$UserInfo ['steamid'])) ? @base64_decode ($UserInfo ['steamid']) : 'Steam не привязан')),
				"├✨ Репутация: 👍🏻 {$likes} / 👎🏻 {$dislikes}",
				"├&#128179; Баланс: {$UserInfo ['money']} руб.",
				("└⛔ Блокировка в боте: " . ($IsBanBot ? 'Да' : 'Нет') . '.')
			
			];
			
			$KeyBoard->AddButton (
				'👍🏻',
				['func' => 'rep', 'data' => [true, $user_id]],
				false,
				'positive'
			);
			
			$KeyBoard->AddButton (
				'👎🏻',
				['func' => 'rep', 'data' => [false, $user_id]],
				true,
				'negative'
			);
			
			if (IsAdmin && $user_id != $info [2]['user_id'])
			{
				$KeyBoard->AddButton (
					'Заблокировать',
					['func' => 'admban', 'data' => [$user_id]],
					false,
					'negative'
				);
			}
			
			$Control->printm (implode ("\n", $msg));
		}
		else $Control->printm ("❗ Ошибка, данный Пользователь не найден в базе данных.");
    };

?>