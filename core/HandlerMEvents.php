<?php

class MEvents
{
    public $info = [];
    public $params = null;
    public $callback = null;
    
    public function Test ()
    {
		return; // Чтобы невозможно было вызвать.
		
		global $Control;
		
		$params = $this->params;
        $Control->printm ('Вы указали параметры: ' . implode (' ', $params));
    }
	
	public function tlk ()
	{
		if ($this->params != null)
		{
			global $Control, $db, $MySql, $ServerControl;
				
			if (($Cfg = Cfg ('tlk')) && $Cfg->enable)
			{
				
				$SteamId = '';
				
				$lk_modify = [
					'lk' => ['cash', 'all_cash'],
					'lk_system' => ['money', 'all_money']
				];
				$lk_tableName = 'lk';
				
				if (!empty (@$this->params [2]))
				{
					$SteamId = @str_replace (["'", '"', 'DELETE', 'FROM', 'SELECT', 'WHERE', '=', '*', ' ', ';'], '', @base64_decode (@$this->params [2]));
					
					@$MySql->Server (@$this->params [0] + 1);
					
					foreach ($lk_modify as $table => $columns)
					{
						if ($MySql->IsTable ($table))
						{
							$lk_tableName = $table;
							break;
						}
					}
					
					$DBInfo = @$MySql->query ("SELECT * FROM {$lk_tableName} WHERE auth = '{$SteamId}';");
				}
				
				if (empty ($SteamId) || $SteamId != @$DBInfo [0]['auth'])
				{
					if (!is_bool ($DBInfo))
						$Control->printm ((empty ($DBInfo) ? "❗ Ошибка, возможно Вы установили неверный SteamId.\n\n" : '') . "&#127918; Привяжите свой аккаунт Steam к боту, написав команду <<" . prefix . "Стим [Ваш SteamId]>>.\n\n💬 После чего повторите попытку еще раз!");
					else
						$Control->printm ('❗ Ошибка, проблемы с подключением к базе данных данного сервера, попробуйте повторить попытку позже!');
					
					return;
				}
				
				$rub = @$this->params [1];
				
				if (is_numeric ($rub))
				{
					if ($rub > $this->info [2]['money'])
						$Control->printm ("❗ Ошибка, у Вас недостаточно средств.\nㅤㅤ💳 Вы можете перевести: {$this->info [2]['money']} RUB.");
					elseif ($rub <= 0)
						$Control->printm ("❗ Ошибка, пожалуйста, укажите нормальную сумму перевода.");
					else
					{
						$TLK = false;
						$Players = $ServerControl->send ('sm_mike_getinfo', @$this->params [0] + 1);
						$IsPlayerInGame = strpos ($Players, "|{$DBInfo [0]['auth']}") > 0;
						
						if (!empty ($Players) 
							&& ($IsPlayerInGame 
							|| $MySql->query ("UPDATE {$lk_tableName} SET {$lk_modify [$lk_tableName][0]} = '" . ($DBInfo [0][$lk_modify [$lk_tableName][0]] + $rub) . "', {$lk_modify [$lk_tableName][1]} = '" . ($DBInfo [0][$lk_modify [$lk_tableName][1]] + $rub) . "' WHERE auth = '{$DBInfo [0]['auth']}';", 'q')))
						{
							if ($IsPlayerInGame)
							{
								$userid = trim (@end (@explode ("\n#", @explode ("|{$DBInfo [0]['auth']}", $Players) [0])));
								
								if ($MySql->query ("UPDATE {$lk_tableName} SET {$lk_modify [$lk_tableName][1]} = '" . ($DBInfo [0][$lk_modify [$lk_tableName][1]] + $rub) . "' WHERE auth = '{$DBInfo [0]['auth']}';", 'q'))
								{
									if (strpos ($ServerControl->send ("sm_lkrub add #{$userid} {$rub}; sm_psay #{$userid} \"Ваш баланс был пополнен на {$rub} RUB\"", @$this->params [0] + 1), 'пополнил счет игроку') > 0
										|| $MySql->query ("UPDATE {$lk_tableName} SET {$lk_modify [$lk_tableName][0]} = '" . ($DBInfo [0][$lk_modify [$lk_tableName][0]] + $rub) . "' WHERE auth = '{$DBInfo [0]['auth']}';", 'q'))
									{
										$db->query ("UPDATE MikeDb SET money = '" . ($this->info [2]['money'] - $rub) . "' WHERE user_id = '{$this->info [2]['user_id']}'", 'q');
										$TLK = true;
									}
								}
							}
							else
							{
								$db->query ("UPDATE MikeDb SET money = '" . ($this->info [2]['money'] - $rub) . "' WHERE user_id = '{$this->info [2]['user_id']}'", 'q');
								$TLK = true;
							}
						}
						else
						{
							$Control->printm ('❗ Произошла неизвестная ошибка.');
							return;
						}
						
						if ($TLK) $Control->printm ("👑 Игроку <<{$DBInfo [0]['name']}>> был пополнен баланс на сумму {$rub} RUB.");
						else $Control->printm ('❗ Ошибка, игрок с таким SteamId не найден. (Возможно Вы не правильно указали свой SteamId (Он должен быть в формате STEAM_X:X:XXXXXXX))');
					}
				}
				else $Control->printm ('❗ Ошибка, пожалуйста, укажите нормально рубли.');
			}
			else $Control->printm ("❗ Пополнение личного кабинета отключено Администратором.");
		}
	}
	
