<?php

	global $funcs, $Control;

/* Информация о команде */
    $name = 'команды';
	$funcs [$name]['params'] 			 = 0;						// Кол-во параметров
	$funcs [$name]['description'] 		 = "Информация о командах";	// Описание команды
	$funcs [$name]['conversations'] 	 = true; 					// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = false; 					// Использовать команду возможно только в беседах. (true: Да / false: Нет)
	$funcs [$name]['hide'] 				 = true; 					// Скрыть команду
	
/* Работа команды */
	$funcs [$name]['func'] = function (array $info) use ($Control): void
	{
		global $funcs;
		$help = '';
		
		ksort ($funcs);
		foreach ($funcs as $key => $value)
		    if (!$value ['hide'])
				$help .= '❗️ ' . prefix . mb_ucfirst ($key) . ' -- ' . mb_ucfirst ($value ['description']) . ".\n";
		
		$Control->printm ("📖 Информация о командах.\n\n" . trim ($help));
	};
	
	// Дублируем команду.
	$funcs ['помощь'] = $funcs [$name];
	$funcs ['помощь']['hide'] = false;

?>