<?php

	global $funcs, $Control, $db;

/* Информация о команде */
    $name = 'кейсы';
    $funcs [$name]['params'] 			 = 0;							// Кол-во параметров
    $funcs [$name]['description'] 		 = "Открытие кейсов на деньги";	// Описание команды
	$funcs [$name]['conversations'] 	 = true; 						// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = false; 						// Использовать команду возможно только в беседах. (true: Да / false: Нет)
    $funcs [$name]['hide'] 				 = false; 						// Скрыть команду


/* Работа команды */
    $funcs [$name]['func'] = function (array $info) use ($Control, $db): void
    {
		if (($Cfg = Cfg ('cases')) && $Cfg->cases_enable)
		{
			if (!array_key_exists ('opencase', $info [2]))
				$db->query ("UPDATE MikeDb SET opencase = '".time ()."' WHERE user_id = '{$info [2]['user_id']}'", 'q');
			
			$btns = [];
			
			$i = 0;
			$outinfo = '';
			foreach ($Cfg->cases_data as $name => $data)
			{
				$i++;
				$btns[] = 
				[
					'action' => [
						'type' 	  => 'callback',
						'label'   => $name,
						'payload' => json_encode (['date' => time (), 'func' => 'cases', 'data' => [$name]])
					],
					'color' => 'positive'
				];
				
				$outinfo .= "{$i}.  Кейс <<" . $name . '>> ' . $data ['money'] . ' руб. / Вы можете выиграть от ' . $data ['min'] . ' до ' . $data ['max'] . " руб.\n";
			}
			
			if (!empty ($outinfo))
			{
				$keyboard = 
				[
					'one_time' => false,
					'inline' => true,
					'buttons' => [$btns]
				];
				
				$Control->printm ("📦 Выберите какой кейс хотели бы открыть.\n\n" . trim ($outinfo) . "\n\n💬 К слову -- Вы можете также и проиграть свои деньги, так что перед тем как играть подумайте хорошо. Вас никто не принуждает открывать кейсы.\n❗️ Деньги за проигрыш мы также не возвращаем.", '', 0, $keyboard);
			}
			else
				$Control->printm ('❗️ Ошибка, кейсы не настроены.');
		}
		else
			$Control->printm ("❗️ Кейсы были отключены Администратором.");
    };

?>