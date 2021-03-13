<?php

class HandlerMessages
{
	public $classes = [];
	
	public function __construct ($classes)
	{
		$this->classes = $classes;
	}
	
    protected function MEvents (string $type, array $info, array $params): void
    {
        $MEvents = $this->classes [5];
        
        if (method_exists($MEvents, $type))
		{
            $MEvents->info = $info;
            $MEvents->params = $params;
            $MEvents->$type();
        }
    }
    
	protected function action (stdclass $Events): void
	{
		if (!$GLOBALS ['EventNotifier'])return;
		
		global $KeyBoard;
		
		$action = $Events->message->action;
		
		switch ($action->type)
		{
			case 'chat_invite_user':{	// Добавили участника.
				// @EVENTS
			    $user_id = $action->member_id;
				
				if ((int)$user_id > 0 && substr ($user_id, 1) != GROUP_ID)
				{
					$KeyBoard->AddButton (
						'👨‍💻 Исключить',
						['func' => 'kickuser', 'data' => [$Events->message->peer_id, $user_id]],
						false
					);
					
					$this->classes [3]->printm ("👑 Добро пожаловать в беседу [id{$user_id}|" . $this->classes [4]->query ("SELECT * FROM MikeDb WHERE user_id = '{$user_id}'", 'array:assoc')[0]['fname'] . "].\n🤖 Я Бот который будет помогать Вам во всем.\n\n💸 Если Вы хотите пополнить свой баланс на любом из наших серверов, просто напишите <<".prefix."Пополнить>> в личные сообщения группы.\n💳 Узнать свой баланс Вы можете с помощью команды <<".prefix."Баланс>>.\n\n📖 Более подробный список всех команд Вы можете получить с помощью команды <<".prefix."Команды>>.\n\n🐾 Желаю Вам хорошо провести время. ^^", '', 0, $KeyBoard->Get ());
				}
			}
			break;
			
			case 'chat_kick_user':{	// Исключили участника.
				// @EVENTS
			    $user_id = $action->member_id;
				
				if (substr ($user_id, 1) != GROUP_ID)
				{
					$_KeyBoard = '';
					if ($user_id == $Events->message->from_id)
					{
						$KeyBoard->AddButton (
							'👨‍💻 Исключить',
							['func' => 'kickuser', 'data' => [$Events->message->peer_id, $user_id]],
							false
						);
						
						$_KeyBoard = $KeyBoard->Get ();
					}
					
					if ((int)$user_id > 0)
						$this->classes [3]->printm ("😿 Пользователь [id{$user_id}|" . $this->classes [4]->query ("SELECT * FROM MikeDb WHERE user_id = '{$user_id}'", 'array:assoc')[0]['fname'] . '] покинул нас.', '', 0, $_KeyBoard);
					else
					{
						$GetGroupInfo = $Api->Inquiry ('groups.getById', ['group_id' => substr ($user_id, 1)]);
						
						if (!is_bool ($GetGroupInfo) && !empty (@$GetGroupInfo [0]['name']))
							$this->classes [3]->printm ("😿 Сообщество [club" . substr ($user_id, 1) . '|' . @$GetGroupInfo [0]['name'] . '] покинуло нас.', '', 0, $_KeyBoard);
						else
							$this->classes [3]->printm ('😿 Сообщество покинуло нас.', '', 0, $_KeyBoard);
					}
				}
			}
			break;
			
			case 'chat_title_update':{	// Изменили заголовок.
				// @EVENTS
				
			}
			break;
			
			case 'chat_photo_update':{	// Обновили фото.
				// @EVENTS
				
			}
			break;
		}
	}
	
	// CallBack
	protected function event ($event, array $info): void
	{
		if ($event == null)return;
		
		$type = $event->func;
		$MEvents = $this->classes [5];
        
        if (!empty ($type) && method_exists($MEvents, $type))
        {
            $MEvents->info = $info;
            $MEvents->params = $event->data;
            $MEvents->$type();
        }
	}
	
