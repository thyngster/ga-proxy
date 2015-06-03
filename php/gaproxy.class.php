<?php
/**
 * Universal Analytics Proxy
 * Prevent Bots, and Hide your real Property ID. 
 * @package  GaProxy
 * @author   David Vallejo <thyngster@gmail.com> @thyng
 * @version 1.0
*/

class GaProxy {

    // Configuration Start
    // Set your real Property Nmber where you want to redirect the data
    private $property_id = 'UA-XXXX-Y';
    // This will be attached to the hostname value, so we can then filter any hit not coming from this script 
    private $filterHash = 'dontspamme';
    // set this to true, if you want to remove the last Ip's Octet
    private $anonymizeIp = false;
    // Configuration End
    public $payload;
    // We are setting the jail for the visitos, a PHP is started and the session_id is saved into a session variable
    // When collect.php is loaded we will check the current session_id agains this value, and if it doesn't match we'll abort sending the hit to Google Analytics
    function setupProxy()
    {
	session_start();
	$_SESSION["gajail_session_id"] = session_id();
	$_SESSION["gajail_current_url"] =  $_SERVER['REQUEST_URI'];
    }

    // We'll build the hit on there, adding the current user IP address to keep the Geolocation reports, and the user agent
    // Again, we'll setup out real filtered UA property in here. An attacker will not be able to guess our real UA account.
    function setupHit()
    {
	$this->payload = $_GET;
	$this->payload["uip"] = $this->getIpAddress();
	$this->payload["ua"] = $this->getUserAgent();
	$this->payload["tid"] = $this->property_id;
	$this->payload["dh"] = $this->filterHash.'-'.$this->getRequestHostName();
	
    }

    // This will be used to add more antispam mechanism in a future.
    function checkRequestHeaders()
    {
	// TODO
	// Check User Agent Format : bots, malformed user agents, etc
        // Check Againts spammers blacklist IP's
        // Check throttling
    }

    // Gets the current loading user agent
    function getUserAgent()
    {
	return $_SERVER["HTTP_USER_AGENT"];
    }

    // Gets the current hostnamea
    function getRequestHostName()
    {
	return $_SERVER["SERVER_NAME"];
    }

    // Gets the current loading Ip Address
    function getIpAddress()
    {
	$ipAddress = $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']);
	if($this->anonymizeIp == true)
		return preg_replace('/\.\d{1,3}$/', '.0', $ipAddress);
	else
		return $ipAddress;
   }

    // This function will care of building the hit payload and sending it to Universal Analytics endpoint
    function sendHit()
    {
	session_start();
	$this->setupHit();
	if($_SESSION["gajail_session_id"] == session_id())
	{
		$context = stream_context_create(array('http' => array(
		    'method'    => 'GET',
	 	    'header'=>"Accept-language: ".$this->payload["ul"]."\r\n" .
               		      "User-Agent: ".$this->payload["ua"]."\r\n"
	   	    )));

	 	$url = 'http://www.google-analytics.com/collect?'.http_build_query($this->payload,'','&');
		$fp = fopen($url, 'r', false, $context);
	}
        //  Used for debug
	// else
	//{
	//	die("ABORTING HIT");
	//}
	// Return a 1x1 Gif Pixel
        // Not sure if creating it using base64 is the fastest way, it may need improvement
	header('Content-Type: image/gif');
	echo base64_decode("R0lGODdhAQABAIAAAPxqbAAAACwAAAAAAQABAAACAkQBADs=");
	die();
    }
}
?>