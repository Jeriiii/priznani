<?php

/**
 * My Application bootstrap file.
 */
use Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Forms\Container;

// Load Nette Framework
require __DIR__ . '/../vendor/Nette/loader.php';


$configurator = new Nette\Configurator;

$configurator->setDebugMode(TRUE);  // debug mode MUST NOT be enabled on production server
// Enable Nette Debugger for error visualisation & logging
//$configurator->setProductionMode($configurator::AUTO);
$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../vendor/others')
	->register();

//pokud se automaticky testuje
$testing = isset($_SERVER['TESTING']) && $_SERVER['TESTING'];

if ($testing) {
	$configurator->addConfig(__DIR__ . '/config/test.config.neon');
} else {
	// Create Dependency Injection container from config.neon file
	$configurator->addConfig(__DIR__ . '/config/config.neon');
}

$container = $configurator->createContainer();

// Setup router
$router = new RouteList;

$container->router = $router;
$router[] = new Route('index.php', 'OnePage:default', Route::ONE_WAY);
//$router[] = new Route('//[www.]priznanizparty.cz/[/<presenter>/<url>]', array(
//    'presenter' => 'Page',
//    'action' => 'default',
//	'url' => 'priznani-z-party'
//));
//$router[] = new Route('//[www.]priznanizparby.cz/[/<presenter>/<url>]', array(
//    'presenter' => 'Page',
//    'action' => 'default',
//	'url' => 'priznanizparby'
//));
//$router[] = new Route('//priznaniosexu.cz/seznamka[/<presenter>/<url>]', array(
//    'presenter' => 'Page',
//    'action' => 'default',
//	'url' => 'seznamka'
//));
//$router[] = new Route('//priznaniosexu.cz/[/<presenter>/<url>]', array(
//    'presenter' => 'Page',
//    'action' => 'default',
//	'url' => 'priznani-o-sexu'
//));
//$router[] = new Route('//priznaniosexu.cz/poradna/[/<presenter>/<url>]', array(
//    'presenter' => 'Page',
//    'action' => 'default',
//	'url' => 'poradna-o-sexu'
//));
//$router[] = new Route('//priznaniosexu.cz/priznani/<id>', array(
//    'presenter' => 'Page',
//    'action' => 'confession',
//	'id' => '<id>'
//));
//$router[] = new Route('//priznaniosexu.cz/poradna/<id>', array(
//    'presenter' => 'Page',
//    'action' => 'advice',
//	'id' => '<id>'
//));
$router[] = new Route('<presenter>/<action>[/<url>]', 'OnePage:default');

Container::extensionMethod('addDateTimePicker', function (Container $_this, $name, $label, $cols = NULL, $maxLength = NULL) {
	return $_this[$name] = new Nette\Extras\DateTimePicker($label, $cols, $maxLength);
});
Kdyby\BootstrapFormRenderer\DI\RendererExtension::register($configurator);

return $container;