	public function vkadmin ()
	{
		if ($this->params != null)
		{
			global $Control, $db;
			
			if (($data = $db->query ("SELECT fname, lname, user_id FROM MikeDb WHERE steamid = '" . base64_encode ($this->params [1]) . "'", 'array:assoc')))
				$Control->printm ("⭐ Администратор <<{$this->params [0]}>>\nㅤㅤВКонтакте: https://vk.com/id{$data [0]['user_id']}");
			else $Control->printm ('❗ Ошибка, возможно Администратор не привязал свой аккаунт Steam к боту.');
		}
	}
	
	public function shop ()
	{
		if ($this->params != null && count ($this->params) > 0 && is_numeric ($this->params [0]))
		{
			if (($Cfg = Cfg ('shop')) && $Cfg->enable)
			{
				global $Control, $Api, $ServerControl, $KeyBoard;
				
				$btns = [];
				$id = $this->params [0];
				$items = @$Cfg->items [$id + 1];
				
				if (count ($items) > 0)
				{
					$type = count ($this->params);
					
					if ($type >= 2)
					{
						// Получаем или задаем номер страницы
						// И получаем Id элемента на котором остановились (Если не удалось получить номер страницы то Id элемента будет 0)
						$next = 0;
						$list_number = @$this->params [2];
						if (!empty ($list_number) && is_numeric ($list_number))
						{
							$next = @$this->params [3];
							if (empty ($next) || !is_numeric ($next))
								$next = 0;
							
							$list_number++;
						}
						else $list_number = 2;
						
						$items = $items [$this->params [1]];
						$items_count = count ($items);
						$i = 0;
						
						if ($items_count > 0)
						{
							$jump = false;
							foreach ($items as $Name => $Data)
							{
								$i++;
								if ($next >= $i) continue;
								
								$KeyBoard->AddButton (
									'💳 Купить',
									['func' => 'shop_pay', 'data' => [$id, $this->params [1], $Name]],
									false
								);
								
								$attachment = '';
								if (!empty ($Data ['photo']) && file_exists (__IMAGES__ . "/{$Data ['photo']}"))
								{
									// Загрузка фотографии
									if (($Upload = $Api->UploadMessagePhoto (__IMAGES__ . "/{$Data ['photo']}")) && !empty ($Upload))
										$attachment = "photo{$Upload [0]['owner_id']}_{$Upload [0]['id']}";
								}
								
								$Control->printm ('👑 Название: ' . ucfirst (strtolower ($Name)) . ".\n💳 Стоимость: {$Data ['price']} RUB." . (!empty ($Data ['info']) ? "\n\n{$Data ['info']}" : ''), $attachment, 0, $KeyBoard->Get (), ($i + 1));
							
								// Создаем новую страницу предметов
								if ($i == ($next+4) && $items_count > $i)
								{
									$KeyBoard->AddButton (
										"👾 Перейти на {$list_number} страницу",
										['func' => 'shop', 'data' => [$id, $this->params [1], $list_number, $i]],
										false
									);
									
									$Control->printm ("😼 Вы сейчас на ".($list_number - 1)." странице, показать следующую страницу?.", '', 0, $KeyBoard->Get (), ($i + 2));
									
									break;
								}
							}
						}
						else $Control->printm ('❗️ Ошибка, в этой категории пока что ничего не продается.');
					}
					elseif ($type == 1)
					{
						$Colors = ['primary', 'positive', 'negative'];
						if (count ($ServerControl->servers) == 0)
						{
							$Control->printm ('❗ Ошибка, никакие сервера к боту не подключены!');
							return;
						}
						
						$btns = [];
						$glob_i = 0;
						$i = 0;
						
						$iItems = count ($items);
						
						foreach ($items as $Name => $Data)
						{
							$glob_i++;
							$i++;
							
							if ($i >= 1)
							{
								$KeyBoard->AddButton (
									ucfirst (strtolower ($Name)),
									['func' => 'shop', 'data' => [$id, $Name]],
									false,
									$Colors [$i-1]
								);
								
								if ($i == 3 || ($iItems-$glob_i) == 0)$i = 0;
							}
							
							if ($i == 0)
								$Control->printm ((($glob_i > 0 && $glob_i < 4) ? '👑 Выберите категорию вещей.' : '&#13;'), '', 0, $KeyBoard->Get (), $glob_i+1);
						}
					}
				}
				else $Control->printm ('❗️ На сервере <<' . $ServerControl->servers [$id]['title'] . '>> никакие вещи за деньги не продаются.');
			}
			else $Control->printm ('❗️ Магазин был отключен Администратором.');
		}
	}
	
