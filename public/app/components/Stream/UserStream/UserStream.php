<?php

/**
 * Stream na úvodní stránce, zobrazuje všechny příspěvky co by chtěl uživatel vidět.
 *
 * @author Mario
 */

namespace POSComponent\Stream\UserStream;

use Nette\Application\UI\Form as Frm;
use POSComponent\Stream\BaseStream\BaseStream;
use POSComponent\PhotoRating;
use NetteExt\DaoBox;

class UserStream extends BaseStream {

	/** @var \POS\Model\StreamDao */
	public $streamDao;

	/** @var \POS\Model\StatusDao */
	public $statusDao;

	/** @var \POS\Model\UsersNewsDao */
	public $usersNewsDao;

	/** @var \POS\Model\UserPositionDao */
	public $userPositionDao;

	/** @var \POS\Model\EnumPositionDao */
	public $enumPositionDao;

	/** @var \POS\Model\UserPlaceDao */
	public $userPlaceDao;

	/** @var \POS\Model\EnumPlaceDao */
	public $enumPlaceDao;

	/** @var \POS\Model\RateImageDao */
	public $rateImageDao;

	/** @var \POS\Model\ImageLikesDao */
	public $imageLikesDao;

	public function __construct($data, DaoBox $daoBox, $userData) {
		parent::__construct($data, $daoBox, $userData);

		$this->setDaos($daoBox);
	}

	private function setDaos(DaoBox $daoBox) {
		$this->streamDao = $daoBox->streamDao;
		$this->statusDao = $daoBox->statusDao;
		$this->usersNewsDao = $daoBox->usersNewsDao;
		$this->userPositionDao = $daoBox->userPositionDao;
		$this->enumPositionDao = $daoBox->enumPositionDao;
		$this->userPlaceDao = $daoBox->userPlaceDao;
		$this->enumPlaceDao = $daoBox->enumPlaceDao;
		$this->rateImageDao = $daoBox->rateImageDao;
		$this->imageLikesDao = $daoBox->imageLikesDao;
	}

	public function render() {
		$mode = 'mainStream';
		$templateName = "../UserStream/userStream.latte";
		$user = $this->presenter->user;

		if ($user->isLoggedIn()) {
			$this->showQuestion();
		}

		$this->renderBase($mode, $templateName);
	}

	/**
	 * Zobrazit dotaz na blíbenou polohu nebo pozici.
	 */
	private function showQuestion() {
		$userData = $this->loggedUser;
		$userProperty = $userData->property;
		if ($userProperty) { // ochrana proti uživatelům, co nemají vyplněné user property
			$placePosSession = $this->presenter->getSession('placePosSession');
			$placePosSession->count++;
			$this->template->placePosSession = $placePosSession;
			$placePosSession->setExpiration(0, 'password');

			$place = $this->userPlaceDao->isFilled($userProperty->id);
			$position = $this->userPositionDao->isFilled($userProperty->id);

			$this->template->place = $place;
			$this->template->position = $position;
		}
		$this->template->userData = $userData;
		$this->template->newInfo = $this->usersNewsDao->getActual($this->loggedUser->id);
	}

	/**
	 * Přidá fotky do defaultní galerie.
	 * @param string $name
	 * @return \Nette\Application\UI\Form\NewStreamImageForm
	 */
	protected function createComponentNewStreamImageForm($name) {
		return new Frm\NewStreamImageForm($this->userGalleryDao, $this->userImageDao, $this->streamDao, $this, $name);
	}

	/**
	 * Formulář na výběr pozice a místa sexu.
	 * @param string $name
	 * @return \Nette\Application\UI\Form\PlacesAndPositionsForm
	 */
	protected function createComponentPlacesAndPositionsForm($name) {
		return new Frm\PlacesAndPositionsForm($this->userPositionDao, $this->enumPositionDao, $this->userPlaceDao, $this->enumPlaceDao, $this->userDao, $this, $name);
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
		return new Frm\AddStatusForm($this->streamDao, $this->statusDao, $this->loggedUser->property, $this, $name);
	}

	protected function createComponentPhotoRating($name) {
		return new PhotoRating($this->userImageDao, $this->rateImageDao, $this->imageLikesDao, $this->loggedUser, $this, $name);
	}

	public function handleNewReaded($newID) {
		$this->usersNewsDao->deleteByUser($this->loggedUser->id, $newID);
		$this->redirect("this");
	}

}
