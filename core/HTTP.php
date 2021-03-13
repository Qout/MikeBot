<?php

class HTTP
{
	public function POST ($url, $params, $header = null)
	{
        if (!($curl = curl_init($url))) return false;
        
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		if ($header != null) curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		
        $response = curl_exec($curl);
        curl_close($curl);
		
        return $response;
    }
	
	public function GET ($url, $header = null)
	{
        if (!($curl = curl_init($url))) return false;
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		if ($header != null) curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		
        $response = curl_exec($curl);
        curl_close($curl);
		
        return $response;
    }
}

?>