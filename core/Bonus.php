<?php

function Bonus (int $amount)
{
	if (($Cfg = Cfg ('bonus')))
		 return [$Cfg->bonus_enable, $Cfg->bonus_pr, ($Cfg->bonus_enable ? ($amount + (($amount / 100) * $Cfg->bonus_pr)) : 0)];
	
	// If Bonus Disabled
	return [false, 0, 0];
}

?>