	public function shop_pay ()
	{
		if ($this->params != null && count ($this->params) == 3 && is_numeric ($this->params [0]))
		{
			if (($Cfg = Cfg ('shop')) && $Cfg->enable)
			{
				global $Control, $db, $ServerControl;
				
				$item = @$Cfg->items [$this->params [0] + 1][$this->params [1]][$this->params [2]];
				
				if (!empty ($item))
				{
					$price = $item ['price'];
					
					if ($price > $this->info [2]['money'])
						$Control->printm ("❗ Ошибка, у Вас недостаточно средств.\nㅤㅤ💳 Ваш баланс: {$this->info [2]['money']} RUB.");
					else
					{
						if (empty ($this->info [2]['steamid']))
							$Control->printm ('&#127918; Привяжите свой аккаунт Steam к боту, написав команду <<' . prefix . "Стим [Ваш SteamId]>>.\n\n💬 После чего повторите попытку еще раз!");
						else
						{
							if (empty ($item ['count'])) $item ['count'] = 1;
							
							$buffer   = ' Вы успешно получили вещь {green}' . ucfirst (strtolower ($this->params [2])) . ' {default}в свой инвентарь!';
							$response = $ServerControl->send ("sm_mike_giveitems \"" . @base64_decode ($this->info [2]['steamid']) . "\" \"{$item ['category']}\" \"{$item ['item']}\" \"{$item ['count']}\" \"{$buffer}\";", $this->params [0] + 1);
						
							if ($response == PLAYER_NOT_FOUND)
								$Control->printm ('❗️ Ошибка, пожалуйста, зайдите на сервер чтобы бот смог выдать Вам эту вещь.');
							elseif ($response == ALREADY_IS)
								$Control->printm ('❗️ Ошибка, похоже у Вас уже имеется эта вещь в инвентаре.');
							elseif ($response == OK)
							{
								$db->query ("UPDATE MikeDb SET money = '" . ($this->info [2]['money'] - $price) . "' WHERE user_id = '{$this->info [2]['user_id']}'", 'q');
								$Control->printm ('👑 Вы успешно купили вещь <<' . ucfirst (strtolower ($this->params [2])) . ">>.\n\n👾 Проверьте свой инвентарь!");
							}
							else $Control->printm ('❗ Ошибка, возможно сейчас сервер недоступен.');
						}
					}
				}
				else $Control->printm ("❗️ Ошибка, вещь <<{$this->params [1]}>> не найден.");
			}
			else $Control->printm ('❗️ Магазин был отключен Администратором.');
		}
	}
	
	public function pay ()
	{
		Call('пополнить', $this->info);
	}
	
	public function checkpay ()
	{
		Call('проверить', $this->info);
	}
	
	public function mybalance ()
	{
		Call('баланс', $this->info);
	}
	
	public function mytransf ()
	{
		Call('перевести', $this->info);
	}
	
	public function rep ()
	{
		if ($this->params != null)
		{
			global $Control, $db, $KeyBoard;
			
			$user_id = $this->info [2]['user_id'];
			$Status = $this->params [0];
			$ProfileID = $this->params [1];
			
			if ($ProfileID == $user_id)
			{
				$Control->popup ('Мы знаем что Вы ' . (!$Status ? 'не ' : '') . 'нравитесь самому себе.');
				return;
			}
			
			if (($info = @$db->query ("SELECT likes, dislikes, listrep FROM MikeDb WHERE user_id = '{$ProfileID}'", 'array:assoc') [0]))
			{
				if (StrContains ((string)$info ['listrep'], (string)$user_id))
					$Control->popup ('Вы уже поставили оценку.');
				else
				{
					$Name = $Status ? 'likes' : 'dislikes';
					
					$list = "{$info ['listrep']},{$user_id}";
					$info [$Name]++;
					
					$db->query ("UPDATE MikeDb SET {$Name} = '{$info [$Name]}', listrep = '{$list}' WHERE user_id = '{$ProfileID}'", 'q');
					
					$likes = @$info ['likes'];
					$likes = $likes == '' ? 0 : $likes;
					
					$dislikes = @$info ['dislikes'];
					$dislikes = $dislikes == '' ? 0 : $dislikes;
					
					$getMsg = $Control->getm ();
					$edit = explode ("\n", $getMsg ['message']);
					$edit [3] = "├✨ Репутация: 👍🏻 {$likes} / 👎🏻 {$dislikes}";
					$Control->editm (implode ("\n", $edit), $getMsg ['keyboard']);
					
					$Control->popup ('Вы поставили ' . ($Status ? 'лайк 👍🏻' : 'дизлайк 👎🏻'));
				}
			}
		}
	}
	
