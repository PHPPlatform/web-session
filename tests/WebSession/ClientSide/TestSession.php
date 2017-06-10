<?php

namespace PhpPlatform\Tests\WebSession\ClientSide;

use PhpPlatform\Tests\WebSession\TestBase;
use Guzzle\Http\Client;

class TestSession extends TestBase {
	
	function testCreateSession(){
		$client = new Client();
		$jsonContent = '{"name":"raaghu","children":[{"name":"shri"},{"name":"di"}]}';
		$request = $client->post(APP_DOMAIN.'/'.APP_PATH.'/tests/WebSession/Services/CreateSession.php',array("Content-Type"=>"application/json","Content-Length"=>strlen($jsonContent)),$jsonContent);
		$response = $client->send($request);
		
		$this->assertEquals(200, $response->getStatusCode());
		$setCookie =  $response->getSetCookie();
		$setCookieeParams = $this->parseSetCookieParams($setCookie);
		
		$this->assertTrue(isset($setCookieeParams["PhpPlatformSession"]));
		$this->assertEquals("/", $setCookieeParams['path']);
		$this->assertTrue($setCookieeParams['httponly']);
		
	}
	
	private function parseSetCookieParams($setCookie){
		$params = explode(';', $setCookie);
		$paramsArray = array();
		foreach ($params as $param){
			$keyValues = preg_split('/=/', trim($param));
			if(count($keyValues) == 1){
				$keyValues[] = true;
			}
			$paramsArray[$keyValues[0]] = $keyValues[1];
		}
		return $paramsArray;
	}
	
}