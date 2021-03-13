<?php

class KeyBoard
{
	private $Struct = null;
	private $Type = null;
	
	public function __construct (string $type = 'callback')
	{
		if ($this->Struct == null)
		{
			$this->Struct = (object)[
				'one_time' => false,
				'inline'   => true,
				'buttons'  => []
			];
			
			$this->Type = $type;
		}
	}
	
	public function Option (bool $one_time = false, bool $inline = true, string $type = 'callback'): void
	{
		if ($this->Struct == null)return;
		
		$this->Struct->one_time = $one_time;
		$this->Struct->inline = $inline;
		$this->Type = $type;
	}
	
	public function Get (): array
	{
		if ($this->Struct == null)return [];
		
		$_ret = (array)$this->Struct;
		
		// @Clear
		$this->Struct->buttons = [];
		
		return $_ret;
	}
	
	public function AddButton (string $name, array $payload = [], bool $toline = false, string $color = 'primary'): void
	{
		$i = count ($this->Struct->buttons);
		
		//@$payload ['date'] = time ();
		
		if ($toline && $i > 0)
			$this->Struct->buttons[($i-1)][] = ['action' => ['type' => $this->Type, 'label' => $name, 'payload' => json_encode ($payload)], 'color' => $color];
		else
			$this->Struct->buttons[] = [['action' => ['type' => $this->Type, 'label' => $name, 'payload' => json_encode ($payload)], 'color' => $color]];
	}
}

?>