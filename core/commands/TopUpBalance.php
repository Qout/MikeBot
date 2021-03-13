<?php

	global $funcs, $Api, $Control, $db, $AdminId, $KeyBoard;

/* Информация о команде */
    $name = 'пополнить';
    $funcs [$name]['params'] 			 = 0;					// Кол-во параметров
    $funcs [$name]['description'] 		 = "Пополнить баланс";	// Описание команды
	$funcs [$name]['conversations'] 	 = false; 				// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = false; 				// Использовать команду возможно только в беседах. (true: Да / false: Нет)
    $funcs [$name]['hide'] 				 = false; 				// Скрыть команду


/* Работа команды */
    $funcs [$name]['func'] = function (array $info) use ($Api, $Control, $db, $AdminId, $KeyBoard): void
    {
		if ((($Cfg = Cfg ('tlk')) && $Cfg->enable) 
			&& ($Cfg = Cfg ('qiwi.pays')) && ((strlen ($Cfg->QiwiNumber)+strlen ($Cfg->QiwiToken)) > 38))
		{
			if (empty ($info [2]['unickey']))
			{
				$unickey = '#' . CreateCode ($info [2]['user_id']);
				$db->query ("UPDATE MikeDb SET unickey = '{$unickey}' WHERE user_id = '{$info [2]['user_id']}'", 'q');
			}
			else $unickey = $info [2]['unickey'];
			
			$KeyBoard->AddButton (
				'🤖 Проверить оплату',
				['func' => 'checkpay', 'data' => $unickey],
				false,
				'positive'
			);
			
			$_KeyBoard = $KeyBoard->Get ();
			$_KeyBoard ['buttons'][] = [['action' => ['type' => 'open_link', 'link' => "https://qiwi.com/payment/form/99?blocked[0]=account&blocked[1]=comment&extra['comment']=". urlencode($unickey) ."&extra['account']={$Cfg->QiwiNumber}", 'label' => '💳 Оплатить']]];
			$Control->printm ("💸 Оплата производиться через сервис QIWI.\n❗ Оплата работает только через ПК-версию.\n\n🤖 После того как Вы произвели оплату, нажмите кнопку <<Проверить>>.\n\n👨‍💻 Если у Вас возникли какие-то трудности: https://vk.com/id" . @(explode (',', $AdminId) [0]), '', 0, $_KeyBoard);
		}
		else
			$Control->printm ('❗️ Автоматическая оплата была отключена Администратором.');
	};

?>