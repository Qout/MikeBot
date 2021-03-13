<?php

/* Портируем команду и подключаем класс для отправки сообщений */
	global $funcs, $Control, $db;

/* Информация о команде */
    $name = 'стим';
    $funcs [$name]['params'] 			 = 0;								// Кол-во параметров
    $funcs [$name]['description'] 		 = "Связать стим-аккаунт с ботом";	// Описание команды
	$funcs [$name]['conversations'] 	 = false; 							// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = false; 							// Использовать команду возможно только в беседах. (true: Да / false: Нет)
    $funcs [$name]['hide'] 				 = false; 							// Скрыть команду


/* Работа команды */
    $funcs [$name]['func'] = function (array $info) use ($Control, $db, $name): void
    {
		$g_SteamId = $db->query ("SELECT steamid FROM MikeDb WHERE user_id = '{$info [2]['user_id']}'", 'array:assoc');
		
		if (count ($info [0]) > 0)
		{
			$SteamId = $info [0][0];
			
			if (count (explode (mb_strtolower ('STEAM_'), mb_strtolower ($SteamId))) > 1)
			{
				$SteamId = base64_encode (trim (str_replace (['[', ']'], '', str_replace (mb_strtoupper ('STEAM_0'), mb_strtoupper ('STEAM_1'), mb_strtoupper ($SteamId)))));
				$db->query ("UPDATE MikeDb SET steamid = '{$SteamId}' WHERE user_id = '{$info [2]['user_id']}'", 'q');
				
				$Control->printm ("&#127918; Ваш SteamId успешно привязан к боту.\n\nЧтобы узнать какой SteamId привязан, напишите команду <<" . prefix . mb_ucfirst ($name) . ">>.");
			}
			else
				$Control->printm ("❗ Ошибка, пожалуйста, укажите Ваш SteamId.");
		}
		else
			$Control->printm (($g_SteamId && !empty (trim (@$g_SteamId [0]['steamid']))) ? ('&#127918; Ваш SteamId: ' . base64_decode ($g_SteamId [0]['steamid']) . '.') : "❗ Ошибка, у Вас еще на привязан никакой SteamId.\n\nЧтобы привязать свой Steam к боту, напишите: ".prefix."стим [Ваш SteamId]");
    };

?>