<?php

namespace PhpPlatform\Tests\WebSession\ClientSide;

use PhpPlatform\Tests\WebSession\TestBase;
use Guzzle\Http\Client;

class TestSession extends TestBase {
	
	function testCreateSession(){
		$jsonContent = '{"name":"raaghu","children":[{"name":"shri"},{"name":"di"}],"spouse":{"name":"div"}}';
		$setCookieParams = $this->createSession($jsonContent);
		
		$this->assertTrue(isset($setCookieParams["PhpPlatformSession"]));
		$this->assertEquals("/", $setCookieParams['path']);
		$this->assertTrue($setCookieParams['HttpOnly']);
	}
	
	function testSessionValues(){
		
		$jsonContent = '{"name":"raaghu","children":[{"name":"shri"},{"name":"di"}],"spouse":{"name":"div"}}';
		$setCookieParams = $this->createSession($jsonContent);
		
		// assert session values are stored properly
		$sessionCookie = array("name"=>"PhpPlatformSession","value"=>$setCookieParams["PhpPlatformSession"]);
		$this->assertSessionContains($sessionCookie, "name", "raaghu");
		$this->assertSessionContains($sessionCookie, "children", '[{"name":"shri"},{"name":"di"}]');
		$this->assertSessionContains($sessionCookie, "spouse", '{"name":"div"}');
		
		// assert session values are not accessible outside the session
		$sessionCookie["value"] = $sessionCookie["value"]."1";
		$this->assertSessionContains($sessionCookie, "name", "");
		$this->assertSessionContains($sessionCookie, "children", '');
		$this->assertSessionContains($sessionCookie, "spouse", '');
		
	}
	
	function testDeleteSessionValue(){
		$jsonContent = '{"name":"raaghu","children":[{"name":"shri"},{"name":"di"}],"spouse":{"name":"div"}}';
		$setCookieParams = $this->createSession($jsonContent);
		
		// assert session values are stored properly
		$sessionCookie = array("name"=>"PhpPlatformSession","value"=>$setCookieParams["PhpPlatformSession"]);
		$this->assertSessionContains($sessionCookie, "name", "raaghu");
		$this->assertSessionContains($sessionCookie, "children", '[{"name":"shri"},{"name":"di"}]');
		$this->assertSessionContains($sessionCookie, "spouse", '{"name":"div"}');
		
		// delete some session values
		$client = new Client();
		$request = $client->get(APP_DOMAIN.'/'.APP_PATH.'/tests/WebSession/Services/DeleteSessionValue.php?key=name');
		$request->addCookie($sessionCookie['name'], $sessionCookie['value']);
		$client->send($request);
		
		// assert specified session values are are deleted 
		$this->assertSessionContains($sessionCookie, "name", "");
		$this->assertSessionContains($sessionCookie, "children", '[{"name":"shri"},{"name":"di"}]');
		$this->assertSessionContains($sessionCookie, "spouse", '{"name":"div"}');
		
		// delete again some session values
		$client = new Client();
		$request = $client->get(APP_DOMAIN.'/'.APP_PATH.'/tests/WebSession/Services/DeleteSessionValue.php?key=children');
		$request->addCookie($sessionCookie['name'], $sessionCookie['value']);
		$client->send($request);
		
		// assert specified session values are are deleted
		$this->assertSessionContains($sessionCookie, "name", "");
		$this->assertSessionContains($sessionCookie, "children", '');
		$this->assertSessionContains($sessionCookie, "spouse", '{"name":"div"}');
		
		// delete again again some session values
		$client = new Client();
		$request = $client->get(APP_DOMAIN.'/'.APP_PATH.'/tests/WebSession/Services/DeleteSessionValue.php?key=spouse');
		$request->addCookie($sessionCookie['name'], $sessionCookie['value']);
		$client->send($request);
		
		// assert specified session values are are deleted
		$this->assertSessionContains($sessionCookie, "name", "");
		$this->assertSessionContains($sessionCookie, "children", '');
		$this->assertSessionContains($sessionCookie, "spouse", '');
		
	}
	
