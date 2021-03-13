<?php

/* Портируем команду и подключаем класс для отправки сообщений */
	global $funcs, $Control, $db, $ServerControl;

/* Информация о команде */
    $name = 'майк';
    $funcs [$name]['params'] 			 = 0;					// Кол-во параметров
    $funcs [$name]['description'] 		 = "Информация о боте";	// Описание команды
	$funcs [$name]['conversations'] 	 = false; 				// Возможность использовать команду в беседах. (true: Да / false: Нет)
	$funcs [$name]['conversations_only'] = false; 				// Использовать команду возможно только в беседах. (true: Да / false: Нет)
    $funcs [$name]['hide'] 				 = true; 				// Скрыть команду


/* Работа команды */
    $funcs [$name]['func'] = function (array $info) use ($Control, $db, $ServerControl): void
    {
		if (IsAdmin)
		{
			$tlk = $db->query ("SELECT * FROM MikeBf", 'array:assoc');
			if (!$tlk) $db->query ("INSERT INTO MikeBf VALUES('0', '0')", 'q');
			
			$version = @$ServerControl->send ('sm_mike', 1);
			
			// 		   Local Info (If Server Not Response)
			$version = empty ($version) ? __VERSION__
					   : mb_substr (explode ("\n", $version) [1], 8);
			
			$Control->printm ("👑 Бот на движке: MikeBot\n&#128421; Версия: {$version}\n\n&#129302; Загружено: ". count ($GLOBALS ['funcs']) ." команд\n&#128101; В базе: ". $db->count_row ('MikeDb') ." пользователей\n&#128184; Общая сумма денег за все время: ". ($tlk ? $tlk [0]['tlk'] : 0) ." RUB\n\n❗ Данная команда доступа только для Администратора.");
		}
		else $Control->printm ("❗ Ошибка, у Вас нет доступа к этой команде.");
	};

?>