	public function Messages (stdclass $Events): void
	{
		// @Обрабатываем события
		switch ($Events->type)
		{
			// @Подключение к группе
			case 'confirmation': {
				echo $this->classes [1]->SAPI ['confirmationToken'];
				return;
			}
			break;
			
			// @Новое сообщение
			case 'message_new': {
				$Events = $Events->object;
				
				// Получаем данные об сообщении
				$user_id = $Events->message->from_id;
				$peer_id = $Events->message->peer_id;
				$replace = '[club' . GROUP_ID . '|';
				$message = trim (str_replace ([$replace . '@' . GROUP_DOMEN . ']', $replace . GROUP_NAME . ']'], '', $Events->message->text));
				$IsChat = ($user_id == $peer_id ? false : true);
				
				// Проверяем, является-ли сообщение событием
				if (!($action = array_key_exists ('action', (array)($Events->message))) && (!($event = array_key_exists ('payload', (array)($Events->message))) || @json_decode (@$Events->message->payload)->command == 'start'))
				{
					// Проверяем, является-ли сообщение пустым
					if (!empty (trim ($message)))
					{
						// @Добавляем Пользователя в бд если его нету в системе бота
						if (is_bool (($userInfo = RegUser ($user_id))))
							$userInfo = $this->classes [4]->query ("SELECT * FROM MikeDb WHERE user_id = '{$user_id}';", 'array:assoc') [0];
						
					    if (!empty($userInfo ['MEvents']))
					    {
							$MEventsInfo = @json_decode (@base64_decode ($userInfo ['MEvents']));
							$this->classes [4]->query ("UPDATE MikeDb SET MEvents = '' WHERE user_id = '{$user_id}'", 'q');
					        $this->MEvents ($MEventsInfo->func, [[], $Events, $userInfo], ['message' => $message, 'data' => $MEventsInfo->data]);
					    }
						
						// Проверяем, является-ли сообщение командой
						elseif ((!$IsChat || @$message [0] == prefix))
						{
							// Проверяем, флудит-ли Пользователь или нет.
							if ((enable_antiflood && CheckFlood ($userInfo)) || $this->classes [3]->isBan ($userInfo, true))
							{
								$this->close ();
								return;
							}
							
							// Проверяем, существует-ли данная команда
							$command = mb_strtolower ((@$message [0] == prefix 
								? substr (explode (' ', $message) [0], 1)
								: explode (' ', $message) [0]));
								
							// Проверяем, существует-ли такая команда
							if (array_key_exists ($command, $this->classes [6]))
							{
								// @Обрабатываем команду
								{
									if (!$this->classes [6][$command]['conversations'] && $peer_id != $user_id)
									{
										$this->classes [3]->printm ('❗ Ошибка, данная команда не работает в беседах.');
										$this->close ();
										return;
									}
									
									if (@$this->classes [6][$command]['conversations_only'] && $peer_id == $user_id)
									{
										$this->classes [3]->printm ('❗ Ошибка, данная команда работает только в беседах.');
										$this->close ();
										return;
									}
									
									// @Обрабатываем команду на кол-во параметров.
									$params = substr ($message, strlen ($command) + (@$message [0] == prefix ? 2 : 1));
									$params = (strlen ($params) > 0 ? GetParams ($params) : []);
									
									if (count ($params) >= $this->classes [6][$command]['params'])
									{
										// @SetParams
										$GLOBALS ['__params'] = $params;
										
										// @Execute command
										Call ($command, [$params, $Events, $userInfo]);
									}
									else
										$this->classes [3]->printm ('❗ Ошибка, укажите еще ' . ($this->classes [6][$command]['params'] - count ($params)) . ' параметр (-а, -ов).');
								}
							}
							elseif (@$message [0] == prefix)
								$this->classes [3]->printm ('❗ Ошибка, данная команда не найдена.');
						}
						
						// @Специальное условие для кнопки <Начать> и команды майк.
						elseif (!$IsChat && (mb_strtolower ($message) == 'начать' || mb_strtolower ($message) == 'майк'))
							Call (mb_strtolower ($message), [[], $Events, $userInfo]);
					}
				}
				elseif ($action)	// Если это событие, обрабатываем его специальной функцией
					$this->action ($Events);
			}
			break;
			
			// @Нажатие на кнопку
			case 'message_event': {
				$Events = $Events->object;
				$user_id = $Events->user_id;
				
				RegUser ($user_id);
				
				if (($userInfo = $this->classes [4]->query ("SELECT * FROM MikeDb WHERE user_id = '{$user_id}'", 'array:assoc')))
				{
					if (array_key_exists ('payload', (array)$Events))	// Если это нажатие на кнопку, обрабатываем его специальной функцией
						@$this->event ($Events->payload, [[], $Events, $userInfo [0]]);
				}
			}
			break;
		}
		
		// @Отключаем и сохраняем все изменения в бд и говорим что всё у нас выполнено гуд!
		$this->close ();
	}
	
	protected function close(): void
	{
		// Отключаем Sqlite3
		$this->classes [4]->close ();
		
		// Отключаемся от всех MySql
		$this->classes [7]->close ();
		
		// Возвращаем успешно
		echo 'ok';
	}
}

?>