	// @Заблокировать Пользователя в Боте
	public function admban ()
	{
		global $Control, $db;
		
		if (IsAdmin)
		{
			$user_id = $this->info [2]['user_id'];
			
			if ($this->params != null)
			{
				$iCountParams = count ($this->params);
				
				if ($iCountParams > 0)
				{
					if (array_key_exists ('message', $this->params))
					{
						switch (@$this->params ['data'][1])
						{
							case 'time': {
								SetMEvents ($user_id, ['func' => 'admban', 'data' => [$this->params ['data'][0], 'reason', $this->params ['message']]]);
								$Control->printm ('💬 Пожалуйста, напишите причину блокировки.');
							}
							break;
							
							case 'reason': {
								$user_id = $this->params ['data'][0];
								$min = $this->params ['data'][2];
								$reason = $this->params ['message'];
								
								$Control->ban ($user_id, $min, $reason);
								$info = @$db->query ("SELECT * FROM MikeDb WHERE user_id = '{$user_id}'", 'array:assoc') [0];
								
								$Control->printm ("👤 Пользователь [id{$user_id}|{$info ['fname']} {$info ['lname']}] был заблокирован на {$min} мин. по причине {$reason}.");
							}
							break;
						}
					}
					elseif (is_numeric ($this->params [0]))
					{
						SetMEvents ($user_id, ['func' => 'admban', 'data' => [$this->params [0], 'time']]);
						$Control->printm ('💬 Пожалуйста, напишите время на которое хотите выдать бан. (В минутах)');
					}
				}
			}
		}
		else $Control->popup ('У Вас недостаточно прав');
	}
	
	public function kickuser ()
	{
		// @$this->params
		if (IsAdmin)
		{
			global $Control, $Api;
			$response = $Api->Inquiry ('messages.removeChatUser', ['chat_id' => @$this->params [0], 'user_id' => @$this->params [1]]);
			$Control->printm ('👨‍💻 ' . ($response == 1 ? 'Пользователь успешно исключен.' : 'Произошла неизвестная ошибка, возможно Пользователь является Создателем беседы.'));
		}
	}
	
    public function cases ()
    {
        global $Control, $db, $KeyBoard;

		if (($Cfg = Cfg ('cases')) && $Cfg->cases_enable)
		{
			$case_name = @$this->params [0];
			
			if (empty ($case_name) || !array_key_exists ($case_name, $Cfg->cases_data))
			{
				$Control->printm ("❗ Ошибка, данный кейс не найден.");
				return;
			}
			
			$case_min 	= $Cfg->cases_data [$case_name]['min'];
			$case_max 	= $Cfg->cases_data [$case_name]['max'];
			$case_price = $Cfg->cases_data [$case_name]['money'];
			
			if (array_key_exists ('opencase', $this->info [2]) && $this->info [2]['opencase'] > time ())
				$Control->printm ("❗ Ошибка, кейсы можно открыть 1 раз в 12 часов.");
			elseif ($this->info [2]['money'] < $case_price)
			{
				$KeyBoard->AddButton (
					'💳 Да, пополнить',
					['func' => 'pay', 'data' => '']
				);
				
				$Control->printm ("❗ Ошибка, у Вас недостаточно средств для открытия данного кейса.\n\n🔥 Хотите пополнить свой баланс?.", '', 0, $KeyBoard->Get ());
			}
			else
			{
				$rub = rand ($case_min, $case_max);
				$balance = $this->info [2]['money'] - $case_price;

				$db->query ("UPDATE MikeDb SET money = '".($balance + $rub)."', opencase = '".(time () + ($Cfg->cases_time == 0 ? -10 : $Cfg->cases_time))."' WHERE user_id = '{$this->info [2]['user_id']}'", 'q');
				
				$Control->printm ((($rub >= $case_price) ? ("👑 Вы выиграли: {$rub} RUB") : ("😿 Вы проиграли: {$rub} RUB")) . '.');
			}
		}
		else
			$Control->printm ("❗ Кейсы были отключены Администратором.");
    }
}