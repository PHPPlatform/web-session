<?php

include_once dirname(__FILE__).'/../vendor/autoload.php';

// delete coverage.xml if present
$coverageFile = dirname(__FILE__).'/../coverage.xml';
if(is_file($coverageFile)){
	unlink($coverageFile);
}

// copy tests/index.inc to the root of the package as index.php
$packageRootDir = dirname(__FILE__).'/../';

$index = "";

if(defined('APP_COVERAGE') && APP_COVERAGE == "true"){
	// concat tests/index.inc and resources/index.php and copy to {package_root}/index.php
	$__indexFixture = file_get_contents($packageRootDir.'/tests/index.inc');
	
	// get a temp directory to store coverage files for each request in this run
	$coverageDir = sys_get_temp_dir().'/php-platform/web-session/test-coverage/'.microtime(true);
	mkdir($coverageDir,0777,true);
	chmod($coverageDir, 0777);
	
	define('COVERAGE_DIR', $coverageDir);
	
	$__indexFixture = str_replace('COVERAGE_DIR', "'$coverageDir'", $__indexFixture);
	
	$index = $__indexFixture.$index;
	
	register_shutdown_function(function () use($coverageDir){
		// aggregate the coverage reports
		$coverageFiles = array_diff(scandir($coverageDir),array('.','..'));
		
		$phpCodeCoverage = new PHP_CodeCoverage();
		
		foreach ($coverageFiles as $coverageFile){
			$coverage = include $coverageDir.'/'.$coverageFile;
			$phpCodeCoverage->merge($coverage);
		}
		
		$writer = new PHP_CodeCoverage_Report_Clover();
		$writer->process($phpCodeCoverage, 'coverage.xml');
		
	});
}

file_put_contents($packageRootDir.'/index.php', $index);
