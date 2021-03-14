<?php

/* Портируем команду и подключаем класс для отправки сообщений */
	global $funcs, $Control, $db, $HTTP;

/* Информация о команде */
    $name = 'стим';
    $funcs [$name]['params'] 			 = 0;								// Кол-во параметров
    $funcs [$name]['description'] 		 = "Связать стим-аккаунт с ботом";	// Описание команды
	$funcs [$name]['conversations'] 	 = false; 							// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = false; 							// Использовать команду возможно только в беседах. (true: Да / false: Нет)
    $funcs [$name]['hide'] 				 = false; 							// Скрыть команду


/* Работа команды */
    $funcs [$name]['func'] = function (array $info) use ($Control, $db, $HTTP, $name): void
    {
		$g_SteamId = $db->query ("SELECT steamid FROM MikeDb WHERE user_id = '{$info [2]['user_id']}'", 'array:assoc');
		
		if (CountArgs() > 0)
		{
			$SteamId = CmdArgs (1);
			
			if ($SteamId != NULL)
			{
				$VarCheck  = 'g_rgProfileData';
				$UrlNumber = '://steamcommunity.com/profiles/';
				$UrlDomain = '://steamcommunity.com/id/';

				if (StrContains($SteamId, 'STEAM_'))
				{
					$SteamId = str_replace (mb_strtoupper ('STEAM_0'), mb_strtoupper ('STEAM_1'), mb_strtoupper ($SteamId));
					$SteamId64 = @__l (SteamId)->Convert ($SteamId);
					if (!StrContains($HTTP->GET ("https{$UrlNumber}{$SteamId64}"), $VarCheck))
						$SteamId = NULL;
				}
				elseif (StrContains($SteamId, $UrlNumber))
				{
					$response = $HTTP->GET ('https' . str_replace(['https', 'http'], '', $SteamId));

					// @Fix slash
					if ($SteamId[strlen($SteamId) - 1] == '/')$SteamId = substr($SteamId, 0, -1);

					$SteamId = ((StrContains($response, $VarCheck) || md5($response) == '7029066c27ac6f5ef18d660d5741979a')
					? @__l (SteamId)->Convert (end (explode ('/', $SteamId)))
					: NULL);
				}
				elseif (StrContains($SteamId, $UrlDomain))
				{
					$response = $HTTP->GET('https' . str_replace(['https', 'http'], '', $SteamId));

					if (StrContains($response, $VarCheck)
						&& ($Number = BEGet($response, ',"steamid":"', '"'))
						&& !empty ($Number))
							$SteamId = __l (SteamId)->Convert ($Number);
					else
						$SteamId = NULL;
				}
				else $SteamId = NULL;
				
				if (!empty($SteamId))
				{
					$SteamId = base64_encode (trim (str_replace (['[', ']'], '', $SteamId)));
					$db->query ("UPDATE MikeDb SET steamid = '{$SteamId}' WHERE user_id = '{$info [2]['user_id']}'", 'q');
				
					$Control->printm ("&#127918; Ваш SteamId успешно привязан к боту.\n\nЧтобы узнать какой SteamId привязан, напишите команду <<" . prefix . mb_ucfirst ($name) . ">>.");
				}
				else $Control->printm ("❗ Ошибка, пожалуйста, укажите Steam нормально. (Прямую ссылку на профиль или SteamID)");
			}
			else
				$Control->printm ("❗ Ошибка, пожалуйста, укажите Ваш SteamId.");
		}
		else
			$Control->printm (($g_SteamId && !empty (trim (@$g_SteamId [0]['steamid']))) ? ('&#127918; Ваш SteamId: ' . base64_decode ($g_SteamId [0]['steamid']) . '.') : "❗ Ошибка, у Вас еще на привязан никакой SteamId.\n\nЧтобы привязать свой Steam к боту, напишите: ". prefix . mb_ucfirst ($name) ." [Ваш SteamId]");
    };

?>