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
use Exception;

class FeatureContext extends MinkContext {

	/** @var \SystemContainer|Container */
	protected $context;

	/** @var UserManager @inject */
	private $userManager;

	/** @var DatabaseManager @inject */
	private $databaseManager;

	/** @var ScreenshotManager @inject */
	private $screenshotManager;

	/** @var MailManager @inject  */
	private $mailer;

	/** @var string  */
	private $lastEmail;

	/**
	 * Initialization of context
	 */
	public function __construct() {
		$this->context = $GLOBALS['container'];
		//$this->context->callInjects($this);//lepsi zpusob s DI - podivat se na to spolecne
		$this->userManager = $this->context->userManager;
		$this->databaseManager = $this->context->databaseManager;
		$this->screenshotManager = $this->context->screenshotManager;
		$this->mailer = $this->context->getService('nette.mailer');
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
		$databaseManager->featureStartScripts(); //usage of end sql scripts
	}

	/**
	 * Called after every feature
	 *  @AfterFeature */
	public static function teardownFeature() {
		$databaseManager = DatabaseManager::getInstance();
		$databaseManager->featureEndScripts(); //usage of end sql scripts
	}

	/**
	 * Sends test header to Mink, so Mink`s Nette can indicate testing
	 * @BeforeScenario
	 */
	public function sendTestInfo() {
		$this->getSession()->setRequestHeader('X-Testing', '1');
		$this->getSession()->setRequestHeader('X-Requested-With', 'Behat');
	}

	/**
	 * Clears emails
	 * @BeforeScenario
	 */
	public function clearEmails() {
		$mailManager = MailManager::getInstance();
		$mailManager->clearEmails(); //clearing email folder
	}

	/**
	 * Clears sessions after scenario
	 * @AfterScenario
	 */
	public function clearSessions() {
		$this->userManager->clearSession();
	}

	/**
	 * Sets cookie of virtual browser
	 * @param String $name
	 * @param array $value
	 */
	private function setBrowserCookie($name, $value) {
		$minkSession = $this->getSession(); //session of Mink browser
		$minkSession->setCookie($name, $value);
		$minkSession->reload();
	}

	//STEP DEFINITIONS
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
		$this->iAmOnHomepage();
		$this->userManager->loginWithEmail($username);
		$session = $this->userManager->getSession();
		$sessionId = $this->userManager->getSessionId();
		$this->setBrowserCookie($session->getName(), $sessionId);
	}

	/**
	 * @Then /^I look on the page$/
	 */
	public function iLookOnThePage() {
		$html = $this->getSession()->getDriver()->getContent();

		throw new PendingException($this->screenshotManager->saveHtml($html));
	}

	/**
	 * When email should arrive
	 * @Then /^I should receive( an)? email$/
	 */
	public function iShouldReceiveAnEmail() {
		$this->lastEmail = $this->mailer->getLastEmail();
		if ($this->lastEmail == NULL) {
			throw new Exception('No email arrived.');
		}
	}

	/**
	 * 	When email should NOT arrive
	 * @Then /^I should not receive( another)? email$/
	 */
	public function iShouldNotReceiveAnEmail() {
		$this->lastEmail = $this->mailer->getLastEmail();
		if ($this->lastEmail != NULL) {
			throw new Exception('An email arrived, but it should not.');
		}
	}

	/**
	 * When last arrived email should contains something
	 * @Then /^I should see "([^"]*)" in last email$/
	 */
	public function iShouldSeeInLastEmail($content) {
		if ($this->lastEmail == NULL) {
			throw new Exception('No email arrived. Perhaps you did not used "I should receive an email" before?');
		}
		if (strpos($this->lastEmail, $content) !== FALSE) {
			//content was found
		} else {
			throw new Exception('Received email does not contains ' . $content);
		}
	}

	/**
	 * @When /^I follow( the)? link from last email$/
	 */
	public function followEmailLink() {
		if ($this->lastEmail === NULL) {
			throw new Exception('No email arrived. Perhaps you did not used "I should receive an email" before?');
		}
		$matches = array();
		$baseUrl = $this->getMinkParameter('base_url');
		$cleanBaseUrl = rtrim($baseUrl, '/'); //url without whitespaces on the end
		$quoted = preg_quote($cleanBaseUrl, '#'); //escaping for regular expression
		if (!preg_match("#" . $quoted . "(/[^\\s\"\\.,]*([\\.,][^\\s\"\\.,]+)*)#", $this->lastEmail, $matches)) {
			throw new Exception(
			"There is no link to this website in the email. (Links to other website are ignored.)"
			);
		}
		$link = $matches[1]; //first link
		$this->getSession()->visit($this->locatePath($link));
	}

}
