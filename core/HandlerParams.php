<?php

// Говно-код ^^
// За то без лишних условий и нагрузок :)

function GetParams ($str)
{
	$a_buffer = explode (' ', $str);
	$ab_params = [];
	
	$search = -1;
	for($i = 0; $i < count($a_buffer); $i++)
	{
		if (@$a_buffer [$i][0] == '"' || @$a_buffer [$i][0] == '\'')
		{
			if (@$a_buffer [$i][@strlen(@$a_buffer [$i])-1] == '"' || @$a_buffer [$i][@strlen(@$a_buffer [$i])-1] == '\'')
				@$ab_params[$search] .= (@strlen (@$ab_params[$search]) > 0 ? ' ' : '') . @substr (@$a_buffer [$i], 1, @strlen(@$a_buffer [$i])-2);
			else
			{
				$search = $i;
				@$ab_params[$i] = substr (@$a_buffer [$i], 1, @strlen(@$a_buffer [$i])-1);
			}
		}
		else if ($search >= 0 && @$a_buffer [$i][@strlen(@$a_buffer [$i])-1] == '"' || @$a_buffer [$i][@strlen(@$a_buffer [$i])-1] == '\'')
		{
			@$ab_params[$search] .= (@strlen (@$ab_params[$search]) > 0 ? ' ' : '') . @substr (@$a_buffer [$i], 0, @strlen(@$a_buffer [$i])-1);
			$search = -1;
		}
		else if ($search >= 0) @$ab_params[$search] .= ' ' . @$a_buffer [$i];
		else @$ab_params[$i] .= (@strlen (@$ab_params[$i]) > 0 ? ' ' : '') . @$a_buffer [$i];
	}
	
	return (count ($ab_params) > 0 ? array_values ($ab_params) : []);
}

?>