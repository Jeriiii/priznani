<?php

namespace ProfilModule;

/**
 * Base presenter for all profile application presenters.
 */
use Nette\Security\User;
use Nette\Http\Request;
use Nette\Application\UI\Form as Frm;
use Navigation\Navigation;
use POSComponent\Galleries\UserGalleriesThumbnails\MyUserGalleriesThumbnails;
use POSComponent\Galleries\UserGalleriesThumbnails\UserGalleriesThumbnails;
use POSComponent\Galleries\UserImagesGalleryThumbnails\UserGalleryImagesThumbnails;
use POSComponent\Galleries\UserImagesGalleryThumbnails\MyUserGalleryImagesThumbnails;
use POSComponent\Galleries\Images\UsersGallery;
use POSComponent\Galleries\Images\VerificationGallery;
use NetteExt\File;
use NetteExt\Path\GalleryPathCreator;
use NetteExt\Path\ImagePathCreator;
use Nette\Database\Table\ActiveRow;

class GalleriesPresenter extends \BasePresenter {

	public $galleryID;
	public $imageID;
	public $id_image;
	private $images;
	public $verificationExists;

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var \POS\Model\UserGalleryDao @inject */
	public $userGaleryDao;

	/** @var \POS\Model\UserImageDao @inject */
	public $userImageDao;

	/** @var \POS\Model\UsersCompetitionsDao @inject */
	public $usersCompetitionsDao;

	/** @var \POS\Model\StreamDao @inject */
	public $streamDao;

	/** @var \POS\Model\CompetitionsImagesDao @inject */
	public $competitionsImagesDao;

	/** @var \POS\Model\ImageLikesDao @inject */
	public $imageLikesDao;

	/** @var \POS\Model\PaymentDao @inject */
	public $paymentDao;

	/** @var \POS\Model\UserAllowedDao @inject */
	public $userAllowedDao;
	public $isPaying;

	/** @var \POS\Model\FriendDao @inject */
	public $friendDao;

	/** @var \POS\Model\CommentImagesDao @inject */
	public $commentImagesDao;

	/** @var \POS\Model\LikeImageCommentDao @inject */
	public $likeImageCommentDao;

	/** @var ActiveRow Aktuální obrázek */
	private $image;

	public function startup() {
		parent::startup();

		$this->addToCssVariables(array(
			"img-height" => "200px",
			"img-width" => "200px",
			"text-padding-top" => "10px"
		));

		$this->checkLoggedIn();

		$this->isPaying = $this->paymentDao->isUserPaying($this->user->id);
	}

	public function actionEditGalleryImage($imageID, $galleryID) {
		$this->galleryID = $galleryID;
		$this->imageID = $imageID;

		$competitionData = $this->usersCompetitionsDao->getLastCompetitionNameAndId();
		$competitionImage = $this->competitionsImagesDao->findByImgAndCmpId($this->imageID, $competitionData->id);

		/* Ochrana před opakovaným vložením */
		if (!$competitionImage) {
			$this->template->competition = $competitionData;
		} else {
			$this->template->competition = FALSE;
		}
		$this->template->galleryImage = $this->userImageDao->find($imageID);
	}

	public function actionListGalleryImage($imageID, $galleryID) {
		$this->id_image = $this->userImageDao->find($imageID);
		$this->galleryID = $galleryID;
	}

	public function actionUserGalleryChange($galleryID) {
		$this->galleryID = $galleryID;
		$this->template->galleryImages = $this->userImageDao->getInGallery($galleryID);
	}

	public function actionImageNew($galleryID) {
		if ($galleryID) {
			$this->galleryID = $galleryID;
		}
	}

	public function renderDefault($userID) {
		$myGallery = FALSE;

		if (!empty($userID)) {
			/* je vlastník */
			if ($userID == $this->getUser()->id) {
				$this->template->paying = $this->isPaying;
				$myGallery = TRUE;
			}
		} else {
			/* nebyla zvolena konkrétní­ galerie */
			$this->template->paying = $this->isPaying;
			$myGallery = TRUE;
		}

		$this->template->myGallery = $myGallery;
		$this->template->userID = $userID;
		$this->template->mode = "listAll";
	}

	public function actionListUserGalleryImages($galleryID) {
		$gallery = $this->userGaleryDao->find($galleryID);
		$this->checkAccess($gallery);

		$this->galleryID = $galleryID;
	}

	public function renderListUserGalleryImages($galleryID) {
		$gallery = $this->userGaleryDao->find($galleryID);

		if ($gallery->verification_gallery) {
			$this->redirect("Galleries:verification", array("galleryID" => $gallery->id));
		}

		/* je vlastník */
		if ($gallery->userID == $this->getUser()->id) {
			$myGallery = TRUE;
			$this->template->paying = $this->isPaying;
			$this->template->private = $gallery->private;
		} else {
			$myGallery = FALSE;
		}

		$this->template->galleryID = $galleryID;
		$this->template->galleryOwner = $gallery->userID;
		$this->template->private = $gallery->private;
		$this->template->gallery = $gallery;
		$this->template->myGallery = $myGallery;
	}

