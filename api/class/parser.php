<?php
class Parser
{   
    function __construct()
    {
    }

    function load($url)
    {
        if (empty($url)) return false;

        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $html = curl_exec($ch);
        curl_close($ch);            

        $dom = new DOMDocument();         
        
        @$dom->loadHTML($html);
        
        return new DOMXPath($dom);
    }
}
?>