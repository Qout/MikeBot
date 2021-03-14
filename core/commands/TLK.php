<?php

	global $funcs, $Control, $db, $MySql, $ServerControl, $KeyBoard;

/* Информация о команде */
    $name = 'перевести';
    $funcs [$name]['params'] 			 = 0;						// Кол-во параметров
    $funcs [$name]['description'] 		 = "Перевод денег на лк";	// Описание команды
    $funcs [$name]['conversations'] 	 = false; 					// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = false;					// Использовать команду возможно только в беседах. (true: Да / false: Нет)
    $funcs [$name]['hide'] 				 = false; 					// Скрыть команду


/* Работа команды */
    $funcs [$name]['func'] = function (array $info) use ($Control, $db, $MySql, $ServerControl, $KeyBoard): void
    {
		if (($Cfg = Cfg ('tlk')) && $Cfg->enable)
		{
			if ($info [2]['money'] > 0)
			{
				$SteamId = $db->query ("SELECT steamid FROM MikeDb WHERE user_id = '{$info [2]['user_id']}'", 'array:assoc');
				if (empty (@$SteamId [0]['steamid']))
				{
					$Control->printm ("&#127918; Привяжите свой аккаунт Steam к боту, написав команду <<" . prefix . "Стим [Ваш SteamId]>>.\n\n❗ После чего повторите попытку еще раз!");
					return;
				}
				
				$id = -1;
				$sid = 0;
				$rub = (CountArgs() > 0 ? CmdArgs(1) : $info [2]['money']);
				
				if (!is_numeric ($rub))
					$Control->printm ('❗ Ошибка, пожалуйста, укажите нормально рубли.');
				elseif ($rub > $info [2]['money'])
					$Control->printm ("❗ Ошибка, у Вас недостаточно средств.\nㅤㅤ💳 Вы можете перевести: {$info [2]['money']} RUB.");
				elseif ($rub <= 0)
					$Control->printm ("❗ Ошибка, пожалуйста, укажите нормальную сумму перевода.");
				else
				{
					$Colors = ['primary', 'positive', 'negative'];
					$iServers = count ($ServerControl->servers);
					if ($iServers == 0)
					{
						$Control->printm ('❗ Ошибка, никакие сервера к боту не подключены!');
						return;
					}
					
					$jump = -1;
					for ($i = 0; $i < $iServers; $i++)
					{
						if ($i <= $jump)continue;
						
						$btns = [];
						
						for ($x = 0; $x < 3; $x++)
						{
							if ($iServers == ($jump+1))break;
							$jump++;
							$KeyBoard->AddButton (
								@$ServerControl->servers [$jump]['title'],
								['date' => time (), 'func' => 'tlk', 'data' => [$jump, $rub, @$SteamId [0]['steamid']]],
								false,
								$Colors [$x]
							);
						}
						
						if (count ($btns) > 0)
							$Control->printm (($jump <= 2 ? '👑 Выберите сервер на который хотите перевести деньги!' : '&#13;'), '', 0, $KeyBoard->Get (), $i);
					}
				}
			}
			else
				$Control->printm ("❗ Ошибка, перевод в данный момент невозможен.\nВаш баланс: " . $info [2]['money'] . ' руб.');
		}
		else
			$Control->printm ("❗ Пополнение личного кабинета отключено Администратором.");
    };

?>