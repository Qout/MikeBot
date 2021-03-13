<?php

class Control
{
	protected $Events = [];
	
	public function __construct ($Events)
	{
		$this->Events = $Events;
	}
	
	public function ban (string $user_id, int $time, string $description): void
	{
		global $AdminId, $db;
		
		if (!StrContains ($user_id, $AdminId))
			$db->query ("UPDATE MikeDb SET ban = '".base64_encode (json_encode (['ban' => true, 'time' => (time () + ($time * 60)), 'warning' => 0, 'description' => (empty ($description) ? 'Причина не указана' : $description)]))."', MEvents = '' WHERE user_id = '{$user_id}'", 'q');
		else
			$this->printm ('❗ Ошибка, заблокировать Гл. Администратора невозможно!');
	}
	
	public function isBan (array $userInfo, bool $inf = false): bool
	{
		global $AdminId;
		$banInfo = json_decode (base64_decode ($userInfo ['ban']), true);
		
		if (!StrContains ($userInfo ['user_id'], $AdminId) && $banInfo ['ban'])
		{
			if ($inf)
			{
				global $db;
				
				if ($banInfo ['time'] <= time ())
				{
					$db->query ("UPDATE MikeDb SET ban = '".base64_encode (json_encode (['ban' => false, 'time' => 0, 'warning' => 0, 'description' => null]))."' WHERE user_id = '{$userInfo ['user_id']}'", 'q');
					
					$this->printm ("Время блокировки истекло.\nПодробнее: Теперь Вы можете пользоваться ботом.");
					return false;
				}
				elseif ($banInfo ['warning'] < 1)
				{
					$db->query ("UPDATE MikeDb SET ban = '".base64_encode (json_encode (['ban' => $banInfo ['ban'], 'time' => $banInfo ['time'], 'warning' => $banInfo ['warning'] + 1, 'description' => $banInfo ['description']]))."' WHERE user_id = '{$userInfo ['user_id']}'", 'q');
					
				    $this->printm ("❗ Ошибка, Вы были заблокированы.\nДлительность блокировки: " . date ('H:i:s / d.m.Y', $banInfo ['time']) . ".\n\nПричина: " . $banInfo ['description'] . '.');
				}
			}
			
			return true;
		}
		else
			return false;
	}
	
	// @Работает только при нажатие на кнопку Callback
	public function popup (string $text, $peer_id = 0): bool
	{
		global $Api;
		
		if ($this->Events->event_id == null || empty (@trim ($text)))return false;
		else
		{
			if ($peer_id == 0) $peer_id = $this->Events->peer_id;
			
			$Api->Inquiry ('messages.sendMessageEventAnswer', [
				'event_id' => $this->Events->event_id,
				'user_id' => $this->Events->user_id,
				'peer_id' => $peer_id,
				'event_data' => json_encode (['type' => 'show_snackbar', 'text' => trim ($text)])
			]);
			
			return true;
		}
	}
	
	public function getm ($peer_id = 0, $conversation_message_id = 0): array
	{
		global $Api;
		
		if ($peer_id == 0) 				   $peer_id 			    = $this->Events->peer_id;
		if ($conversation_message_id == 0) $conversation_message_id = $this->Events->conversation_message_id;
		
		$response = @$Api->Inquiry ('messages.getByConversationMessageId', [
			'peer_id' => $peer_id,
			'conversation_message_ids' => $conversation_message_id
		]);
		
		$_ret = ['message' => @$response ['items'][0]['text']];
		if (array_key_exists ('keyboard', @$response ['items'][0]) && count (@$response ['items'][0]['keyboard']) >= 3)
		{
			unset ($response ['items'][0]['keyboard']['author_id']);
			$_ret ['keyboard'] = $response ['items'][0]['keyboard'];
		}
		
		return $_ret;
	}
	
	public function editm (string $newtext, $keyboard = '', $peer_id = 0, $conversation_message_id = 0): void
	{
		global $Api;
		
		if ($peer_id == 0) 				   $peer_id 			    = $this->Events->peer_id;
		if ($conversation_message_id == 0) $conversation_message_id = $this->Events->conversation_message_id;
		
		$KeyBoard_Enable 	 = is_array ($keyboard);
		
		$KeyBoard_Name	 	 = '';
		$KeyBoard_Structure	 = '';
		if ($KeyBoard_Enable)
		{
			$KeyBoard_Name	 	 = 'keyboard';
			$KeyBoard_Structure	 = json_encode ($keyboard);
		}
		
		$Api->Inquiry ('messages.edit', [
			'peer_id' 				  => $peer_id,
			'message' 				  => $newtext,
			'conversation_message_id' => $conversation_message_id,
			$KeyBoard_Name => $KeyBoard_Structure
		]);
	}
	
	public function printm ($text, string $attachment = '', $peer_id = 0, $keyboard = '', $random_id = 0, $template = '')
	{
		global $Api, $KeyBoard;
		
		if ($peer_id == 0) $peer_id = $this->Events->peer_id;
		
		if (empty ($keyboard)
			&& ($keyboard = @$KeyBoard->Get ())
			&& (@count ($keyboard) + @count (@$keyboard ['buttons'])) <= 3)
				$keyboard = '';
		
		$KeyBoard_Enable 	 = is_array ($keyboard);
		
		$KeyBoard_Name	 	 = '';
		$KeyBoard_Structure	 = '';
		if ($KeyBoard_Enable)
		{
			$KeyBoard_Name	 	 = 'keyboard';
			$KeyBoard_Structure	 = json_encode ($keyboard);
		}
		
		$Template_Enable 	 = is_array ($template);
		$Template_Name	 	 = ($Template_Enable ? 'template' : '');
		$Template_Structure	 = ($Template_Enable ? json_encode ($template) : '');
		
		$random_id = $this->Events->date + $peer_id + ($random_id * 2);
		
		return $Api->Inquiry ('messages.send', [$Template_Name => $Template_Structure, $KeyBoard_Name => $KeyBoard_Structure, 'random_id' => $random_id, 'peer_id' => $peer_id, 'message' => print_r ($text, true), 'attachment' => $attachment]);
	}
}

?>