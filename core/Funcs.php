<?php

// Fix Encoding
function mb_ucfirst ($string, $encode = 'UTF-8')
{
	return mb_strtoupper (mb_substr ($string, 0, 1, $encode), $encode) . mb_substr ($string, 1, mb_strlen($string, $encode), $encode);
}

// Fix Encoding
function mb_lcfirst ($string, $encode = 'UTF-8')
{
	return mb_strtolower (mb_substr ($string, 0, 1, $encode), $encode) . mb_substr ($string, 1, mb_strlen($string, $encode), $encode);
}

// For PHP < 7.3
if (!function_exists('is_countable'))
{
    function is_countable($var): bool
	{
        return (is_array($var) || $var instanceof Countable);
    }
}

function StrContains ($str, $find): bool
{
	if (is_string ($str) && is_string ($find) && !empty ($str) && !empty ($find))
		return !(mb_strripos ($str, $find) === false);
	else
		return false;
}

function NormalString (string $str): string
{
	return trim (mb_eregi_replace ('[^a-zа-яё0-9# ]', '', $str));
}

function IpToStr (string $ip): string
{
	global $HTTP;
	
	$_ip = @explode (':', $ip) [0];
	
	$net = '';
	$geojson = @json_decode ($HTTP->GET ("http://ip-api.com/json/{$_ip}"), true);
	
	if (!empty ($geojson)
		&& is_array($geojson)
		&& count ($geojson) >= 3
		&& array_key_exists ('country', $geojson))
	{
		$net = strtolower (@$geojson ['country']);
		
		switch ($net)
		{
			case 'russia':
				$lang = '🇷🇺 Russia';
			break;
			
			case 'united states':
				$lang = '🇺🇸 United States';
			break;
			
			case 'united kingdom':
				$lang = '🇬🇧 United Kingdom';
			break;
			
			case 'canada':
				$lang = '🇨🇦 Canada';
			break;
			
			case 'france':
				$lang = '🇫🇷 France';
			break;
			
			case 'netherlands':
				$lang = '🇳🇱 Netherlands';
			break;
			
			case 'germany':
				$lang = '🇩🇪 Germany';
			break;
			
			case 'ukraine':
				$lang = '🇺🇦 Ukraine';
			break;
			
			case 'belarus':
				$lang = '🇧🇾 Belarus';
			break;
			
			case 'kazakhstan':
				$lang = '🇰🇿 Kazakhstan';
			break;
			
			default:
				$lang = '🏳️‍🌈 ' . @$geojson ['country'];
			break;
		}
	}
	
	$_ret = ($net == 'none' ? '🏳️‍🌈 Неизвестная' : ((empty ($lang) && !empty (@$geojson ['country'])) ? ('🏳️‍🌈 ' . @$geojson ['country']) : $lang));

	return $_ret . (empty (@$geojson ['city']) ? '' : ", {$geojson ['city']}");
}

function IsUser (int $user_id): bool
{
	return !(!$GLOBALS ['db']->query ("SELECT * FROM MikeDb WHERE user_id = '{$user_id}'", 'array:assoc'));
}

function RegUser (int $user_id)
{
	global $Api, $db;
	
	$info = $Api->Inquiry ('users.get', ['user_ids' => $user_id]);
	$info [0]['first_name'] = NormalString ($info [0]['first_name']);
	$info [0]['last_name']  = NormalString ($info [0]['last_name']);
	
	if (IsUser ($user_id))
	{
		$userInfo = @$db->query ("SELECT * FROM MikeDb WHERE user_id = '{$user_id}';", 'array:assoc');
		// @Обновляем Имя и Фамилию Пользователя в базе данных
		if ($userInfo && !StrContains ($userInfo [0]['fname'], $info [0]['first_name'])
			|| !StrContains ($userInfo [0]['lname'], $info [0]['last_name']))
				$db->query ("UPDATE MikeDb SET fname = '{$info [0]['first_name']}', lname = '{$info [0]['last_name']}' WHERE user_id = '{$user_id}'", 'q');
				
		if ($userInfo && count ($userInfo) == 1)return $userInfo [0];
	}
	else
	{
		// @Если Пользователь впервые воспользовался ботом
		$db->query ("INSERT INTO MikeDb (user_id, fname, lname, MEvents, flood, ban, access, unickey, money, opencase, likes, dislikes, listrep) VALUES('{$user_id}', '{$info [0]['first_name']}', '{$info [0]['last_name']}', '', '".base64_encode(json_encode (['time' => 0, 'warning' => 0]))."', '".base64_encode(json_encode (['ban' => false, 'time' => 0, 'warning' => 0, 'description' => null]))."', '0', '', '0', '".(time () - 300)."', '0', '0', '')", 'q');
		return true;
	}
	
	return false;
}

function CmdArgs (int $param)
{
	$Params = $GLOBALS ['__params'];
	return (($param > 0 && count ($Params) >= $param) ? @$Params [$param-1] : ((count ($Params) > 0 && $param == -1) ? $Params : null));
}

function CountArgs ()
{
	return @count ($GLOBALS ['__params']);
}

?>