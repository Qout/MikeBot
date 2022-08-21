<?php

	global $funcs, $Control, $Api, $ServerControl;

	for ($i = 0; $i < count ($ServerControl->servers); $i++)
	{
		$name = ($i + 1);
		$funcs [$name]['params'] 	 		 = 0;																// Кол-во параметров
		$funcs [$name]['description'] 		 = "Информация о сервере {$ServerControl->servers [$i]['title']}";	// Описание команды
		$funcs [$name]['conversations'] 	 = true; 															// Возможность использовать команду в беседах. (true: Да / false: Нет)
		$funcs [$name]['conversations_only'] = false; 															// Использовать команду возможно только в беседах. (true: Да / false: Нет)
		$funcs [$name]['hide'] 				 = false; 															// Скрыть команду


		$funcs [$name]['func'] = function (array $info) use ($Control, $Api, $ServerControl, $i): void
		{
			$ShowTimeMap = false;			// Cfg Var
			if (($Cfg = Cfg ('show.time.map')))	// If Cfg File Loaded
				$ShowTimeMap = $Cfg->ShowTimeMap;
			
			$ServerId  = $i+1;
			$ArrayData = @explode ("\n", @$ServerControl->send ('sm_mike_getinfo', $ServerId));
			
			$title = trim (@explode ('Name: ', @$ArrayData [0]) [1]);
			if (!empty ($title))
			{
				$VisSteam = false;
				$VisGEO = false;
				$VisIP = false;
				
				if (count ($info [0]) == 1)
				{
					$__find = true;
					$subCommand = strtolower ($info [0][0]);
					
					if ($subCommand == 'steam')
						$VisSteam = true;
					else if (IsAdmin)
					{
						switch ($subCommand)
						{
							case 'geo':
								$VisGEO = true;
							break;
							
							case 'ip':
								$VisIP = true;
							break;
							
							// @No Remove First Param
							default:
								$__find = false;
						}
					}
					else $__find = false;
					
					if ($__find)unset ($info [0][0]);
				}
				
				if (count ($info [0]) == 0)
				{
					$Info 		  = explode ('|', explode ('Info: ', $ArrayData [1]) [1]);
					
					$IpPort 	  = $Info [0];
					
					$MapName 	  = $Info [1];
					$MapTime 	  = '';
					
					if ($ShowTimeMap)
					{
						$MapType 	  = (int)substr ($Info [2], 0, 3);
						$MapTime 	  = substr ($Info [2], 3, strlen($Info [2]));
						$MapParams 	  = explode ('/', $MapTime);
						$MapTime 	  =    $MapType == MAPFLAG_TIME 	  ? "До конца карты: {$MapTime}"
										: ($MapType == MAPFLAG_TIME_LAST  ? "До конца карты: {$MapParams [0]}.\n|ㅤㅤСледующая карта: {$MapParams [1]}"
										: ($MapType == MAPFLAG_ROUND_LAST ? "Последний раунд, следующая карта: {$MapTime}" : ''));
					}
					
					$Players 	  = $Info [3];
					$PlayersMax   = $Info [4];
					
					$PlayersArray = @array_diff(@explode ("\n", trim (@explode ("Players:\n", @implode ("\n", $ArrayData)) [1])), ['']);
					
					$Buttons	  = [];
					$PlayersList  = '';
					$Count 		  = 0;
					
					for ($i = 0; $i < count ($PlayersArray); $i++)
					{
						$PlayerInfo = @explode ('|', $PlayersArray [$i+1]);
						if (count ($PlayerInfo) >= 3)
						{
							$Flags 		= $PlayerInfo [2];
							$Admin 		= ['access' => false, 'ico' => null, 'color' => null];
							
							if ($Flags > 0)
							{
								if ($Flags == ADMFLAG_ROOT)
									$Admin = 
										[
											'access' => true,
											'ico' => '👑',
											'color' => 'negative'
										];
								else if ($Flags == ADMFLAG_MODER)
									$Admin = 
										[
											'access' => true,
											'ico' => '⭐',
											'color' => 'primary'
										];
							}
							
							$NickName 	= NormalString ($PlayersArray [$i]);
							if (empty ($NickName))
								$NickName = "unnamed#{$PlayerInfo [0]}";
							
							if ($Admin ['access'] && count ($Buttons) < 6)$Buttons[] = 
							[
								'action' => [
									'type' 	  => 'callback',
									'label'   => $NickName,
									'payload' => json_encode (['date' => time (), 'func' => 'vkadmin', 'data' => [$NickName, $PlayerInfo [1]]])
								],
								'color' => $Admin ['color']
							];
							
							$aInfo = '';
							if ($VisSteam)
								$aInfo = " 🎮 {$PlayerInfo [1]}";
							else if ($VisGEO)
								$aInfo = ' ' . IpToStr ($PlayerInfo [3]);
							else if ($VisIP)
								$aInfo = ' 🌍 ' . @explode (':', $PlayerInfo [3]) [0];
							
							$PlayersList .= '| ' . (!$Admin ['access'] ? '👨‍💻' : $Admin ['ico']) . ' Игрок ' . ($Count+1) . ". <<{$NickName}>>{$aInfo}\n";
							$Count++;	// +1 Player
						}
						
						$i++;	// Jump To Next Player
					}
					
					$KeyBoard = '';
					if (count ($Buttons) > 0)$KeyBoard = 
					[
						'one_time' => false,
						'inline' => true,
						'buttons' => [count ($Buttons) > 5 ? array_slice ($Buttons, 0, 5) : $Buttons]
					];
					
					// Загрузка фотографии
					$file_photo = __IMAGES__ . "/server-{$ServerId}.jpg";
					$attachment = '';
					if (file_exists ($file_photo) && ($Upload = $Api->UploadMessagePhoto ($file_photo)) && !empty ($Upload))
						$attachment = "photo{$Upload [0]['owner_id']}_{$Upload [0]['id']}";
					
					$Control->printm ("| -- {$title} --\n|\n| ❗ Чтобы подключиться напишите в консоль:\n|ㅤㅤㅤㅤㅤconnect {$IpPort}\n|\n| 👾 Игроков: {$Count} из {$PlayersMax}.\n| 🥀 Карта: {$MapName}." . ($ShowTimeMap ? "\n| 🕓 {$MapTime}." : '') ."\n|\n| 🖥 -- [Список игроков] --\n" . ($Count > 0 ? trim ($PlayersList) : "| Игроков на данный момент нету."), $attachment, 0, $KeyBoard);
					
					if (count ($KeyBoard ['buttons']) > 5)
					{
						$Colors = ['primary', 'positive', 'negative'];
						$Buttons = array_slice ($KeyBoard ['buttons'], 5);
						$iButtons = count ($Buttons);
						
						$jump = -1;
						for ($i = 0; $i < $iButtons; $i++)
						{
							if ($i <= $jump)continue;
							
							$btns = [];
							
							for ($x = 0; $x < 3; $x++)
							{
								if ($iButtons == ($jump+1))break;
								$jump++;
								$btns[] = [$Buttons [$jump]];
							}
							
							if (count ($btns) > 0)
							{
								$KeyBoard = 
								[
									'one_time' => false,
									'inline' => true,
									'buttons' => $btns
								];
								
								$Control->printm ('&#13;', '', 0, $KeyBoard, $i);
							}
						}
					}
				}
				else
				{
					$user_id 		= $info [2]['user_id'];
					
					if ($info [1]->message->peer_id != $user_id)
					{
						$first_name = $info [2]['fname'];
						$last_name 	= $info [2]['lname'];
						
						$message 	= @trim (NormalString (implode (' ', $info [0])));
						if (mb_strlen ($message) > 64)
							$Control->printm ('❗ Ошибка, Вы привысили максимальное кол-во символов на отправку сообщения. Максимум 64 символа.');
						elseif (empty ($message))
							$Control->printm ('❗ Ошибка, пожалуйста, напишите нормальное сообщение.');
						else
						{
							$message 	= "{$first_name} {$last_name} пишет -> {$message}";
						
							$ServerControl->send ("sm_mike_vkmessage \"{$message}\"", $ServerId);
							$Control->printm ("Сообщение на сервер <<{$title}>> успешно отправлено.\n\nСообщение отправил <<[id{$user_id}|{$first_name} {$last_name}]>>.");
						}
					}
					else
						$Control->printm ("❗ Ошибка, отправлять сообщение на сервер можно только через нашу беседу ВКонтакте.");
				}
			}
			else
				$Control->printm ('❗ Ошибка, возможно сейчас сервер недоступен.');
		};
	}

?>