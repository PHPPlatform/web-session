<?php

namespace PhpPlatform\WebSession;

use PhpPlatform\Session\Session as ISession;
use PhpPlatform\JSONCache\Cache;
use PhpPlatform\Config\Settings;
use PhpPlatform\Errors\Exceptions\Application\ProgrammingError;

class Session extends Cache implements ISession{
	static private $_lasAccessKey = "php-platform.web-session.last-access";
	private $id = null;
	private static $session = null;
	private $isValid = false;
	
	protected function __construct(){
		// check if session cokiee is present in the request
		$sessionCookieName = Settings::getSettings(Package::Name,"name");
		$sessionFilePrefix = Settings::getSettings(Package::Name,'sessionFilePrefix');
		$sessionTimeOut    = Settings::getSettings(Package::Name,'timeout');
		$sessionSalt       = Settings::getSettings(Package::Name,'salt');
		$sessionPath       = Settings::getSettings(Package::Name,'path');
		$sessionSecurity   = Settings::getSettings(Package::Name,'secure');
		
		$validSession = false;
		if(array_key_exists($sessionCookieName, $_COOKIE)){
			// cookie is set
			$sessionCookie = $_COOKIE[$sessionCookieName];
			$sessionLastAccessTime = Cache::getInstance()->getData(self::$_lasAccessKey.'.'.$sessionCookie);
			
			if(isset($sessionLastAccessTime) && time() - $sessionLastAccessTime < $sessionTimeOut){
				// session not expired
				$sessionFileName = md5($sessionSalt.$sessionCookie);
				$this->cacheFileName = $sessionFilePrefix.$sessionFileName;
				$validSession = true;
			}
		}
		if(!$validSession){
			// cookie is not set OR session is expired OR cookie is invalid
			// generate new session
			$sessionLastAccessTime = time();
			while(isset($sessionLastAccessTime)){
				// generate a non-colliding session cokiee
				$sessionCookie = md5(microtime().$_SERVER['REMOTE_ADDR'].rand(1,1000));
				$sessionLastAccessTime = Cache::getInstance()->getData(self::$_lasAccessKey.'.'.$sessionCookie);
			}
			$sessionFileName = md5($sessionSalt.$sessionCookie);
			$this->cacheFileName = $sessionFilePrefix.$sessionFileName;
		}
		
		parent::__construct();
		
		// set session id
		$this->id = $sessionCookie;
		
		// update session last access time
		$this->setLastAccessTime(time());
		
		// set cookie
		$this->removeSessionSetCookieHeader($sessionCookieName);
		setcookie($sessionCookieName,$sessionCookie,time()+$sessionTimeOut,$sessionPath,"",$sessionSecurity,true);
		$this->isValid = true;
	}
	
	
	/**
	 * @return Session
	 */
	public static function getInstance() {
		if(self::$session == null){
			self::$session = new Session();
		}
		return self::$session;
	}

    public function getId() {
    	if(!$this->isValid){
    		throw new ProgrammingError('Invalid session instance, get a new instance from  PHPPlatform\Session\Factory::getSession()');
    	}
    	return $this->id;
	}

	public function get($key) {
		if(!$this->isValid){
			throw new ProgrammingError('Invalid session instance, get a new instance from  PHPPlatform\Session\Factory::getSession()');
		}
		return parent::getData($key);
	}

	public function set($key, $value) {
		if(!$this->isValid){
			throw new ProgrammingError('Invalid session instance, get a new instance from  PHPPlatform\Session\Factory::getSession()');
		}
		return parent::setData(array($key=>$value));
	}

	public function clear() {
		if(!$this->isValid){
			throw new ProgrammingError('Invalid session instance, get a new instance from  PHPPlatform\Session\Factory::getSession()');
		}
		return parent::reset();
	}

	public function reset($flag = 0) {
		if(!$this->isValid){
			throw new ProgrammingError('Invalid session instance, get a new instance from  PHPPlatform\Session\Factory::getSession()');
		}
		// create a new Session
		$sessionCookieName = Settings::getSettings(Package::Name,"name");
		unset($_COOKIE[$sessionCookieName]);
		self::$session = new Session();
		
		if($flag & self::RESET_COPY_OLD){
			self::$session->setData($this->getData(""));
		}
		
		if($flag & self::RESET_DELETE_OLD){
			$this->clear();
		}
		
		$this->setLastAccessTime(0);
		$this->isValid = false;
		
	}
	
	private function setLastAccessTime($time){
		$lastAccessTime = array($this->id=>$time);
		$cachePaths = array_reverse(explode(".", self::$_lasAccessKey));
		foreach ($cachePaths as $cachePath){
			$lastAccessTime = array($cachePath=>$lastAccessTime);
		}
		Cache::getInstance()->setData($lastAccessTime);
	}
	
	private function removeSessionSetCookieHeader($sessionName){
		$headers = headers_list();
		$cookiesToBeRestored = array();
		foreach ($headers as $header){
			if(strpos($header, "Set-Cookie:") === 0){
				// SetCookie Header
				if(strpos($header, "Set-Cookie: $sessionName=") !== 0){
					// not a session SetCookie header
					$cookiesToBeRestored[] = $header;
				}
			}
		}
		header_remove("Set-Cookie");
		foreach ($cookiesToBeRestored as $header){
			header($header,false);
		}
	}

}