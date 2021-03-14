<?php

class SteamIDConverter
{
    public function Convert(string $id, bool $gameid = true)
    {
        return (strpos($id, 'STEAM') === false ? $this->getIDFromCommunity($id, $gameid) : $this->getCommunityFromID($id));
    }
	
    protected function getCommunityFromID($id)
    {
        $accountarray = explode(':', $id);
        if (count($accountarray) == 3
            && is_numeric($accountarray[1])
            && (int)$accountarray[1] >= 0
            && is_numeric($accountarray[2])
            && (int)$accountarray[2] > 0)
        {
            $_ret = bcadd(bcmul($accountarray[2], 2), bcadd($accountarray[1], '76561197960265728'));
            return $_ret;
        }
        else return NULL;
    }
	
    protected function getIDFromCommunity($id, bool $gameid = true)
    {
        $idnum      =   '0';
        $constant   =   '76561197960265728';
        if(bcmod($id, '2') == 0)
        {
            $idnum  =   '0';
            $temp   =   bcsub($id, $constant);
        }
        else
        {
            $idnum  =   '1';
            $temp   =   bcsub($id, bcadd($constant, '1'));
        }

        $_id = number_format(bcdiv($temp, '2'), 0, '', '');
        return (int)$_id > 0 ? "STEAM_{$gameid}:{$idnum}:{$_id}" : NULL;
    }
}

?>