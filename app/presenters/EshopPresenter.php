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
	
	protected function createComponentEshopGamesOrdersForm($name) {
		return new Frm\EshopGamesOrdersForm($this, $name);
	}
	
	public function createComponentJsGame()
	{
			$files = new \WebLoader\FileCollection(WWW_DIR . '/js');                                       
							 //$files->addRemoteFile('http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js');
			$files->addFiles(array(
				'eshop/game.js'));

			$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
			$compiler->addFilter(function ($code) {
				$packer = new JavaScriptPacker($code, "None");
				return $packer->pack();
			});
	return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}


}
