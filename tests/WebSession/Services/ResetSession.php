<?php

use PhpPlatform\Session\Factory;

include_once dirname(__FILE__).'/../../../index.php';

$session = Factory::getSession();

header("MyTestHeader:value");
header("Set-Cookie: c1=v1; path=/mypath/");
setcookie("c2","v2");

$session->reset($_REQUEST['flag']);