<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Základ pro stream komponenty - nefunguje samostatně.
 *
 * @author Mario
 */

namespace POSComponent\Stream\BaseStream;

use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\ConfessionDao;
use POS\Model\UserDao;
use POS\Model\ImageLikesDao;
use POS\Model\LikeStatusDao;
use POSComponent\BaseProjectControl;
use Nette\Database\Table\Selection;
use Nette\Application\UI\Form as Frm;

class BaseStream extends BaseProjectControl {

	/** @var Nette\Database\Table\Selection */
	protected $dataForStream;

	/** @var int Jaké příspěvky se mají načítat z DB - posun od posledního vydaného příspěvku */
	protected $offset = null;

	/**
	 * @var \POS\Model\UserGalleryDao
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\ImageGalleryDao
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\ConfessionDao
	 */
	public $confessionDao;

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/**
	 * @var \POS\Model\ImageLikesDao
	 */
	public $imageLikesDao;

	/**
	 * @var \POS\Model\LikeStatusDao
	 */
	public $likeStatusDao;

	public function __construct($data, LikeStatusDao $likeStatusDao, ImageLikesDao $imageLikesDao, UserDao $userDao, UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, ConfessionDao $confDao) {
		parent::__construct();
		$this->dataForStream = $data;
		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->confessionDao = $confDao;
		$this->userDao = $userDao;
		$this->imageLikesDao = $imageLikesDao;
		$this->likeStatusDao = $likeStatusDao;
	}

	/**
	 * vykresli zaklad pro stream (zed s prispevky) a rozhodne se pro jednou z moznosti - globalni stream nebo uzivatelsky profil stream
	 * @param type $mode
	 * @param type $templateName
	 */
	public function renderBase($mode, $templateName = "baseStream.latte") {

		if ($mode == "mainStream") {
			$this->renderMainStream($templateName);
		}

		if ($mode == "profilStream") {
			$this->renderProfileStream($templateName);
		}

// Data ohledně profilového fota a jestli zobrazit/nezobrazit formulář
		$profileGalleryID = $this->userGalleryDao->findProfileGallery($this->presenter->user->id);
		$this->template->profilePhoto = $this->userImageDao->getInGallery($profileGalleryID)->fetch();

		$this->template->render();
	}

	/**
	 * Vykresli globalni stream - activity stream.
	 * @param string $templateName Jméno šablony.
	 */
	private function renderMainStream($templateName) {
		$this->setNewOffset();

		$this->template->setFile(dirname(__FILE__) . '/' . $templateName);
	}

	/**
	 * Vykresli stream na profilu.
	 * @param string $templateName Jméno šablony.
	 */
	private function renderProfileStream($templateName) {
		$this->setNewOffset();

		$this->template->setFile(dirname(__FILE__) . '/' . $templateName);
	}

	/**
	 * Metoda nastavuje novy offset pro nacitani dalsich prispevku uzivatele
	 */
	public function setNewOffset() {
// musí se nastavit i v jQuery pluginu
		$limit = 4;
		if (!empty($this->offset)) {
			$this->template->stream = $this->dataForStream->limit($limit, $this->offset);
		} else {
			$this->template->stream = $this->dataForStream->limit($limit);
		}
	}

	/**
	 * vraci dalsi data do streamu, ktere snippet appenduje
	 * @param int $offset
	 */
	public function handleGetMoreData($offset) {
		$this->offset = $offset;

		if ($this->presenter->isAjax()) {
			$this->invalidateControl('posts');
		} else {
			$this->redirect('this');
		}
	}

	/**
	 * vypsani vice fb komentaru
	 * @return \Nette\Application\UI\Multiplier
	 */
	protected function createComponentFbControl() {
		$streamItems = $this->dataForStream;

		return new \Nette\Application\UI\Multiplier(function ($streamItem) use ($streamItems) {
			return new \FbLikeAndCom($streamItems->offsetGet($streamItem));
		});
	}

	/**
	 * formulář pro nahrávání profilových fotografií
	 * @param type $name
	 * @return \Nette\Application\UI\Form\ProfilePhotoUploadForm
	 */
	protected function createComponentUploadPhotoForm($name) {
		return new Frm\ProfilePhotoUploadForm($this->userGalleryDao, $this->userImageDao, $this->streamDao, $this, $name);
	}

	/**
	 * vrací nejlepší osoby pro seznámení s uživatelem
	 * @param type $name
	 * @return \POSComponent\Search\BestMatchSearch
	 */
	protected function createComponentBestMatchSearch($name) {
		$user = $this->userDao->find($this->presenter->getUser()->id);
		$session = $this->presenter->getSession();
		return new \POSComponent\Search\BestMatchSearch($user->property, $this->userDao, $session, $this, $name);
	}

	/**
	 * možnost lajknutí uživatelské fotky na streamu
	 * @return \Nette\Application\UI\Multiplier multiplier pro dynamické vykreslení více komponent
	 */
	protected function createComponentLikeImages() {
		$streamItems = $this->dataForStream;

		return new \Nette\Application\UI\Multiplier(function ($streamItem) use ($streamItems) {
			return new \POSComponent\BaseLikes\ImageLikes($this->imageLikesDao, $streamItems->offsetGet($streamItem)->userGallery->lastImage, $this->presenter->user->id);
		});
	}

	/**
	 * možnost lajknutí uživatelského statusu na streamu
	 * @return \Nette\Application\UI\Multiplier multiplier pro dynamické vykreslení více komponent
	 */
	protected function createComponentLikeStatus() {
		$streamItems = $this->dataForStream;

		return new \Nette\Application\UI\Multiplier(function ($streamItem) use ($streamItems) {
			return new \POSComponent\BaseLikes\StatusLikes($this->likeStatusDao, $streamItems->offsetGet($streamItem)->status, $this->presenter->user->id);
		});
	}

}