	function testClearSession(){
		$jsonContent = '{"name":"raaghu","children":[{"name":"shri"},{"name":"di"}],"spouse":{"name":"div"}}';
		$setCookieParams = $this->createSession($jsonContent);
		
		// assert session values are stored properly
		$sessionCookie = array("name"=>"PhpPlatformSession","value"=>$setCookieParams["PhpPlatformSession"]);
		$this->assertSessionContains($sessionCookie, "name", "raaghu");
		$this->assertSessionContains($sessionCookie, "children", '[{"name":"shri"},{"name":"di"}]');
		$this->assertSessionContains($sessionCookie, "spouse", '{"name":"div"}');
		
		// clear session
		$client = new Client();
		$request = $client->get(APP_DOMAIN.'/'.APP_PATH.'/tests/WebSession/Services/ClearSession.php');
		$request->addCookie($sessionCookie['name'], $sessionCookie['value']);
		$response = $client->send($request);
		
		// assert session cookie value is not changed after clear
		$setCookieParams = $this->parseSetCookieParams($response->getSetCookie());
		$this->assertEquals($sessionCookie['value'], $setCookieParams[$sessionCookie['name']]);
		
		// assert all session values are are deleted
		$this->assertSessionContains($sessionCookie, "name", "");
		$this->assertSessionContains($sessionCookie, "children", '');
		$this->assertSessionContains($sessionCookie, "spouse", '');
		
	}
	
	/**
	 * @dataProvider resetSessionProvider
	 * 
	 * @param unknown $flag
	 * @param unknown $sessionData
	 */
	function testResetSession($flag,$sessionData){
		$jsonContent = '{"name":"raaghu","children":[{"name":"shri"},{"name":"di"}],"spouse":{"name":"div"}}';
		$setCookieParams = $this->createSession($jsonContent);
		
		// assert session values are stored properly
		$sessionCookie = array("name"=>"PhpPlatformSession","value"=>$setCookieParams["PhpPlatformSession"]);
		$this->assertSessionContains($sessionCookie, "name", "raaghu");
		$this->assertSessionContains($sessionCookie, "children", '[{"name":"shri"},{"name":"di"}]');
		$this->assertSessionContains($sessionCookie, "spouse", '{"name":"div"}');
		
		// reset session
		$client = new Client();
		$request = $client->get(APP_DOMAIN.'/'.APP_PATH.'/tests/WebSession/Services/ResetSession.php?flag='.$flag);
		$request->addCookie($sessionCookie['name'], $sessionCookie['value']);
		$response = $client->send($request);
		
		// assert new session id is generated
		$setCookieParamsAfterReset = $this->parseSetCookieParams($response->getSetCookie());
		$this->assertNotEquals($sessionCookie['value'], $setCookieParamsAfterReset[$sessionCookie['name']]);
		
		// assert session values with old cookie after reset
		$this->assertSessionContains($sessionCookie, "name", "");
		$this->assertSessionContains($sessionCookie, "children", '');
		$this->assertSessionContains($sessionCookie, "spouse", '');
		
		// assert session values with new cookie after reset
		$sessionCookie["value"] = $setCookieParamsAfterReset["PhpPlatformSession"];
		$this->assertSessionContains($sessionCookie, "name", $sessionData[0]);
		$this->assertSessionContains($sessionCookie, "children", $sessionData[1]);
		$this->assertSessionContains($sessionCookie, "spouse", $sessionData[2]);
		
	}
	
	function resetSessionProvider(){
		return array(
				array(0,array("","","")),
				array(1,array("raaghu",'[{"name":"shri"},{"name":"di"}]','{"name":"div"}')),
				array(2,array("","","")),
				array(3,array("raaghu",'[{"name":"shri"},{"name":"di"}]','{"name":"div"}'))
		);
	}
	
	private function createSession($jsonContent){
		$client = new Client();
		$request = $client->post(APP_DOMAIN.'/'.APP_PATH.'/tests/WebSession/Services/CreateSession.php',array("Content-Type"=>"application/json","Content-Length"=>strlen($jsonContent)),$jsonContent);
		$response = $client->send($request);
		
		$this->assertEquals(200, $response->getStatusCode());
		
		$setCookie =  $response->getSetCookie();
		$setCookieParams = $this->parseSetCookieParams($setCookie);
		
		return $setCookieParams;
	}
	
	private function parseSetCookieParams($setCookie){
		$params = explode(';', $setCookie);
		$paramsArray = array();
		foreach ($params as $param){
			$keyValues = preg_split('/=/', trim($param));
			if(count($keyValues) == 1){
				$keyValues[] = true;
			}
			if(strtolower($keyValues[0]) == "httponly"){
				$keyValues[0] = "HttpOnly";
			}
			$paramsArray[$keyValues[0]] = $keyValues[1];
		}
		return $paramsArray;
	}
	
	private function assertSessionContains($sessionCookie,$key,$value){
		$client = new Client();
		$request = $client->get(APP_DOMAIN.'/'.APP_PATH.'/tests/WebSession/Services/ReadSession.php?key='.$key);
		$request->addCookie($sessionCookie['name'], $sessionCookie['value']);
		$response = $client->send($request);
		$this->assertEquals($value, $response->getBody(true));
	}
	
}