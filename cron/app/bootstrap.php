<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

Tracy\Debugger::$email = 'p.kukral@nejlevnejsiwebstranky.cz';

//$configurator->setDebugMode('23.75.345.200'); // enable for your remote IP
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$environment = Nette\Configurator::detectDebugMode() ? $configurator::DEVELOPMENT : $configurator::PRODUCTION;

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon', $environment);

$container = $configurator->createContainer();

return $container;