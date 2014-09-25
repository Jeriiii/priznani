<?php

namespace ProfilModule;

use Nette\Application\UI\Form as Frm,
	Nette\ComponentModel\IContainer;
use Nette\Security\User;
use POSComponent\Galleries\UserGalleries\MyUserGalleries;
use POSComponent\UsersList\FriendRequestList;
use POS\Model\UserPositionDao;
use POS\Model\EnumPositionDao;
use POS\Model\EnumPlaceDao;
use POS\Model\UserPlaceDao;

class EditPresenter extends ProfilBasePresenter {

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	/**
	 * @var \POS\Model\CoupleDao
	 * @inject
	 */
	public $coupleDao;

	/**
	 * @var \POS\Model\UserPropertyDao
	 * @inject
	 */
	public $userPropertyDao;

	/**
	 * @var \POS\Model\UserGalleryDao
	 * @inject
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\FriendRequestDao
	 * @inject
	 */
	public $friendRequestDao;

	/**
	 * @var \POS\Model\StreamDao
	 * @inject
	 */
	public $streamDao;

	/**
	 * @var \POS\Model\UserImageDao
	 * @inject
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\EnumStatusDao
	 * @inject
	 */
	public $enumStatusDao;

	/**
	 * @var \POS\Model\UserPositionDao
	 * @inject
	 */
	public $userPositionDao;

	/**
	 * @var \POS\Model\EnumPositionDao
	 * @inject
	 */
	public $enumPositionDao;

	/**
	 * @var \POS\Model\UserPlaceDao
	 * @inject
	 */
	public $userPlaceDao;

	/**
	 * @var \POS\Model\EnumPlaceDao
	 * @inject
	 */
	public $enumPlaceDao;

	/** @var ActiveRow User kterému se mají editovat data */
	private $userData;

	public function startup() {
		parent::startup();
		$this->checkLoggedIn();
	}

	public function actionDefault() {
		$userID = $this->getUser()->getId();
		$this->userData = $this->userDao->find($userID);
	}

	public function renderDefault() {
		$this->template->userData = $this->userData;
	}

	protected function createComponentFirstEditForm($name) {
		$userID = $this->getUser()->getId();
		$user = $this->userDao->find($userID);

		return new Frm\DatingEditFirstForm($this->userPropertyDao, $this->userDao, $user, $this, $name);
	}

	protected function createComponentSecondEditForm($name) {
		return new Frm\DatingEditSecondForm($this->userPropertyDao, $this->userDao, $this, $name);
	}

	protected function createComponentThirdEditManForm($name) {
		return new Frm\DatingEditManThirdForm($this->userPropertyDao, $this->userDao, $this, $name);
	}

	protected function createComponentThirdEditWomanForm($name) {
		return new Frm\DatingEditWomanThirdForm($this->userPropertyDao, $this->userDao, $this, $name);
	}

	protected function createComponentFourthEditWomanForm($name) {
		return new Frm\DatingEditWomanFourthForm($this->coupleDao, $this->userDao, $this, $name);
	}

	protected function createComponentFourthEditManForm($name) {
		return new Frm\DatingEditManFourthForm($this->coupleDao, $this->userDao, $this, $name);
	}

	protected function createComponentInterestedInForm($name) {
		return new Frm\InterestedInForm($this->userPropertyDao, $this->userDao, $this, $name);
	}

	public function createComponentMyUserGalleries() {
		return new MyUserGalleries($this->userDao, $this->userGalleryDao);
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

	protected function createComponentProfilePhotoForm($name) {
		return new Frm \ ProfilePhotoUploadForm($this->userGalleryDao, $this->userImageDao, $this->streamDao, $this, $name);
	}

	protected function createComponentStatusChangeForm($name) {
		return new Frm \ StatusChangeForm($this->userData->property, $this->enumStatusDao, $this, $name);
	}

}
