<?php

/* Портируем команду и подключаем класс для отправки сообщений */
	global $funcs, $Control, $KeyBoard;
	
/* Информация о команде */
    $name = strtolower (basename(__FILE__, '.php'));							// Название команды. (Это - Название данного файла)
    $funcs [$name]['params'] 			 = 0;									// Кол-во параметров.
    $funcs [$name]['description'] 		 = "Пример команды для разработчиков";	// Описание команды.
    $funcs [$name]['conversations'] 	 = false; 								// Возможность использовать команду в беседах. (Да - true / Нет - false)
	$funcs [$name]['conversations_only'] = false; 								// Использовать команду возможно только в беседах. (Да - true / Нет - false)
    $funcs [$name]['hide'] 				 = true; 								// Скрыть команду. (Да - true / Нет - false)

/* / Информация о команде /           / Параметры /*/
	$funcs [$name]['func'] = function (array $info) use (
	
		/*
		 * Подключаем классы
		 */
		 
		$Control,
		$KeyBoard
		
		
	): void
	{
		return;	// Чтобы невозможно было вызвать.
		
		$params_count = count ($info [0]);	// Подсчитываем сколько-параметров задал пользователь.
		$params 	  = $info [0];			// Передаем параметры в переменную.
		
		$var1 = ($params_count > 0
		? print_r ($params, true)
		: 'Без параметров.');				// Переменная которая проверяет сколько-ко параметров задано, если их <0> то возвращает - Без параметров.
		
		$user_id 	= $info [2]['user_id'];	// Id-пользователя который вызвал команду.
		$first_name = $info [2]['fname'];	// Его имя.
		$last_name 	= $info [2]['lname'];	// Его фамилия.
		
		// Добавим 2 кнопки
		// 1 кнопка: Привет мир (Цвет: Зеленая)
		// 2 кнопка: RED (Цвет: Красная)
		
		$KeyBoard->AddButton (
			'Привет мир',									// Название	(*Обязательный)
			['func' => 'Test', 'data' => ['information']],	// Payload	(Дефолт: [])
			false,											// В одну линию? (true - Да, false - Нет. Дефолт: false)
			'primary'										// Цвета: secondary, primary, positive, negative (Дефолт: primary)
		);
		
		$KeyBoard->AddButton (
			'RED',											// Название	(*Обязательный)
			['func' => 'Test', 'data' => ['information']],	// Payload	(Дефолт: [])
			true,											// В одну линию? (true - Да, false - Нет. Дефолт: false)
			'negative'										// Цвета: secondary, primary, positive, negative (Дефолт: primary)
		);
		
		
		// Отправляем сообщение
		$Control->printm ($var1);
	};

?>