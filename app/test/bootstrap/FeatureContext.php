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

	/**
	 * Initialization of context
	 */
	public function __construct() {
		$this->context = $GLOBALS['container'];
		//$this->context->callInjects($this);//lepsi zpusob s DI - podivat se na to spolecne
		$this->userManager = $this->context->userManager;
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
		$this->userManager->loginWithEmail($username);
		$session = $this->userManager->getSession();
		$this->setBrowserCookie($session->getName(), $session->getId());
	}

	/**
	 * @Then /^I look on the page$/
	 */
	public function iLookOnThePage() {

		$html = $this->getSession()->getDriver()->getContent();
		throw new PendingException($html);
	}

	/**
	 * Sets cookie of virtual browser
	 * @param String $name
	 * @param array $value
	 */
	private function setBrowserCookie($name, $value) {
		$minkSession = $this->getSession(); //session of Mink browser
		$minkSession->setCookie($name, $value);
		//$this->getMainContext()
	}

}
