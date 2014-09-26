<?php

/**
 * Stream na úvodní stránce, zobrazuje všechny příspěvky co by chtěl uživatel vidět.
 *
 * @author Mario
 */

namespace POSComponent\Stream\UserStream;

use Nette\Application\UI\Form as Frm;
use POSComponent\Stream\BaseStream\BaseStream;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\ConfessionDao;
use POS\Model\StreamDao;
use POS\Model\StatusDao;
use POS\Model\ImageLikesDao;
use POS\Model\UserDao;
use POS\Model\LikeStatusDao;
use POS\Model\UserPositionDao;
use POS\Model\EnumPositionDao;
use POS\Model\UserPlaceDao;
use POS\Model\EnumPlaceDao;

class UserStream extends BaseStream {

	/** @var \POS\Model\StreamDao */
	public $streamDao;

	/**
	 * @var \POS\Model\StatusDao
	 */
	public $statusDao;

	public function __construct($data, LikeStatusDao $likeStatusDao, ImageLikesDao $imageLikesDao, UserDao $userDao, StatusDao $statusDao, StreamDao $streamDao, UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, ConfessionDao $confDao, UserPositionDao $userPositionDao, EnumPositionDao $enumPositionDao, UserPlaceDao $userPlaceDao, EnumPlaceDao $enumPlaceDao) {
		parent::__construct($data, $likeStatusDao, $imageLikesDao, $userDao, $userGalleryDao, $userImageDao, $confDao, $streamDao, $userPositionDao, $enumPositionDao, $userPlaceDao, $enumPlaceDao);
		$this->streamDao = $streamDao;
		$this->statusDao = $statusDao;
	}

	public function render() {
		$mode = 'mainStream';
		$templateName = "../UserStream/userStream.latte";

		$this->renderBase($mode, $templateName);
	}

	/**
	 * Přidá fotky do defaultní galerie.
	 * @param type $name
	 * @return \Nette\Application\UI\Form\NewStreamImageForm
	 */
	protected function createComponentNewStreamImageForm($name) {
		return new Frm\NewStreamImageForm($this->userGalleryDao, $this->userImageDao, $this->streamDao, $this, $name);
	}

	/**
	 * Přidání přiznání do streamu
	 * @param string $name
	 * @return \Nette\Application\UI\Form\AddItemForm
	 */
	protected function createComponentAddConfessionForm($name) {
		$addItem = new Frm\AddItemForm($this, $name);
		$addItem->setConfession($this->confessionDao);
		return $addItem;
	}

	protected function createComponentFilterForm($name) {
		return new Frm\FilterStreamForm($this, $name);
	}

	protected function createComponentStatusForm($name) {
		return new Frm\AddStatusForm($this->streamDao, $this->statusDao, $this, $name);
	}

}
