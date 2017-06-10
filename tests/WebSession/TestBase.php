<?php

namespace PhpPlatform\Tests\WebSession;

use PhpPlatform\Mock\Config\MockSettings;
use PhpPlatform\Config\SettingsCache;

abstract class TestBase extends \PHPUnit_Framework_TestCase {
	
	private static $errorLogDir = null;
	
	static function setUpBeforeClass(){
		parent::setUpBeforeClass();
		
		// create a temporary error log directory
		$errorLogDir = sys_get_temp_dir().'/php-platform/web-session/errors/'.microtime(true);
		mkdir($errorLogDir,0777,true);
		chmod($errorLogDir, 0777);
		
		self::$errorLogDir = $errorLogDir;
		
		// clear caches
		SettingsCache::getInstance()->reset();
		
		// set PhpPlatform\WebSession\Session  as implenetation
		MockSettings::setSettings('php-platform/session', "session.class", 'PhpPlatform\WebSession\Session');
		
	}
	
	function setUp(){
		$errorlogFile = self::$errorLogDir.'/'. $this->getName();
		
		// create an temporary error log
		MockSettings::setSettings('php-platform/errors', 'traces', array(
				"Persistence"=>$errorlogFile,
				"Application"=>$errorlogFile,
				"Http"=>$errorlogFile,
				"System"=>$errorlogFile
		));
	}
	
	function tearDown(){
		// display error log if any
		$errorlogFile = self::$errorLogDir.'/'. $this->getName();
		if(file_exists($errorlogFile)){
			echo PHP_EOL.file_get_contents($errorlogFile).PHP_EOL;
			unlink($errorlogFile);
		}
	}
	
	function clearErrorLog(){
		$errorlogFile = self::$errorLogDir.'/'. $this->getName();
		if(file_exists($errorlogFile)){
			unlink($errorlogFile);
		}
	}
	
	function assertContainsAndClearLog($message){
		$errorlogFile= self::$errorLogDir.'/'. $this->getName();
		$log = "";
		if(file_exists($errorlogFile)){
			$log = file_get_contents($errorlogFile);
		}
		$this->assertContains($message, $log);
		unlink($errorlogFile);
	}
	
	static function tearDownAfterClass(){
		// delete error log directory
		rmdir(self::$errorLogDir);
	}
	
}