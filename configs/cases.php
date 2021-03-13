<?php

return [
	'cases_enable' => 0,		// Включить кейсы?. (Да - 1 / Нет - 0)
	'cases_time'   => 43200,	// Через сколько времени можно будет снова открыть кейсы. (В секундах / Поставьте 0 чтобы отключить / 43200 - 12 часов)
	
	'cases_data' => [
		'Диор' => [
			'min' => 25,
			'max' => 200,
			'money' => 100
		],
		
		'Дриада' => [
			'min' => 50,
			'max' => 300,
			'money' => 150
		],
		
		'Илиодор' => [
			'min' => 150,
			'max' => 600,
			'money' => 300
		]
	],
];

?>