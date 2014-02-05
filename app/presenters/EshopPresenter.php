<?php

/**
 * Homepage presenter.
 *
 * Zobrayení úvodní stránky systému
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */
use Nette\Application\UI\Form as Frm;

class EshopPresenter extends BasePresenter {

	public $domain;

	public function startup() {
		parent::startup();

		$httpRequest = $this->context->httpRequest;
		$this->domain = $httpRequest
						->getUrl()
						->host;

		if (strpos($this->domain, "priznanizparby") !== false) {
			$this->setPartyMode();
		} else {
			$this->setSexMode();
		}
	}
	
	public function actionGame() {
		$this->addToCssVariables(array());
	}

	public function renderGame() {
		$this->template->games = $this->context->createEshopGames();
	}
	
	protected function createComponentEshopGameForm($name) {
		return new Frm\EshopGameForm($this, $name);
	}

}
