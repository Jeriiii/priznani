<?php

require __DIR__ . '/../../vendor/autoload.php';

require __DIR__ . '/../Tester/bootstrap.php';
require __DIR__ . '/../Tester/Runner/PhpInterpreter.php';
require __DIR__ . '/../Tester/Runner/ZendPhpInterpreter.php';
require __DIR__ . '/../Tester/Runner/HhvmPhpInterpreter.php';

define('APP_DIR', __DIR__ . '/../../app');
define('TEMP_DIR', __DIR__ . '/../../temp');

date_default_timezone_set('Europe/Prague');

Tester\Environment::setup();
$configurator = new Nette\Configurator;
$configurator->setDebugMode(FALSE);
$configurator->setTempDirectory(TEMP_DIR);
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->addDirectory(__DIR__ . '/../../vendor/others')
	->register();
$configurator->addConfig(APP_DIR . '/config/config.neon');
$configurator->addConfig(APP_DIR . '/config/localeconection.neon');

return $configurator->createContainer();
