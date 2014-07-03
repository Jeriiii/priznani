<?php

namespace ProfilModule;

use Nette\Application\UI\Form as Frm,
	Nette\ComponentModel\IContainer;
use POSComponent\Galleries\UserGalleries\UserGalleries;
use POSComponent\Galleries\UserImagesInGallery\UserImagesInGallery;

class ShowProfilPresenter extends ProfilBasePresenter {

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

	public function renderDefault($id) {
		if (empty($id)) {
			$id = $this->getUser()->getId();
		}

		$this->userID = $id;
		$this->template->userID = $id;

		$user = $this->userDao->find($id);
		$userProperty = $this->userDao->findProperties($id);

		$this->template->userData = $user;
		$this->template->userProfile = $this->userDao->getUserData($user->id);

		$property = $userProperty->user_property;
		if ($property == 'couple' || $property == 'coupleMan' || $property == 'coupleWoman') {
			$this->template->userPartnerProfile = $this->coupleDao->getPartnerData($user->coupleID);
		}
	}

	public function createComponentUserGalleries() {
		return new UserGalleries($this->userDao, $this->userGalleryDao);
	}

	/**
	 * vykresluje obrázky ze všech galerií daného uživatele
	 */
	protected function createComponentUserImagesAll() {
		$images = $this->userImageDao->getAllFromUser($this->userID);
		$gallery = $this->userGalleryDao->findByUser($this->userID);

		return new UserImagesInGallery($gallery->id, $images, $this->userDao);
	}

}
