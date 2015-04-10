<?php

require __DIR__ . '/../../app/ClassToTest.php';
$container = require __DIR__ . '/bootstrap.php';

use Tester\Assert;

class GreetingTest extends Tester\TestCase {

	/** @var IPresenter */
	private $container;

	public function __construct($container) {
		$this->container = $container;
	}

	public function testOne() {
		// z DI kontejneru, který vytvořil bootstrap.php, získáme instanci PresenterFactory
		$presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');

		// a vyrobíme presenter Sign
		$presenter = $presenterFactory->createPresenter('Sign');

		//http://forum.nette.org/cs/14270-testovani-presenteru-v-nette-tester

		$o = new Greeting;
		Assert::same('Hi John', $o->say('John'));
	}

}

# Spuštění testovacích metod
$testCase = new GreetingTest($container);
$testCase->run();

//use Tester\Assert;
//
//# Načteme knihovny Testeru.
//require __DIR__ . '/../../tester/Tester/bootstrap.php';  # při ruční instalaci
//# Načteme testovanou třídu. V praxi se o to zřejmě postará Composer anebo váš autoloader.
//require __DIR__ . '/../../app/ClassToTest.php';
//
//
//$o = new Greeting;
//
//Assert::same('Hi John', $o->say('John'));  # Očekáváme shodu
//
//Assert::exception(function() use ($o) {   # Očekáváme vyjímku
//	$o->say('');
//}, 'InvalidArgumentException', 'Invalid name');
