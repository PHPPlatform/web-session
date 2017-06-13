<?php

namespace PhpPlatform\WebSession;

use PhpPlatform\JSONCache\Cache;
use PhpPlatform\Config\Settings;

class DeleteExpiredSessions {
	
	static function run(){
		$lastAccessKeyRP = new \ReflectionProperty('PhpPlatform\WebSession\Session', '_lasAccessKey');
		$lastAccessKeyRP->setAccessible(true);
		$lastAccessKey = $lastAccessKeyRP->getValue();
		$sessions = Cache::getInstance()->getData($lastAccessKey);
		
		if(is_array($sessions)){
			$sessionFilePrefix = Settings::getSettings(Package::Name,'sessionFilePrefix');
			$sessionTimeOut    = Settings::getSettings(Package::Name,'timeout');
			$sessionSalt       = Settings::getSettings(Package::Name,'salt');
			
			foreach ($sessions as $sessionId=>$lastAccessTime){
				if(time() - $lastAccessTime > 2 * $sessionTimeOut){
					$sessionFileName = md5($sessionSalt.$sessionId);
					$sessionFileName = $sessionFilePrefix.$sessionFileName;
					if(is_file($sessionFileName)){
						unlink($sessionFileName);
					}
				}
			}
		}
	}
	
}