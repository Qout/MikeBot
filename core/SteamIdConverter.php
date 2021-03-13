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
        return bcadd(bcmul($accountarray[2], 2), bcadd($accountarray[1], '76561197960265728'));
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
		
        return "STEAM_{$gameid}:{$idnum}:" . number_format(bcdiv($temp, '2'), 0, '', '');
    }
}

?>