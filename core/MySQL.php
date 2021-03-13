<?php

class CMySQL
{
	private $sql;
	private $ServerId;
	
	public function __construct (array $databases)
	{
		$this->sql = [];
		$this->ServerId = -1;
		
		if (count ($databases) > 0)
		{
			for ($i = 0; $i < count ($databases); $i++)
			{
				$database = @$databases [$i]['mysql'][0];
				if (!is_array ($database))continue;
				
				$host 	  = @$database ['host'];
				$db_name  = @$database ['database'];
				$username = @$database ['user'];
				$password = @$database ['pass'];
				$port 	  = (array_key_exists ('port', $database) ? $database ['port'] : 3306);
				
				
				if (!empty ($host) && !empty ($db_name) && !empty ($username) && !empty ($password) && !empty ($port))
				{
					$connect     = @new mysqli ($host, $username, $password, $db_name, $port);
					if (!$connect->connect_errno)
						$this->sql[] = (!$connect ? null : $connect);
				}
			}
		}
		else $this->sql = null;
	}
	
	public function Server ($num): void
	{
		$this->ServerId = ($num-1);
	}
	
	public function query ($query, $type1 = 'r')
	{
		if (!empty (@$this->sql [$this->ServerId]))
		{
			if ($type1 == 'q') return (@$this->sql [$this->ServerId]->query ($query) ? true : false);
			elseif ($type1 == 'r' && ($status = $this->sql [$this->ServerId]->query ($query)))
			{
				$result = [];
				
				while ($row = (array)$status->fetch_object()) if (is_countable ($row)) $result[] = $row;
				
				return (count ($result) > 0 ? $result : false);
			}
		}
		
		return false;
	}
	
	public function IsTable (string $table): bool
	{
		return (@$this->sql [$this->ServerId]->query ("SELECT * FROM {$table} LIMIT 0") ? true : false);
	}
	
	public function count_row (string $table): int
	{
		return mysqli_num_rows (mysqli_query ($this->sql [$this->ServerId], "SELECT COUNT(*) as count FROM {$table})"));
	}
	
	public function close (): void
	{
		for ($i = 0; $i < count ($this->sql); $i++)
		{
			$_ret = $this->sql [$i];
			
			if (!empty ($_ret))
			{
				$_ret->close ();
				unset ($this->sql [$i]);
			}
		}
		
		unset ($this->sql);
	}
}

?>