<?php

class CSQLite3
{
	protected $sql;
	
	public function __construct ($filename)
	{
		$this->sql = new SQLite3 ($filename);
	}
	
	public function open($filename): void
	{
		$this->sql = new SQLite3 ($filename);
	}
	
	public function query($query, $type1 = 'array:both')
	{
		$type1 = explode (':', strtolower ($type1));
		if ($type1 [0] == 'q') return $this->sql->exec ($query);
		elseif (($status = $this->sql->query ($query)))
		{
			if (count ($type1) > 1)
			{
				if ($type1 [0] == 'array')
				{
					$types = ['both' => SQLITE3_BOTH, 'assoc' => SQLITE3_ASSOC, 'num' => SQLITE3_NUM];
					
					if (($data = $status->fetchArray ($types [$type1 [1]])) && is_countable ($data) && count ($data) > 0)
					{
						$result[] = $data;
						
						while ($row = $status->fetchArray($types [$type1 [1]])) if (is_countable ($row)) $result[] = $row;
						
						return (count ($result) > 0 ? $result : false);
					}
				}
			}
		}
		
		return false;
	}
	
	public function IsTable (string $table): bool
	{
		return $this->sql->exec ("SELECT * FROM {$table} LIMIT 0");
	}
	
	public function count_row(string $table): int
	{
		return $this->sql->querySingle ("SELECT COUNT(*) as count FROM {$table}");
	}
	
	public function close(): void
	{
		$this->sql->close ();
		unset ($this->sql);
	}
}

?>