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
use Behat\Behat\Exception\HtmlWarningException;
use \Behat\Behat\Event\StepEvent;
use POS\Model\TestDao;
use Exception;
use Nette\Utils\Json;

class FeatureContext extends MinkContext {

	/** @var \SystemContainer|Container */
	protected $context;

	/** @var UserManager @inject */
	private $userManager;

	/** @var DatabaseManager @inject */
	private $databaseManager;

	/** @var ScreenshotManager @inject */
	private $screenshotManager;

	/** @var \POS\Model\UserImageDao */
	private $userImageDao;

	/** @var \POS\Model\StreamDao */
	public $streamDao;

	/** @var \POS\Model\ConfessionDao */
	public $confessionDao;

	/** @var MailManager @inject  */
	private $mailer;

	/** @var string  */
	private $lastEmail;

	/** @var TestDao */
	private $testDao;

	/** @var \POS\Model\PaymentDao */
	private $paymentDao;

	/**
	 * Initialization of context
	 */
	public function __construct() {
		$this->context = $GLOBALS['container'];
		$this->userManager = $this->context->userManager;
		$this->databaseManager = $this->context->databaseManager;
		$this->screenshotManager = $this->context->screenshotManager;
		$this->userImageDao = $this->context->userImageDao;
		$this->streamDao = $this->context->streamDao;
		$this->confessionDao = $this->context->confessionDao;
		$this->mailer = $this->context->getService('nette.mailer');
		$this->testDao = $this->context->testDao;
		$this->paymentDao = $this->context->paymentDao;
	}

	/*	 * ***********************HOOKS****************************** */

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
		$this->getSession()->setRequestHeader('Accept-Language', 'cs-CZ,cs;q=0.8,en;q=0.6');
		$this->getSession()->setRequestHeader('Accept-Encoding', 'gzip,deflate,sdch');
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

	/*	 * ***********************BASE****************************** */

	/**
	 * Sets cookie of virtual browser
	 * @param String $name name of cookie
	 * @param array $value value of cookie
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
	public function iAmSignedInAs($email) {

		$this->iAmOnHomepage();
		$this->userManager->loginWithEmail($email);
		$session = $this->userManager->getSession();
		$sessionId = $this->userManager->getSessionId();
		$this->setBrowserCookie($session->getName(), $sessionId);
	}

	/**
	 * @Then /^I look on the page$/
	 */
	public function iLookOnThePage() {
		$html = $this->getSession()->getDriver()->getContent();
		$baseUrl = $this->getMinkParameter('base_url');
		$msg = '<a href="' . $this->screenshotManager->getHtmlLink($html, $baseUrl) . '">' . $baseUrl . '</a>';
		throw new HtmlWarningException($msg);
	}

	/**
	 * @Then /^I look on the url$/
	 */
	public function iLookOnTheUrl() {
		throw new PendingException($this->getSession()->getCurrentUrl());
	}

	/**
	 * @Then /^I look on( the) status code$/
	 */
	public function iLookOnTheStatusCode() {
		throw new PendingException($this->getSession()->getStatusCode());
	}

	/*	 * ***********************EMAILY****************************** */

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
	 * Odpověď dekóduje z formátu JSON
	 * @return JSON
	 */
	private function getResponseJSON() {
		$response = $this->getSession()->getDriver()->getContent();
		return Json::decode($response);
	}

	/**
	 * @Given /^Is not empty JSON$/
	 */
	public function isNotEmptyJson() {
		$json = $this->getResponseJSON();

		if (empty($json)) {
			throw new Exception("JSON is empty.");
		}
	}

	/**
	 * @Then /^Is empty JSON$/
	 */
	public function isEmptyJson() {
		$json = $this->getResponseJSON();

		if (!empty($json)) {
			throw new Exception("JSON is not empty.");
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

	/*	 * ***********************AJAX****************************** */

	/**
	 * @Given /^I am testing ajax$/
	 */
	public function iAmTestingAjax() {
		$this->getSession()->setRequestHeader('X-Requested-With', 'XMLHttpRequest');
	}

	/**
	 * Pošle na danou url daná data pomocí postu s testovacími hlavičkami
	 * @param string $url celá url
	 * @param array $data data v poli
	 * @throws Exception výjimka při selhání
	 */
	private function sendPostRequest($url, $data) {
		$sessionName = $this->userManager->getSession()->getName();
		$sessid = $this->getSession()->getCookie($sessionName); //zjištování id sešny kvůli přihlášení
		$ch = curl_init();
		//open connection
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_COOKIE, $sessionName . '=' . $sessid);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'X-Requested-With: XMLHttpRequest',
			'X-Testing: 1'
		));
		//execute post
		$result = curl_exec($ch);

