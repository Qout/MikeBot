<?php

// Версия
define ('__VERSION__', '5.0R2 (12.03.2021)');

if (array_key_exists ('req', $_GET) && !empty ($_GET ['req']))
{
	switch (strtolower ($_GET ['req']))
	{
		case 'v':
			echo '<center>Version: <b>' . __VERSION__ . '</b></center><br />';
		break;
		
		case 'engine':
			echo '<center>Engine: <b>MikeBot</b>.</center><br />';
		break;
		
		default:
			echo '<center><h1><b>Unknown request</b></h1></center><br />';
		break;
	}
	
	return;
}

elseif (!isset ($_REQUEST)) return;
else
{	
	// Загружаем настройки
	$settings = ((file_exists (__DIR__ . '/configs/settings.txt') && !empty (file_get_contents (__DIR__ . '/configs/settings.txt')))
	? json_decode (file_get_contents (__DIR__ . '/configs/settings.txt'), true)
	: null);
	
	// Загружаем сервера
	$Servers = ((file_exists (__DIR__ . '/configs/servers.txt') && !empty (file_get_contents (__DIR__ . '/configs/servers.txt')))
	? json_decode (file_get_contents (__DIR__ . '/configs/servers.txt'), true)
	: null);

	define ('prefix', (empty ($settings ['prefix']) ? '/' : trim ($settings ['prefix'])));
	define ('enable_antiflood', $settings ['antiflood']);
	
	if (enable_antiflood)
	{
		define ('antiflood_delaytime', $settings ['antiflood_delaytime']);
		define ('antiflood_warning', $settings ['antiflood_warning']);
		define ('antiflood_bantime', $settings ['antiflood_bantime']);
	}

	if ($settings ['logs.show'])
	{
		// Включаем вывод всех ошибок
		error_reporting(E_ALL);
		
		// Задаем переменные php.ini
		ini_set ('error_log', 'errors_' . time (). '.txt');
		ini_set ('log_errors', true);
		ini_set ('display_errors', false);
	}
	
	// Устанавливаем время "Москва" для функций.
	date_default_timezone_set('Europe/Moscow');
	
	// Задаем переменные
	define ('__IMAGES__', __DIR__ . '/images');
	define ('__CORE__', __DIR__ . '/core');
	define ('__CFG__', __DIR__ . '/configs');
	define ('__PROFILES__', __CORE__ . '/dbs/users.db');
	
	
	// Подключаем функции и классы
	require __CORE__ . '/Funcs.php';
	require __CORE__ . '/ClassSerializer.php';
	require __CORE__ . '/Defines.php';
	require __CORE__ . '/Configs.php';
	require __CORE__ . '/LambxCall.php';
	require __CORE__ . '/HTTP.php';
	require __CORE__ . '/SQLite.php';
	require __CORE__ . '/MySQL.php';
	require __CORE__ . '/SteamIdConverter.php';
	require __CORE__ . '/GenerateCode.php';
	require __CORE__ . '/KeyBoard.php';
	require __CORE__ . '/SetMEvents.php';
	require __CORE__ . '/CallBack.php';
	require __CORE__ . '/Api.php';
	require __CORE__ . '/HandlerParams.php';
	require __CORE__ . '/HandlerMessages.php';
	require __CORE__ . '/Control.php';
	require __CORE__ . '/CServerConnect.php';
	require __CORE__ . '/CServerControl.php';
	require __CORE__ . '/HandlerMEvents.php';
	require __CORE__ . '/Flood.php';
	require __CORE__ . '/Bonus.php';
	require __CORE__ . '/OutErrors.php';
	
	global $funcs, $KeyBoard, $HTTP, $Api,
			$Callback, $Handler, $Control,
			$db, $MySql, $ServerControl,
			$AdminId, $SteamIdConvert, $EventNotifier;
	
	$AdminId = str_replace (['https', 'http', ':', '/', 'vk', '.', 'com', 'id', ' '], '', strtolower ($settings ['admin']));
	$EventNotifier = $settings ['EventNotify'] == 1 ? true : false;
	
	$SteamIdConvert = new SteamIDConverter ();
	__l ('SteamId', $SteamIdConvert);
	
	$KeyBoard 	   = new KeyBoard       ();
	$HTTP 		   = new HTTP           ();
	$Api 		   = new Api            ($settings);
	$ServerControl = new CServerControl ($Servers);
	$MEvents 	   = new MEvents        ($Servers);
	
	
	$GetGroupInfo = $Api->Inquiry ('groups.getById', []);
	
	if (!is_bool ($GetGroupInfo) 
		&& !empty (@$GetGroupInfo [0]['id'])
		&& !empty (@$GetGroupInfo [0]['screen_name'])
		&& !empty (@$GetGroupInfo [0]['name']))
	{
		define ('GROUP_ID', @$GetGroupInfo [0]['id']);
		define ('GROUP_DOMEN', @$GetGroupInfo [0]['screen_name']);
		define ('GROUP_NAME', @$GetGroupInfo [0]['name']);
	}
	else
	{
		file_put_contents ('errors_' . time () . '.txt', "Ошибка, не могу получить информацию о группе.\n");
		return;
	}
	
	// Перехватываем события которые нам сообщил ВКонтакте
	$Callback = new Callback ($Api->SAPI ['secretKey']);
	$Events = $Callback->GetEvents ();
	
	if (!is_bool ($Events))
	{
		// Подключаем sqlite3
		$db = new CSQLite3 (__PROFILES__);
		$db->query ('CREATE TABLE IF NOT EXISTS MikeDb(user_id INT PRIMARY KEY NOT NULL, fname TEXT NOT NULL, lname TEXT NOT NULL, MEvents TEXT NOT NULL, flood TEXT NOT NULL, ban TEXT NOT NULL, access INT NOT NULL, unickey TEXT NOT NULL, money INT NOT NULL, opencase INT NOT NULL, steamid TEXT NULL, likes INT NOT NULL, dislikes INT NOT NULL, listrep TEXT NOT NULL);', 'q');
		$db->query ('CREATE TABLE IF NOT EXISTS MikeBf(id INT PRIMARY KEY NOT NULL, tlk INT NOT NULL);', 'q');
		
		// Подключаем MySQL всех серверов
		$MySql = new CMySQL ($Servers);
		
		// Подключаем базовый класс для управления сообщениями и т.д
		$CEvents = (object)
		[
			'user_id' => ($Events->type == 'message_event' ? @$Events->object->user_id : (
				$Events->type == 'message_new' ? @$Events->object->message->from_id : 0
			)),
			'peer_id' => ($Events->type == 'message_event' ? @$Events->object->peer_id : (
				$Events->type == 'message_new' ? @$Events->object->message->peer_id : 0
			)),
			'event_id' => ($Events->type == 'message_event' ? $Events->object->event_id : null),
			'conversation_message_id' => ($Events->type == 'message_event' ? $Events->object->conversation_message_id : (
				$Events->type == 'message_new' ? @$Events->object->message->conversation_message_id : 0
			)),
			'date' => ($Events->type == 'message_event' ? $Events->object->conversation_message_id : (
				$Events->type == 'message_new' ? @$Events->object->message->date : 0
			))
		];
		
		$Control = new Control ($CEvents);
		
		define ('IsAdmin', @in_array ($CEvents->user_id, @explode (',', @$AdminId)));
		
		// Загружаем все команды
		foreach (glob (__CORE__ . '/commands/*.php') as $file) require $file;
		
		// Создаем и запускаем обработчик сообщений
		$Handler = new HandlerMessages ([$HTTP, $Api, $Callback, $Control, $db, $MEvents, $funcs, $MySql]);
		$Handler->Messages ($Events);
	}
}

?>