	public function actionImage($imageID, $galleryID = NULL, $userID = NULL) {
		if (empty($imageID)) {
			$this->flashMessage('Obrázek nebyl nalezen, nebo byl smazán.');
			$this->redirect(':OnePage:');
		}

		$this->imageID = $imageID;

		/* nastaví obrázzky podle uživatele nebo podle galerie */
		if (!empty($galleryID)) {
			$this->images = $this->userImageDao->getInGallery($galleryID);
		} elseif (!empty($userID)) {
			$this->images = $this->userImageDao->getAllFromUser($userID, $this->loggedUser->id, $this->userGaleryDao);
		} else {
			throw new Exception("Musíte nastavit buď ID uživatele, nebo ID galerie.");
		}

		//vytahnu konkretni vybranou fotku podle imageID - objekt
		$image = $this->userImageDao->find($this->imageID);
		$this->checkAccess($image->gallery);
		if (empty($image)) {
			$this->flashMessage("Obrázek nebyl nalezen");
			if ($galleryID) {
				$this->redirect("listUserGalleryImages", array("galleryID" => $galleryID));
			}
			if ($userID) {
				$this->redirect(":Profil:Show:", array("userID" => $userID));
			}
			$this->redirect(":OnePage:");
		}

		$this->image = $image;
	}

	public function actionVerification($galleryID) {
		$galleryData = $this->userGaleryDao->find($galleryID);
		$allowed = $this->userAllowedDao->getByUserID($this->getUser()->getId(), $galleryID);
		// kontrola, jestli se na galerii chce podívat vlastník
		if ($galleryData->userID != $this->getUser()->getId()) {
			if (!$allowed) {
				$this->flashMessage("Tato galerie je nepřístupná.");
				$this->redirect("Show:default", array('id' => $galleryData->userID));
			}
		}

		if (!empty($galleryID)) {
			$this->verificationExists = TRUE;
			$this->galleryID = $galleryID;
			$this->images = $this->userImageDao->getInGallery($galleryID);
			$image = $galleryData->lastImage;
			$this->imageID = $image->id;
		} else {
			$this->verificationExists = FALSE;
		}
		$this->template->exists = $this->verificationExists;
	}

	public function renderUserList($galleryID) {
		$this->checkPaying();
		$this->template->friends = $this->friendDao->getUsersContactList($this->user->id);
		$this->template->allowedUsers = $this->userAllowedDao->getAllowedByGallery($galleryID);
		$this->template->galleryID = $galleryID;
		$gallery = $this->userGaleryDao->find($galleryID);

		if ($this->user->id != $gallery->userID) {
			$this->flashMessage("Tato sekce je nepřístupná.");
			$this->redirect("Show:default", array('id' => $gallery->userID));
		}
	}

	public function renderUserGalleryChange($galleryID) {
		$gallery = $this->userGaleryDao->find($galleryID);
		//pokud je galerie soukromá, provede test, zda uživatel platí
		if ($gallery->private) {
			$this->checkPaying();
		}
		$this->template->galleryID = $this->galleryID;
		$this->template->private = $gallery->private;
		$this->template->paying = $this->paymentDao->isUserPaying($this->user->id);
	}

	public function handledeleteGallery($galleryID) {
		$gallery = $this->userGaleryDao->find($galleryID);
		/* pokud je galerie soukromá, provede test, zda uživatel platí */
		if ($gallery->private) {
			$this->checkPaying();
		}

		$this->streamDao->deleteUserGallery($galleryID);

		$images = $this->userImageDao->getInGallery($galleryID);

		foreach ($images as $image) {

			$this->handledeleteImage($image->id, $galleryID, FALSE);
		}

		$userID = $this->getUser()->getId();
		$path = GalleryPathCreator::getUserGalleryPath($galleryID, $userID);

		File::removeDir($path);

		$this->userGaleryDao->delete($galleryID);

		$this->flashMessage("Galerie byla smazána.");
		$this->redirect("this");
	}

	public function handledeleteImage($id_image, $id_gallery, $redirekt = TRUE) {
		$gallery = $this->userGaleryDao->find($id_gallery);
		//pokud je galerie soukromá, provede test, zda uživatel platí
		if ($gallery->private) {
			$this->checkPaying();
		}
		$image = $this->userImageDao->find($id_image);

		$userID = $this->getUser()->getId();
		$galleryFolder = GalleryPathCreator::getUserGalleryFolder($id_gallery, $userID);

		File::removeImage($image->id, $image->suffix, $galleryFolder);

		$this->userImageDao->delete($id_image);

		if ($redirekt) {
			$this->flashMessage("Obrázek byl smazán.");
			$this->redirect("this");
		}
	}

	public function handleRemoveFriend($userID, $galleryID) {
		$row = $this->userAllowedDao->getByUserID($userID, $galleryID);
		$this->userAllowedDao->delete($row->id);

		if ($this->isAjax()) {
			$this->redrawControl("allowedUsers");
		} else {
			$this->redirect("this");
		}
	}

