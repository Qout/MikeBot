<?php

	global $funcs, $Control, $db, $HTTP, $AdminId, $KeyBoard;

/* Информация о команде */
    $name = 'проверить';
    $funcs [$name]['params'] 		= 0;					// Кол-во параметров
    $funcs [$name]['description'] 	= "Проверить оплату";	// Описание команды
	$funcs [$name]['conversations'] = false; 				// Возможность использовать команду в беседах. (true: Да / false: Нет)
    $funcs [$name]['hide'] 			= false; 				// Скрыть команду


/* Работа команды */
    $funcs [$name]['func'] = function (array $info) use ($Control, $db, $HTTP, $AdminId, $KeyBoard): void
    {
		if (($Cfg = Cfg ('qiwi.pays')))
		{
			if (!empty ($info [2]['unickey']))
			{
				$KeyBoard->AddButton (
					'💳 Мой Баланс',
					['func' => 'mybalance', 'data' => ''],
					false,
					'primary'
				);
				
				$KeyBoard->AddButton (
					'🔥 Перевести',
					['func' => 'mytransf', 'data' => ''],
					false,
					'positive'
				);
				
				$pays = $HTTP->GET ("https://edge.qiwi.com/payment-history/v2/persons/{$Cfg->QiwiNumber}/payments?operation=ALL&rows=50", array ('Accept:application/json', "Authorization:Bearer {$Cfg->QiwiToken}"));
			
				$money = 0;
				$find = false;
			
				if (!empty ($pays))
				{
					$unickey = $info [2]['unickey'];
					
					$pays = @json_decode ($pays, true);
					foreach ($pays ['data'] as $item)
					{
						if ($item ['sum']['currency'] == 643
							&& strtolower(str_replace (['.', ' '], '', $item ['comment'])) == $unickey)
						{
							$money = $item ['sum']['amount'];
							$find = true; break;
						}
					}
				}
				
				if ($find)
				{
					$getBonus = @Bonus ((int)$money);
					$balance  = round (($info [2]['money'] + ($getBonus [2] == 0 ? $money : $getBonus [2])));
					
					$tlk = $db->query ("SELECT * FROM MikeBf WHERE id = '0'", 'array:assoc');
					if (!$tlk) $db->query ("INSERT INTO MikeBf VALUES('0', '0')", 'q');
					
					$db->query ("UPDATE MikeBf SET tlk = '".($tlk ? ($tlk [0]['tlk'] + $money) : 0)."' WHERE id = '0'", 'q');
					
					$db->query ("UPDATE MikeDb SET unickey = '', money = '{$balance}' WHERE user_id = '{$info [2]['user_id']}'", 'q');
					$Control->printm ("👑 Оплата прошла успешно.\n💳 Ваш баланс на данный момент: {$balance} руб." . ($getBonus [0] ? ('(С учетом бонуса ' . $getBonus [1] . '%)') : '') . "\n\n🔥 Чтобы перевести данные средства в Ваш !lk кабинет, нажмите кнопку <<Перевести>> или напишите ".prefix."Перевести [Сумма].", '', 0, $KeyBoard->Get ());
				}
				else
					$Control->printm ("📛 Оплата не найдена.\nПодождите 30-60 сек и попробуйте снова.\n\n👨‍💻 Если Вы оплатили и после 30-60 секунд зачисление не произошло, напишите мне ВКонтакте: https://vk.com/id" . @(explode (',', $AdminId) [0]));
			}
			else
				$Control->printm ("❗️ Напишите команду <<".prefix."Пополнить>> чтобы оплатить и проверить оплату.");
		}
		else
			$Control->printm ('❗️ Автоматическая оплата была отключена Администратором.');
	};

?>