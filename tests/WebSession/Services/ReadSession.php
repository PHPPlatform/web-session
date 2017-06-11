<?php

use PhpPlatform\Session\Factory;

include_once dirname(__FILE__).'/../../../index.php';

$session = Factory::getSession();

$sessionValue = $session->get($_REQUEST['key']);

if(is_array($sessionValue)){
	echo json_encode($sessionValue);
}
if(is_string($sessionValue)){
	echo $sessionValue;
}