	/**
	 * Přidá fotku uživatele do poslední soutěže
	 * @param type $imageID ID obrázku, který bude přidán do soutěže
	 * @param type $competitionID ID soutěže, do které bude obrázek přidán
	 */
	public function handleAddToCompetition($imageID, $competitionID) {
		$this->competitionsImagesDao->insertImageToCompetition($imageID, $this->user->id, $competitionID);
		$this->flashMessage('Fotka byla přidaná. Nyní je ve frontě na schválení.');
		$this->redirect('this');
	}

	protected function createComponentUserGalleryNew($name) {
		return new Frm\UserGalleryNewForm($this->userGaleryDao, $this->userImageDao, $this->streamDao, $this->isPaying, $this->loggedUser->id, $this, $name);
	}

	protected function createComponentUserGalleryChange($name) {
		return new Frm\UserGalleryChangeForm($this->userGaleryDao, $this->userImageDao, $this->streamDao, $this->galleryID, $this->isPaying, $this->loggedUser->id, $this, $name);
	}

	protected function createComponentNewImage($name) {
		return new Frm\NewImageForm($this->userGaleryDao, $this->userImageDao, $this->streamDao, $this->galleryID, $this, $name);
	}

	protected function createComponentUserGalleryImageChange($name) {
		return new Frm\UserGalleryImageChangeForm($this->userGaleryDao, $this->userImageDao, $this->streamDao, $this->imageID, $this->galleryID, $this, $name);
	}

	public function createComponentUserGalleries() {
		return new UserGalleriesThumbnails($this->userDao, $this->userGaleryDao, $this->userAllowedDao, $this->friendDao);
	}

	public function createComponentMyUserGalleries() {
		return new MyUserGalleriesThumbnails($this->userDao, $this->userGaleryDao, $this->userAllowedDao, $this->friendDao);
	}

	/**
	 * vykresluje obrázky v galerii uživatel (VLASTNÍKA)
	 */
	protected function createComponentMyUserImagesInGallery() {
		$images = $this->userImageDao->getInGallery($this->galleryID);

		return new MyUserGalleryImagesThumbnails($this->galleryID, $images, $this->userDao, $this->userAllowedDao);
	}

	/**
	 * vykresluje obrázky v galerii
	 */
	protected function createComponentUserImagesInGallery() {
		$images = $this->userImageDao->getInGallery($this->galleryID);

		return new UserGalleryImagesThumbnails($images, $this->userDao);
	}

	protected function createComponentGallery($name) {
		//vytahnu konkretni galerie podle galleryID
		$gallery = $this->userGaleryDao->find($this->image->galleryID);

		$httpRequest = $this->context->httpRequest;

		return new UsersGallery($this->images, $this->image, $gallery, $this->userImageDao, $this->imageLikesDao, $this->likeImageCommentDao, $this->commentImagesDao, $this->loggedUser, $this, $name);
	}

	/**
	 * Továrnička na verifikační galerii
	 * @return VerificationGallery
	 */
	public function createComponentVerificationGallery($name) {
		if ($this->verificationExists) {
			$image = $this->userImageDao->find($this->imageID);
			$gallery = $this->userGaleryDao->find($this->galleryID);
			$httpRequest = $this->context->httpRequest;
			$domain = $httpRequest->getUrl()->host;
			return new VerificationGallery($this->images, $image, $gallery, $domain, TRUE, $this->userImageDao, $this->imageLikesDao, $this->isPaying, $this, $name);
		}
	}

	/**
	 * Továrnička na formulář pro verifikační fotku
	 * @param type $name
	 * @return \Nette\Application\UI\Form\VerificationImageNewForm
	 */
	public function createComponentVerificationForm($name) {
		return new Frm\VerificationImageNewForm($this->userGaleryDao, $this->userImageDao, $this->streamDao, $this, $name);
	}

	protected function createComponentAllowUserForm($name) {
		return new Frm\AllowForm($this->userAllowedDao, $this->userDao, $this, $name);
	}

	protected function createComponentAllowFriendsForm($name) {
		return new Frm\AllowFriendsForm($this->userGaleryDao, $this, $name);
	}

	/**
	 * Otestuje zda je informace o glaerii v session, pokud ne přidá ji,
	 * poté provede test, zda uživatel muže do galerie a v případě
	 * potřebu ho přesměruje a napíše proč
	 * @param ActiveRow $gallery Galerie
	 */
	private function checkAccess(ActiveRow $gallery) {
		$ownerID = $gallery->userID;
		$haveAccess = $this->userGaleryDao->haveAccessIntoGallery($gallery, $this->loggedUser->id, $ownerID);

		if (!$haveAccess) {
			$this->flashMessage("Do této galerie nemáte přístup.");
			$this->redirect("Galleries:default", array("userID" => $ownerID)); //TODO
		}
	}

	/**
	 * otestuje, zda je uživatel platící, pokud ne, provede redirect
	 */
	private function checkPaying() {
		if (!$this->isPaying) {
			$this->flashMessage("Tato akce je zablokována.");
			$this->redirect("Galleries:");
		}
	}

}
