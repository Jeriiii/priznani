<?php

namespace ProfilModule;

use Nette\Application\UI\Form as Frm,
	Nette\ComponentModel\IContainer;
use Nette\Security\User;
use POSComponent\Galleries\UserGalleriesThumbnails\MyUserGalleriesThumbnails;
use POSComponent\UsersList\FriendRequestList;
use POS\Model\UserPositionDao;
use POS\Model\EnumPositionDao;
use POS\Model\EnumPlaceDao;
use POS\Model\UserPlaceDao;
use POSComponent\CropImageUpload\CropImageUpload;

class EditPresenter extends ProfilBasePresenter {

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var \POS\Model\CoupleDao @inject */
	public $coupleDao;

	/** @var \POS\Model\UserPropertyDao @inject */
	public $userPropertyDao;

	/** @var \POS\Model\UserGalleryDao @inject */
	public $userGalleryDao;

	/** @var \POS\Model\FriendRequestDao @inject */
	public $friendRequestDao;

	/** @var \POS\Model\StreamDao @inject */
	public $streamDao;

	/** @var \POS\Model\UserImageDao @inject */
	public $userImageDao;

	/** @var \POS\Model\EnumStatusDao @inject */
	public $enumStatusDao;

	/** @var \POS\Model\UserPositionDao @inject */
	public $userPositionDao;

	/** @var \POS\Model\EnumPositionDao @inject */
	public $enumPositionDao;

	/** @var \POS\Model\UserPlaceDao @inject */
	public $userPlaceDao;

	/** @var \POS\Model\EnumPlaceDao @inject */
	public $enumPlaceDao;

	/** @var \POS\Model\CityDao @inject */
	public $cityDao;

	/** @var \POS\Model\DistrictDao @inject */
	public $districtDao;

	/** @var \POS\Model\RegionDao @inject */
	public $regionDao;

	/** @var \POS\Model\UserCategoryDao @inject */
	public $userCategoryDao;

	/** @var \POS\Model\UserAllowedDao @inject */
	public $userAllowedDao;

	/** @var \POS\Model\FriendDao @inject */
	public $friendDao;

	/** @var ActiveRow User kterému se mají editovat data */
	protected $userData;

	/** @var \Nette\Database\Table\ActiveRow Obrázek */
	private $image;
	private $redirect;

	public function startup() {
		parent::startup();
		$this->checkLoggedIn();

		$userID = $this->getUser()->getId();
		$this->userData = $this->userDao->find($userID);
		if (!$this->userData->property) {
			$this->flashMessage("Nejdříve si vyplňte informace o sobě.");
			$this->redirect(":DatingRegistration:");
		}
	}

	public function actionFriendRequests() {

	}

	public function renderDefault() {
		$this->template->userData = $this->userData;
		$this->template->cityData = $this->cityDao->getNamesOfProperties();
	}

	public function renderUserImages() {
		$this->template->paying = $this->paymentDao->isUserPaying($this->user->id);
	}

	public function actionInappropriateProfilPhoto($imageID) {
		$this->image = $this->userImageDao->find($imageID);
		if (!$this->image) {
			$this->flashMessage('Obrázek nebyl nalezen.');
			$this->redirect(':OnePage:');
		}
	}

	public function renderInappropriateProfilPhoto($imageID) {
		$this->template->userData = $this->userData;
		$this->template->image = $this->image;
	}

	protected function createComponentFirstEditForm($name) {
		$userID = $this->getUser()->getId();
		$user = $this->userDao->find($userID);

		return new Frm\DatingEditFirstForm($this->userCategoryDao, $this->userPropertyDao, $this->userDao, $user, $user->couple, $this, $name);
	}

	protected function createComponentSecondEditForm($name) {
		return new Frm\DatingEditSecondForm($this->userCategoryDao, $this->userPropertyDao, $this->userDao, $this, $name);
	}

	protected function createComponentThirdEditForm($name) {
		return new Frm\DatingEditThirdForm($this->userCategoryDao, $this->userPropertyDao, $this->coupleDao, $this->userDao, $this->loggedUser->property, $this->loggedUser->couple, $this, $name);
	}

	protected function createComponentSettingsEditForm($name) {
		return new Frm\SettingsEditForm($this->userPropertyDao, $this->loggedUser->property, $this, $name);
	}

	public function createComponentMyUserGalleries() {
		return new MyUserGalleriesThumbnails($this->userDao, $this->userGalleryDao, $this->userAllowedDao, $this->friendDao);
	}

	protected function createComponentEditCityForm($name) {
		$property = $this->userDao->findProperties($this->getUser()->id);
		return new Frm \ EditCityForm($this->regionDao, $this->districtDao, $this->cityDao, $this->userPropertyDao, $property, $this, $name);
	}

	protected function createComponentFriendRequest($name) {
		return new FriendRequestList($this->friendRequestDao, $this->getUser()->id, $this, $name);
	}

	protected function createComponentEditPlacesPositionsForm($name) {
		return new Frm\EditPlacesPositionsForm($this->userPositionDao, $this->enumPositionDao, $this->userPlaceDao, $this->enumPlaceDao, $this->userDao, $this, $name);
	}

	/**
	 * WebLoader pro minifikace skriptu
	 * @return \WebLoader\Nette\JavaScriptLoader
	 */
	public function createComponentJs() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js');
		$files->addFiles(array(
			'slimbox2.js'
		));
		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new \JavaScriptPacker($code, "None");
			return $packer->pack();
		});
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	protected function createComponentCropProfilePhoto($name) {
		return new CropImageUpload($this->userGalleryDao, $this->userImageDao, $this->streamDao, $this, $name);
	}

	protected function createComponentStatusChangeForm($name) {
		return new Frm \ StatusChangeForm($this->userData->property, $this->enumStatusDao, $this, $name);
	}

}
