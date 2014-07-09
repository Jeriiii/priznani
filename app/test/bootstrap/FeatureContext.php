<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Class which is created for every feature
 *
 * @author Jan Kotalík
 */

namespace Test;

use Behat\MinkExtension\Context\MinkContext;
use Nette\DI\Container;
use \Behat\Behat\Exception\PendingException;
use \Behat\Behat\Event\StepEvent;

class FeatureContext extends MinkContext {

	/** @var \SystemContainer|Container */
	protected $context;

	/** @var UserManager @inject */
	private $userManager;

	/** @var DatabaseManager @inject */
	private $databaseManager;

	/**
	 * Initialization of context
	 */
	public function __construct() {
		$this->context = $GLOBALS['container'];
		//$this->context->callInjects($this);//lepsi zpusob s DI - podivat se na to spolecne
		$this->userManager = $this->context->userManager;
		$this->databaseManager = $this->context->databaseManager;
	}

	/**
	 * Called once before testing. Prepares database
	 * @BeforeSuite
	 */
	public static function prepare() {
		$databaseManager = DatabaseManager::getInstance();
		$databaseManager->initScripts();
	}

	/**
	 * Called before every feature
	 * @BeforeFeature */
	public static function setupFeature() {
		$databaseManager = DatabaseManager::getInstance();
		$databaseManager->featureStartScripts();
	}

	/**
	 * Called after every feature
	 *  @AfterFeature */
	public static function teardownFeature() {
		$databaseManager = DatabaseManager::getInstance();
		$databaseManager->featureEndScripts();
	}

	/**
	 * Testing feature
	 * @Given /^It looks great$/
	 */
	public function itLooksGreat() {
		//uncomment this if you want to make test failed
		//throw new PendingException();
	}

	/**
	 * I am logged user
	 * @Given /^I am signed in as "([^"]*)"$/
	 */
	public function iAmSignedInAs($username) {
		$sessionId = $this->userManager->loginWithEmail($username);
		$session = $this->userManager->getSession();
		$this->setBrowserCookie($session->getName(), $sessionId);
	}

	/**
	 * @Then /^I look on the page$/
	 */
	public function iLookOnThePage() {
		$html = $this->getSession()->getDriver()->getContent();
		$path = 'screenshots/behat_page' . rand(0, 100) . '.html';

		file_put_contents($path, $this->modifyHtml($html));
		throw new PendingException('Vystup najdete v ' . __DIR__ . '/../' . $path);
	}

	/**
	 * Sets cookie of virtual browser
	 * @param String $name
	 * @param array $value
	 */
	private function setBrowserCookie($name, $value) {
		$minkSession = $this->getSession(); //session of Mink browser
		$minkSession->setCookie($name, $value);
	}

	/**
	 * Modify input html for changes
	 * @param String $html
	 * @return String modified input
	 */
	public function modifyHtml($html) {
		$html = str_replace('/priznani/www/cache/', __DIR__ . '/../../../www/cache/', $html);
		return $html;
	}

}
