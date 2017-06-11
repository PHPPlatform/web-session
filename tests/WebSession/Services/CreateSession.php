<?php

use PhpPlatform\Session\Factory;

include_once dirname(__FILE__).'/../../../index.php';

$session = Factory::getSession();

$input = file_get_contents('php://input');
$input = json_decode($input,true);

foreach ($input as $key=>$value){
	$session->set($key, $value);
}