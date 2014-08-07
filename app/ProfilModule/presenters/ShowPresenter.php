<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace ProfilModule;

use Nette\Application\UI\Form as Frm;
use POSComponent\Galleries\UserGalleries\UserGalleries;
use POSComponent\Galleries\UserImagesInGallery\UserImagesInGallery;
use POSComponent\Stream\ProfilStream;
use POSComponent\UserInfo\UserInfo;

class ShowPresenter extends ProfilBasePresenter {

	/**
	 * @var int ID uživatele, jehož profil je zobrazován
	 */
	private $userID;

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
	 * @var \POS\Model\StreamDao
	 * @inject
	 */
	public $streamDao;

	/**
	 * @var \POS\Model\ConfessionDao
	 * @inject
	 */
	public $confessionDao;
	public $dataForStream;

	/**
	 * metoda nastavuje hodnoty predavanych parametru predtim, nez se sablona s uzivatelskym streamem vykresli.
	 * Tyto hodnoty pak predava komponente Stream
	 * @param type $id
	 */
	public function actionDefault($id) {
		if (empty($id)) {
			$id = $this->getUser()->getId();
		}
		$this->userID = $id;
		$this->dataForStream = $this->streamDao->getUserStreamPosts($id);
	}

	/**
	 * vykresluje  uzivatelsky stream (zed s vlastnimi prispevky)
	 * @param type $id
	 */
	public function renderDefault($id) {
		if (empty($id)) {
			$id = $this->getUser()->getId();
		}

		$this->userID = $id;
		$user = $this->userDao->find($id);

		$this->template->userData = $user;
		$this->template->userID = $id;
		$this->template->count = $this->dataForStream->count("id");

		$profileGalleryID = $this->userGalleryDao->findProfileGallery($this->userID);
		$profilePhoto = $this->userImageDao->getInGallery($profileGalleryID)->fetch();
		if ($profilePhoto) {
			$this->template->hasProfilePhoto = true;
			$this->template->profilePhoto = $profilePhoto;
		} else {
			$this->template->hasProfilePhoto = false;
		}
	}

	public function actionUserImages($id) {
		if (empty($id)) {
			$id = $this->getUser()->getId();
		}
		$this->userID = $id;
	}

	/**
	 * vykresluje template se vsemi fotky uzivateli
	 * @param type $id
	 */
	public function renderUserImages($id) {
		$user = $this->userDao->find($this->userID);

		$this->template->userData = $user;
		$this->template->userID = $this->userID;
	}

	/**
	 * vykresluje informace uzivatele, pripadne partnerovi
	 * @param type $id
	 */
	public function renderUserInfo($id) {
		if (empty($id)) {
			$id = $this->getUser()->getId();
		}

		$this->userID = $id;
		$this->template->userID = $id;

		$user = $this->userDao->find($id);
		$userProperty = $this->userDao->findProperties($id);

		$this->template->userData = $user;

		$property = $userProperty->user_property;
		if ($property == 'couple' || $property == 'coupleMan' || $property == 'coupleWoman') {
			$this->template->userPartnerProfile = $this->coupleDao->getPartnerData($user->coupleID);
		}
		$this->template->mode = "listAll";
	}

	protected function createComponentUserInfo($name) {
		return new UserInfo($this->userDao, $this, $name);
	}

	/**
	 * Vykresluje uzivatelsky stream, respektive jeho prispevky k hlavnimu streamu
	 * @return \ProfilStream
	 */
	protected function createComponentProfilStream() {
		return new ProfilStream($this->dataForStream, $this->userGalleryDao, $this->userImageDao, $this->confessionDao);
	}

	/**
	 * vykresluje vsechny galerie daneho uzivatele
	 * @return \POSComponent\Galleries\UserGalleries\UserGalleries
	 */
	public function createComponentUserGalleries() {
		return new UserGalleries($this->userDao, $this->userGalleryDao);
	}

	/**
	 * vykresluje obrázky ze všech galerií daného uživatele
	 */
	protected function createComponentUserImagesAll() {
		$images = $this->userImageDao->getAllFromUser($this->userID);

		return new UserImagesInGallery($images, $this->userDao);
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
	 * formulář pro poslání zprávy
	 * @param type $name
	 * @return Frm\SendMessageForm
	 */
	protected function createComponentSendMessageForm($name) {
		return new Frm\SendMessageForm($this->chatManager, $this->userID, $this, $name);
	}

	/**
	 * WebLoader pro minifikace skriptu
	 * @return \WebLoader\Nette\JavaScriptLoader
	 */
	public function createComponentJs() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js');
		$files->addFiles(array(
			'stream.js',
			'nette.ajax.js'
		));
		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new \JavaScriptPacker($code, "None");
			return $packer->pack();
		});
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

}
