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
use POSComponent\Stream\UserStream\UserStream;

class OnePagePresenter extends BasePresenter {

	/**
	 * @var \POS\Model\StreamDao
	 * @inject
	 */
	public $streamDao;

	/**
	 * @var \POS\Model\UserGalleryDao
	 * @inject
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\UserImageDao
	 * @inject
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\ConfessionDao
	 * @inject
	 */
	public $confessionDao;

	/**
	 * @var \POS\Model\StatusDao
	 * @inject
	 */
	public $statusDao;

	/** @var \Nette\Database\Table\Selection Všechny příspěvky streamu. */
	public $dataForStream;
	private $count = 0;
	private $userID;

	public function actionDefault() {
		$this->dataForStream = $this->streamDao->getAll("DESC");
		$this->count = $this->dataForStream->count("id");
		$this->userID = $this->getUser()->getId();
	}

	public function renderDefault() {
		$this->template->count = $this->count;
		$this->template->userID = $this->userID;
		$this->template->profileGallery = $this->userGalleryDao->findProfileGallery($this->userID);
	}

	protected function createComponentUserStream() {
		return new UserStream($this->dataForStream, $this->statusDao, $this->streamDao, $this->userGalleryDao, $this->userImageDao, $this->confessionDao);
	}

	public function createComponentJs() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js');
		$files->addFiles(array(
			'stream.js',
			'nette.ajax.js'
		));
		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new JavaScriptPacker($code, "None");
			return $packer->pack();
		});
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	public function createComponentSearch() {
		$component = new Search();
		return $component;
	}

}
