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
	return trim (mb_eregi_replace ('[^a-zΠ°-ΡΡ0-9# ]', '', $str));
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
				$lang = 'π·πΊ Russia';
			break;
			
			case 'united states':
				$lang = 'πΊπΈ United States';
			break;
			
			case 'united kingdom':
				$lang = 'π¬π§ United Kingdom';
			break;
			
			case 'canada':
				$lang = 'π¨π¦ Canada';
			break;
			
			case 'france':
				$lang = 'π«π· France';
			break;
			
			case 'netherlands':
				$lang = 'π³π± Netherlands';
			break;
			
			case 'germany':
				$lang = 'π©πͺ Germany';
			break;
			
			case 'ukraine':
				$lang = 'πΊπ¦ Ukraine';
			break;
			
			case 'belarus':
				$lang = 'π§πΎ Belarus';
			break;
			
			case 'kazakhstan':
				$lang = 'π°πΏ Kazakhstan';
			break;
			
			default:
				$lang = 'π³οΈβπ ' . @$geojson ['country'];
			break;
		}
	}
	
	$_ret = ($net == 'none' ? 'π³οΈβπ ΠΠ΅ΠΈΠ·Π²Π΅ΡΡΠ½Π°Ρ' : ((empty ($lang) && !empty (@$geojson ['country'])) ? ('π³οΈβπ ' . @$geojson ['country']) : $lang));

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
		// @ΠΠ±Π½ΠΎΠ²Π»ΡΠ΅ΠΌ ΠΠΌΡ ΠΈ Π€Π°ΠΌΠΈΠ»ΠΈΡ ΠΠΎΠ»ΡΠ·ΠΎΠ²Π°ΡΠ΅Π»Ρ Π² Π±Π°Π·Π΅ Π΄Π°Π½Π½ΡΡ
		if ($userInfo && !StrContains ($userInfo [0]['fname'], $info [0]['first_name'])
			|| !StrContains ($userInfo [0]['lname'], $info [0]['last_name']))
				$db->query ("UPDATE MikeDb SET fname = '{$info [0]['first_name']}', lname = '{$info [0]['last_name']}' WHERE user_id = '{$user_id}'", 'q');
				
		if ($userInfo && count ($userInfo) == 1)return $userInfo [0];
	}
	else
	{
		// @ΠΡΠ»ΠΈ ΠΠΎΠ»ΡΠ·ΠΎΠ²Π°ΡΠ΅Π»Ρ Π²ΠΏΠ΅ΡΠ²ΡΠ΅ Π²ΠΎΡΠΏΠΎΠ»ΡΠ·ΠΎΠ²Π°Π»ΡΡ Π±ΠΎΡΠΎΠΌ
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