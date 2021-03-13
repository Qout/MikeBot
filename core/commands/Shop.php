<?php

	global $funcs, $Control, $ServerControl, $KeyBoard;

	foreach (['магазин', 'shop', 'store'] as $i => $name)
	{
		/* Информация о команде */
		$funcs [$name]['params'] 			 = 0;							// Кол-во параметров
		$funcs [$name]['description'] 	     = "Покупка вещей на сервере";	// Описание команды
		$funcs [$name]['conversations']      = false; 						// Возможность использовать команду в беседах. (true: Да / false: Нет)
		$funcs [$name]['conversations_only'] = false; 						// Использовать команду возможно только в беседах. (true: Да / false: Нет)
		$funcs [$name]['hide'] 			     = ($i > 0 ? true : false); 	// Скрыть команду


		/* Работа команды */
		$funcs [$name]['func'] = function (array $info) use ($Control, $ServerControl, $KeyBoard): void
		{
			if (($Cfg = Cfg ('shop')) && $Cfg->enable)
			{
				$items = $Cfg->items;
				$iItems = count ($items);
				
				if ($iItems == 0)
				{
					$Control->printm ('❗ Ошибка, приват вещи пока что никакие не продаются.');
					return;
				}
				
				$Colors = ['primary', 'positive', 'negative'];
				if (count ($ServerControl->servers) == 0)
				{
					$Control->printm ('❗ Ошибка, никакие сервера к боту не подключены!');
					return;
				}
				
				$btns = [];
				$glob_i = 0;
				$i = 0;
				
				foreach ($items as $ServerId => $Info)
				{
					$ServerId--;
					$glob_i++;
					$i++;
					
					if ($i >= 1)
					{
						$KeyBoard->AddButton (
							$ServerControl->servers [$ServerId]['title'],
							['func' => 'shop', 'data' => [$ServerId]],
							false,
							$Colors [$i-1]
						);
						
						if ($i == 3 || ($iItems-$glob_i) == 0)$i = 0;
					}
					
					if ($i == 0)
						$Control->printm ((($glob_i > 0 && $glob_i < 4) ? '👑 Выберите на каком сервере хотите купить вещи.' : '&#13;'), '', 0, $KeyBoard->Get (), $glob_i+1);
				}
			}
			else
				$Control->printm ('❗️ Магазин был отключен Администратором.');
		};
	}

?>