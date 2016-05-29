#!/bin/php
<?php
/*
 *   A personal Script made by Aidan Law <Afl@Aidan-Law.com>
 *   Uses the CloudFlare API in order to create a Dynamic DNS System
 *   Uses icanhazip.com to figure out current external IP
 *   Assumes All A Records will use the same IP address (V4)
 */

$domains = array("aidan-law.com", "netalert.xyz");

class cloudflareDynamicDNS {
	public $loginEmail = "";
	public $apiKey = "";
	protected $zoneIds = array();
	protected $currentIP = "";
	protected $savedIP = "";

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
			$this->zoneIds[$domain] = $zoneData['result'][0]['id'];
		}
		else {
			throw new exception("Bad Zone Data: ".print_r($zoneData));
		}
		return $zoneData['result'][0]['id'];
	}

	/* Sends a request to icanhazip.com to get current IP */
	function getCurrentIP(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://icanhazip.com");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$rawIP = trim(curl_exec($ch));
		if($rawIP == false){
			throw new exception("Unable to Recieve Current IP Address");
		}
		else {
			$this->currentIP = $rawIP;
		}
		return $rawIP;
	}

	function getSavedIP($domain){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones/{$this->zoneIds[$domain]}/dns_records?type=A&name={$domain}");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"X-Auth-Email: {$this->loginEmail}",
			"X-Auth-Key: {$this->apiKey}",
			"Content-Type: application/json"
		));
		$rawRecord = curl_exec($ch);
		if($rawRecord == false){
			throw new exception("Unable to Recieve Record Data");
		}
		curl_close($ch);
		$record = json_decode($rawRecord, true);
		if(isset($record['result'][0]['content'])){
			$this->savedIP = $record['result'][0]['content'];
		}
		else {
			throw new exception("Bad Record Data: ".print_r($record));
		}
		return $record['result'][0]['content'];
	}

	function updateRecords(){

	}

}
	$cf = new cloudflareDynamicDNS;
	$cf->getZoneIds("aidan-law.com");
	$cf->getZoneIds("netalert.xyz");
	$currentIP = $cf->getCurrentIP();
	$savedIP = $cf->getSavedIP("aidan-law.com");
	if($currentIP !== $savedIP){

	}
	else {
		echo $currentIP."\r\n";
		echo "Current IP is the same as Saved IP!";
		exit(1);
	}

?>
