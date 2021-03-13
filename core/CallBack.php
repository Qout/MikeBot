<?php

class Callback
{
	protected $skey = null;
	
	public function __construct ($secretKey)
	{
		$this->skey = $secretKey;
	}
	
	public function GetEvents ()
	{
		$data = file_get_contents ('php://input');
		if (!empty ($data))
		{
			$data = json_decode ($data);
			if ($data->secret == $this->skey) return $data;
		}
		
		return false;
	}
}

?>