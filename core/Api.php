<?php

class Api
{
	public $SAPI = [
        'url' => null,
        'token' => null,
		'secretKey' => null,
		'confirmationToken' => null,
        'version' => null
    ];
	
	function __construct ($settings)
	{
		if ($settings != null)
		{
			$this->SAPI ['url'] = 'https://api.vk.com/method';
			$this->SAPI ['token'] = $settings ['group.token'];
			$this->SAPI ['secretKey'] = $settings ['key.secret'];
			$this->SAPI ['confirmationToken'] = $settings ['group.token.confirmation'];
			$this->SAPI ['version'] = $settings ['vkapi.version'];
		}
	}
	
	public function UploadMessagePhoto (string $filename, string $token = null) 
	{
		$HTTP = new HTTP ();
		
		$response = @json_decode ($HTTP->POST('https://api.vk.com/method/photos.getMessagesUploadServer', [
			'peer_id' => 0,
			'access_token' => ($token == null ? $this->SAPI ['token'] : $token),
			'v' => $this->SAPI ['version']
		]), true);
		
		if (is_array ($response) && array_key_exists ('response', $response))
		{
			$response = @json_decode ($HTTP->POST (@$response ['response']['upload_url'], [
				'photo' => new cURLFile ($filename)
			]), true);
			
			if (is_array ($response) && array_key_exists ('photo', $response))
			{
				$response = @json_decode ($HTTP->POST('https://api.vk.com/method/photos.saveMessagesPhoto', [
					'photo' => $response ['photo'],
					'server' => $response ['server'],
					'hash' => $response ['hash'],
					
					'access_token' => ($token == null ? $this->SAPI ['token'] : $token),
					'v' => $this->SAPI ['version']
				]), true);
				
				if (is_array ($response) && array_key_exists ('response', $response))
					return $response ['response'];
			}
		}
		
		return '';
	}
	
	public function Inquiry (string $method, array $params, string $token = null) 
	{
		$HTTP = new HTTP ();
		$response = @json_decode ($HTTP->POST('https://api.vk.com/method/' . $method, $params + [
			'access_token' => ($token == null ? $this->SAPI ['token'] : $token),
			'v' => $this->SAPI ['version']
		]), true);
		
		if (is_array($response) && !array_key_exists('error', $response))
			return $response ['response'];
		elseif (is_array($response) && array_key_exists('error', $response))
			return $response ['error']['error_code'];
		else
			return false;
	}
}

?>