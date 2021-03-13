<?php

function CheckFlood (array $userInfo): bool
{
	global $Control, $db, $AdminId;
	
	$user_id = $userInfo ['user_id'];
	$info = json_decode (base64_decode ($userInfo ['flood']), true);
	
	$time = $info ['time'];
	$warning = $info ['warning'];
	
	if ($user_id == $AdminId) return false;	// Пользователям с правами "Администратор" разрешается флудить. (Если Вы хотите запретить - удалите данную строку)
	
	if ($time <= time ())
	{
		$db->query ("UPDATE MikeDb SET flood = '".base64_encode(json_encode (['time' => time () + antiflood_delaytime, 'warning' => 0]))."' WHERE user_id = '{$user_id}'", 'q');
		return false;
	}
	elseif ($warning < antiflood_warning)
	{
		$db->query ("UPDATE MikeDb SET flood = '".base64_encode(json_encode (['time' => $time, 'warning' => $warning + 1]))."' WHERE user_id = '{$user_id}'", 'q');
		$Control->printm ("Пожалуйста, перестаньте флудить.\nВы сможете пользоваться ботом через " . ($time - time ()) . " сек, если Вы продолжите флудить, Вы будете заблокированы на " . antiflood_bantime . ' мин.');
	}
	else
		$Control->ban ($user_id, antiflood_bantime, 'флуд');
	
	return true;
}

?>