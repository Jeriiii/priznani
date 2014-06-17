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

	/**
	 * @var \POS\Model\EshopGameDao
	 * @inject
	 */
	public $eshopGameDao;

	/**
	 * @var \POS\Model\EshopGameOrderDao
	 * @inject
	 */
	public $eshopGameOrderDao;
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
		$this->template->games = $this->eshopGameDao->getAll();
	}

	protected function createComponentEshopGamesOrdersForm($name) {
		return new Frm\EshopGamesOrdersForm($this->eshopGameOrderDao, $this, $name);
	}

	public function createComponentJsGame() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js');
		$files->addFiles(array('eshop/game.js'));

		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new JavaScriptPacker($code, "None");
			return $packer->pack();
		});
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

}