		if (!$result) {
			throw new Exception("Sending request failed.");
		}
	}

	/*	 * ***********************CHAT****************************** */

	/**
	 * Pošle POST požadavek tak, jako kdyby se odesílala zpráva do chatu
	 * @Given /^I send chat message "([^"]*)" to "([^"]*)"$/
	 */
	public function iSendChatMessageTo($text, $idUser) {
		$url = $this->getMinkParameter('base_url') . '?do=chat-communicator-sendMessage';
		$data = array(
			"to" => $idUser, 'type' => 'textMessage', 'text' => $text, 'lastid' => 0
		);
		$json = json_encode($data);
		$this->sendPostRequest($url, $json);
	}

	/**
	 * @Then /^Is in JSON$/
	 */
	public function isInJson() {
		$this->getResponseJSON();

		if (json_last_error() != JSON_ERROR_NONE) {
			throw new Exception('Response must return JSON, but it returned: ' . $response);
		}
	}

	/**
	 * @Then /^I read messages in response$/
	 */
	public function iReadMessagesInResponse() {
		$readRequestString = '&chat-communicator-readedmessages='; //sestavení proměnné v url
		$responseArray = $this->getResponseJSON();
		if (empty($responseArray)) {//ošetření prázdné odpovědi
			return;
		}
		$glue = '[';
		foreach ($responseArray as $usersArray) {//projetí všech zpráv a jejich id
			if (!empty($usersArray) && !empty($usersArray->messages)) {
				foreach ($usersArray->messages as $message) {
					$readRequestString = $readRequestString . $glue . $message->id;
					$glue = '%2C';
				}
			}
		}
		$readRequestString = $readRequestString . ']';
		$this->getSession()->visit($this->locatePath('/?do=chat-communicator-refreshMessages&chat-communicator-lastid=1' . $readRequestString));
	}

	/**
	 * Projde, pokud se platícímu uživateli v odpovědi vrátí daný text a neplatícímu ne.
	 * @Then /^just paying users response should contain "([^"]*)"$/
	 */
	public function justPayingResponseContains($text) {
		$myId = $this->userManager->getMyId();
		if (empty($myId)) {
			throw new PendingException('This works only when user is signed in.');
		}

		$isPaying = $this->paymentDao->isUserPaying($myId);
		if ($isPaying) {
			$this->assertSession()->responseContains($this->fixStepArgument($text));
		} else {
			$this->assertSession()->responseNotContains($this->fixStepArgument($text));
		}
	}

	/**
	 * Projde, pokud se platícímu uživateli v odpovědi vrátí daný text a neplatícímu ne.
	 * @Then /^just paying users should see "([^"]*)"$/
	 */
	public function justPayingSee($text) {
		$myId = $this->userManager->getMyId();
		if (empty($myId)) {
			throw new PendingException('This works only when user is signed in.');
		}
		$isPaying = $this->paymentDao->isUserPaying($myId);
		if ($isPaying) {
			$this->assertSession()->pageTextContains($this->fixStepArgument($text));
		} else {
			$this->assertSession()->pageTextNotContains($this->fixStepArgument($text));
		}
	}

	/*	 * *********************DATABAZE**************************** */

	/**
	 * @Given /^there should be "([^"]*)" in column "([^"]*)" in "([^"]*)"$/
	 */
	public function thereShouldBeInColumnIn($data, $column, $table) {
		$rows = $this->testDao->getFromTableWithColumn($data, $column, $table);
		if (!$rows->fetch()) {
			throw new Exception('There should be "' . $data . '" in column "' . $column . '" in table "' . $table . ', but it was not');
		}
	}

	/**
	 * @Given /^there should not be "([^"]*)" in column "([^"]*)" in "([^"]*)"$/
	 */
	public function thereShouldNotBeInColumnIn($data, $column, $table) {
		$rows = $this->testDao->getFromTableWithColumn($data, $column, $table);
		if ($rows->fetch()) {
			throw new Exception('There should not be "' . $data . '" in column "' . $column . '" in table "' . $table . ', but it was');
		}
	}

	/**
	 * @Then /^I recreate data in database$/
	 */
	public function iRecreateData() {
		$databaseManager = DatabaseManager::getInstance();
		$databaseManager->featureStartScripts(); //usage of end sql scripts
	}

	/*	 * **************************SOUBORY********************************** */

	/**
	 * @When /^Approve last image$/
	 * Schválí poslední přidaný a doposud neschválený obrázek.
	 * Postupně lze touto metodou schválit všechny obrázky v DB
	 */
	public function approveLastImage() {
		$image = $this->userImageDao->approveLast();
		if (!empty($image)) {
			$user = $image->gallery->user;
			$this->streamDao->aliveGallery($image->galleryID, $user->id, $user->property->preferencesID);
		}
	}

	/**
	 * @When /^I attach to "([^"]*)" the file "([^"]*)"$/
	 */
	public function iAttachToTheFile($field, $path) {
		/* nastavení pro soubory */
		$absoluteBasePath = __DIR__ . "/../" . "features/files/";

		parent::attachFileToField($absoluteBasePath . $path, $field);
	}

	/**
	 * Překrytí fce pro nahrávání souborů
	 * @param type $field
	 * @param type $path
	 */
//	public function attachFileToField($field, $path) {
//
//	}
//	/**
//	 * @When /^I attach to  "([^"]*)" the file "([^"]*)"$/
//	 */
//	public function iAttachToTheFile($field, $path) {
//		$absoluteBasePath = APP_DIR . "/test/features/files/";
//
//		$field = $this->fixStepArgument($field);
//		$this->getSession()->getPage()->attachFileToField($field, $absoluteBasePath . $path);
//
////		throw new PendingException($absoluteBasePath . $path);
////
//		$this->attachFileToField($field, $absoluteBasePath . $path);
//	}
}
