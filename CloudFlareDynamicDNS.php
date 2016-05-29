<?php

/*
 *   A personal Script made by Aidan Law <Afl@Aidan-Law.com>
 *   Uses the CloudFlare API in order to create a Dynamic DNS System
 *   Requires DIG a Unix utility to figure out the server IP
 *   Assumes All A Records will use the same IP address
 */

$domains = array("aidan-law.com", "netalert.xyz");

class cloudflareDynamicDNS {
	public $loginEmail = "";
	public $apiKey = "";
	public $zoneIds = array();
	public $currentIP = "";

	/* Pass Domains to get Zone Id needed to change Records */
	function getZoneIds($domain){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones?name={$domain}");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"X-Auth-Email: {$this->loginEmail}",
			"X-Auth-Key: {$this->apiKey}",
			"Content-Type: application/json"
		));
		$zoneRaw = curl_exec($ch);
		if($zoneRaw == false){
			throw new exception("Unable to Recieve Zone Data");
		}
		curl_close($ch);
		$zoneData = json_decode($zoneRaw, true);
		if(isset($zoneData['result'][0]['id'])){
			$this->zoneIds[] = $zoneData['result'][0]['id'];
		}
		else {
			throw new exception("Bad Zone Data: ".print_r($zoneData));
		}
	}

	/* Uses the Unix utility Dig to reverse resolve the current server IP */
	function getCurrentIP(){
		$digOutput = trim(shell_exec("dig +short myip.opendns.com @resolver1.opendns.com"));
		if($digOutput == null){
			throw new exception("Error Occured When Calling DIG");
		}
		else {
			$this->currentIP = $digOutput;
		}
	}
}
	$cf = new cloudflareDynamicDNS;
	$cf->getZoneIds("aidan-law.com");
	$cf->getZoneIds("netalert.xyz");
	$cf->getCurrentIP();
	var_dump($cf->currentIP);
?>
