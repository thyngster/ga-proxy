<?php
/**
 * Universal Analytics Proxy
 * Prevent Bots, and Hide your real Property ID. 
 * @package  GaProxy
 * @author   David Vallejo <thyngster@gmail.com> @thyng
 * @version 1.0
*/

include_once('gaproxy.class.php');
$ga = new GaProxy();
$ga->sendHit();
?>