<?php

class CServerControl
{
	public $servers = [];
	
	function __construct ($servers)
	{
		$this->servers = $servers;
	}
	
	public function send (string $command, int $sid = -1): string
	{
		$sid = (($sid == -1 ? 1 : $sid) - 1);
		
		$r = new CServerConnect ($this->servers [$sid]['ip'], $this->servers [$sid]['port'], $this->servers [$sid]['password']);
		$r->Connect ($this->servers [$sid]['ip'], $this->servers [$sid]['port']);
		$info = $r->GetInfo ();
		
		if ($r->AuthRcon ($this->servers [$sid]['password']))
		{
			$_ret = trim ($r->SendCommand ($command));
			
			if (empty ($_ret)) return '';
			else
			{
				$_ret = explode ("\n", $_ret);
				
				if (@strtolower(@substr (@$_ret [count ($_ret)-1], 0, 2)) == 'l ')
					unset ($_ret [count ($_ret) - 1]);
				
				return trim (implode ("\n", $_ret));
			}
		}
		else
			return '';
	}
}

?>