<?php

/* Портируем команду и подключаем класс для отправки сообщений */
	global $funcs, $Control, $db;

/* Информация о команде */
    $name = 'install';
    $funcs [$name]['params'] 			 = 0;					// Кол-во параметров
    $funcs [$name]['description'] 		 = "Авто-настройка бота под обновление";					// Описание команды
	$funcs [$name]['conversations'] 	 = true; 				// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = false; 				// Использовать команду возможно только в беседах. (true: Да / false: Нет)
    $funcs [$name]['hide'] 				 = true; 				// Скрыть команду


/* Работа команды */
    $funcs [$name]['func'] = function (array $info) use ($Control, $db): void
    {
		if (IsAdmin)
		{
			foreach (
			[
				'likes INT NOT NULL',
				'dislikes INT NOT NULL',
				'listrep TEXT NOT NULL',
			] as $q)
				@$db->query ("ALTER TABLE MikeDb ADD {$q}", 'q');
			
			$Control->printm ('Всё готово к работе!');
			@unlink (__CORE__ . '/commands/Install.php');
		}
	};

?>