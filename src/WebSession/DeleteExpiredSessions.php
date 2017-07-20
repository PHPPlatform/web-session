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
			
			$deletedSessions = array();
			foreach ($sessions as $sessionId=>$lastAccessTime){
				if(time() - $lastAccessTime > 2 * $sessionTimeOut){
					$sessionFileName = md5($sessionSalt.$sessionId);
					$sessionFileName = sys_get_temp_dir().'/'.$sessionFilePrefix.$sessionFileName;
					if(is_file($sessionFileName)){
						echo "Deleting $sessionFileName";
						unlink($sessionFileName);
					}
					$deletedSessions[] = $sessionId;
				}
			}
			
			if(count($deletedSessions) > 0){
				$jsonCacheFileNameRP = new \ReflectionProperty('PhpPlatform\JSONCache\Cache', 'cacheFileName');
				$cache = Cache::getInstance();
				$jsonCacheFileNameRP->setAccessible(true);
				$jsonCacheFileName = $jsonCacheFileNameRP->getValue($cache);
				
				
				try{
					//read from cache file with write lock
					$fp = fopen($jsonCacheFileName, "c+");
					$fileLock = flock($fp, LOCK_EX | LOCK_NB);
					if($fileLock){
						clearstatcache(true,$jsonCacheFileName);
						if(filesize($jsonCacheFileName) > 0){
							$contents = fread($fp, filesize($jsonCacheFileName));
						}else{
							$contents = "{}";
						}
						$settings = json_decode($contents,true);
						if($settings === NULL){
							$settings = "{}";
						}
					}else{
						// dont do anything;
					}
					
					// unset deleted sessions
					$lastAccessKeys = explode('.', $lastAccessKey);
					$lastAccessTimeForSession = &$settings;
					foreach ($lastAccessKeys as $_lastAccessKey){
						if(!array_key_exists($_lastAccessKey, $lastAccessTimeForSession)){
							$lastAccessTimeForSession[$_lastAccessKey] = array();
						}
						$lastAccessTimeForSession = &$lastAccessTimeForSession[$_lastAccessKey];
					}
					
					if(is_array($lastAccessTimeForSession)){
						foreach ($deletedSessions as $deletedSession){
							unset($lastAccessTimeForSession[$deletedSession]);
						}
					}
					
					$jsonSettings = json_encode($settings);
					if($jsonSettings === FALSE){
						throw new \Exception();
					}
					
					if($fileLock){
						ftruncate($fp,0);
						rewind($fp);
						if(fwrite($fp, $jsonSettings,strlen($jsonSettings)) === FALSE){
							throw new \Exception();
						}
					}else{
						// no lock,  so dont update the cache file this time
					}
					
				}catch (\Exception $e){
					flock($fp, LOCK_UN);
					fclose($fp);
					return FALSE;
				}
				flock($fp, LOCK_UN);
				fclose($fp);
				return TRUE;
			}
		}
	}
	
}
