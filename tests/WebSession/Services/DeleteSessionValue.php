<?php

use PhpPlatform\Session\Factory;

include_once dirname(__FILE__).'/../../../index.php';

$session = Factory::getSession();

$session->set($_REQUEST['key'], null);
