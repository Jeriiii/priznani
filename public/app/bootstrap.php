<?php

/**
 * My Application bootstrap file.
 */
use POS\Ext\RouteList;
use Nette\Forms\Container;
use Kdyby\BootstrapFormRenderer\DI\RendererExtension;

// Load Nette Framework
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/extendeds/bootstrap/Configurator.php';

$configurator = new POS\Ext\Configurator;

//$configurator->setDebugMode(FALSE);  // zapne produkci
// Enable Nette Debugger for error visualisation & logging
//$configurator->setProductionMode($configurator::AUTO);

$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../vendor/others')
	->register();



// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon');

$productionMode = !$configurator->isDebugMode();

$configurator->addTestConfig(__DIR__);
$configurator->addEvents($productionMode);

$container = $configurator->createContainer();

// Setup router
$router = new RouteList;
$container->router = $router;

Container::extensionMethod('addDateTimePicker', function (Container $_this, $name, $label, $cols = NULL, $maxLength = NULL) {
	return $_this[$name] = new Nette\Extras\DateTimePicker($label, $cols, $maxLength);
});
RendererExtension::register($configurator);

// Na PRODUKCI se nastaví odchytávání vyjímek
if ($productionMode) {
	$container->application->catchExceptions = TRUE;
}

return $container;

