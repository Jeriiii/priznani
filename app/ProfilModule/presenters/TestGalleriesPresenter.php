<?php

namespace ProfilModule;

/**
 * Base presenter for all profile application presenters.
 */
use \Nette\Security\User,
	\UserGalleries\UserGalleries,
	Nette\Http\Request,
	\Nette\Application\UI\Form as Frm,
	\Navigation\Navigation;

class TestGalleriesPresenter extends \BasePresenter {

	private $userModel;
	private $user;
	private $fotos;
	public $galleryID;
	public $imageID;
	public $id_image;

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
	}

	public function startup() {
		parent::startup();
		$this->addToCssVariables(array(
			"img-height" => "200px",
			"img-width" => "200px",
			"text-padding-top" => "10px"
		));
		$this->userModel = $this->context->userModel;

		$user = $this->getUser();

		if (!$user->isLoggedIn()) {
			if ($user->getLogoutReason() === User::INACTIVITY) {
				$this->flashMessage('Uplynula doba neaktivity! Systém vás z bezpečnostních důvodů odhlásil.', 'warning');
			}
			$backlink = $this->getApplication()->storeRequest();
			$this->redirect(':Sign:in', array('backlink' => $backlink));
		} else { //kontrola opravnění pro vztup do příslušné sekce
			if (!$user->isAllowed($this->name, $this->action)) {
				$this->flashMessage('Nejdříve se musíte přihlásit.', 'warning');
				$this->redirect(':Sign:in', array('backlink' => $backlink));
			}
		}
	}

	public function actionEditGalleryImage($imageID, $galleryID) {
		$this->galleryID = $galleryID;
		$this->imageID = $imageID;

		$this->template->galleryImage = $this->context->createUsersImages()
			->where('id', $imageID)
			->fetch();
	}

	public function actionListGalleryImage($imageID, $galleryID) {
		$this->id_image = $this->context->createUsersImages()
			->find($imageID)
			->fetch();
		$this->galleryID = $galleryID;
	}

	public function actionUserGalleryChange($galleryID) {
		$this->galleryID = $galleryID;
		$this->template->galleryImages = $this->context->createUsersImages()
			->where('galleryID', $galleryID)
			->order('id DESC');
	}

	public function actionImageNew($galleryID) {
		if ($galleryID) {
			$this->galleryID = $galleryID;
		}
	}

	public function renderDefault($userID) {
		$myGallery = FALSE;

		if (!empty($userID)) {
			//je vlastník
			if ($userID == $this->getUser()->id) {
				$myGallery = TRUE;
			}
		} else {
			//nebyla zvolena konkrétní galerie
			$myGallery = TRUE;
		}

		$this->template->myGallery = $myGallery;
		$this->template->userID = $userID;
		$this->template->mode = "listAll";
	}

	public function actionListUserGalleryImages($galleryID) {
		$this->galleryID = $galleryID;
	}

	public function renderListUserGalleryImages($galleryID) {
		$gallery = $this->context->createUsersGalleries()
			->find($galleryID)
			->fetch();

		//je vlastník
		if ($gallery->userID == $this->getUser()->id) {
			$myGallery = TRUE;
		} else {
			$myGallery = FALSE;
		}

		$this->template->galleryOwner = $gallery->userID;
		$this->template->myGallery = $myGallery;
	}

	protected function getUserDataFromDB() {
		return $this->context->createUsersImages();
	}

	public function actionImage($galleryID, $imageID) {
		$this->imageID = $imageID;
		$this->galleryID = $galleryID;
	}

	public function renderImage($galleryID, $imageID) {
		if (!empty($galleryID) && !empty($imageID)) {
			$this->galleryID = $galleryID;
			$this->imageID = $imageID;
		}
	}

	public function handledeleteGallery($galleryID) {
		// nastavení vazeb na NULL, jinak to začne padat
		$this->context->createUsersGalleries()
			->find($galleryID)
			->update(array(
				"bestImageID" => NULL,
				"lastImageID" => NULL
		));

		$this->context->createStream()
			->where("userGalleryID", $galleryID)
			->delete();

		$images = $this->context->createUsersImages()
			->where("galleryID", $galleryID);

		foreach ($images as $image) {

			$this->handledeleteImage($image->id, $galleryID, FALSE);
		}

		$way = WWW_DIR . "/images/userGalleries/" . $this->getUser()->getId() . "/" . $galleryID;

		if (file_exists($way)) {
			rmdir($way);
		}

		$this->context->createUsersGalleries()
			->where("id", $galleryID)
			->delete();

		$this->flashMessage("Galerie byla smazána.");
		$this->redirect("this");
	}

	public function handledeleteImage($id_image, $id_gallery, $redirekt = TRUE) {
		$image = $this->context->createUsersImages()
			->find($id_image)
			->fetch();

		$dirPath = WWW_DIR . "/images/userGalleries/" . $this->getUser()->getId() . "/" . $id_gallery . "/";

		$way = $dirPath . $image->id . "." . $image->suffix;
		$wayMini = $dirPath . "min" . $image->id . "." . $image->suffix;
		$wayScrn = $dirPath . "galScrn" . $image->id . "." . $image->suffix;
		$waySqr = $dirPath . "minSqr" . $image->id . "." . $image->suffix;

		if (file_exists($way)) {
			unlink($way);
		}

		if (file_exists($wayMini)) {
			unlink($wayMini);
		}

		if (file_exists($wayScrn)) {
			unlink($wayScrn);
		}

		if (file_exists($waySqr)) {
			unlink($waySqr);
		}


		$gallery = $this->context->createUsersGalleries()
			->where("bestImageID = ? OR lastImageID = ?", $id_image, $id_image)
			->fetch();

		/* kontrola, zda se nemaze obrazek zastupujici galerii */
		if ($gallery) {
			$image = $this->context->createUsersImages()
				->where("galleryID", $gallery->id)
				->where("id != ?", $id_image)
				->fetch();

			$galleryID = $gallery->id;

			/* existuji jine obrazky v galerii? */
			if ($image) {
				/* ANO - nastav jiný obrázek */
				$gallery
					->update(array(
						"bestImageID" => $image->id,
						"lastImageID" => $image->id
				));
			} else {
				/* NE */
				$gallery
					->update(array(
						"bestImageID" => NULL,
						"lastImageID" => NULL
				));

				/* smaž galerii ze streamu */
				$this->context->createStream()
					->where("userGalleryID", $galleryID)
					->delete();
			}
		}


		$this->context->createUsersImages()
			->find($id_image)
			->delete();


		if ($redirekt) {
			$this->flashMessage("Obrázek byl smazán.");
			$this->redirect("this");
		}
	}

	protected function createComponentUserGalleryNew($name) {
		return new Frm\UserGalleryNewForm($this, $name);
	}

	protected function createComponentUserGalleryChange($name) {
		return new Frm\UserGalleryChangeForm($this, $name);
	}

	protected function createComponentNewImage($name) {
		return new Frm\NewImageForm($this, $name);
	}

	protected function createComponentUserGalleryImageChange($name) {
		return new Frm\UserGalleryImageChangeForm($this, $name);
	}

	public function createComponentUserGalleries() {
		return new \POSComponent\Galleries\UserGalleries\UserGalleries();
	}

	public function createComponentMyUserGalleries() {
		return new \POSComponent\Galleries\UserGalleries\MyUserGalleries();
	}

	/**
	 * vykresluje obrázky v galerii uživatel (VLASTNÍKA)
	 */
	protected function createComponentMyUserImagesInGallery() {
		$images = $this->context->createUsersImages()
			->where("galleryID", $this->galleryID)
			->order("id DESC");

		return new \POSComponent\Galleries\UserImagesInGallery\MyUserImagesInGallery($this->galleryID, $images);
	}

	/**
	 * vykresluje obrázky v galerii
	 */
	protected function createComponentUserImagesInGallery() {
		$images = $this->context->createUsersImages()
			->where("galleryID", $this->galleryID);

		return new \POSComponent\Galleries\UserImagesInGallery\UserImagesInGallery($this->galleryID, $images);
	}

	protected function createComponentGallery() {
		//vytahnu vsechny fotky dane galerie podle galleryID - objekt
		$images = $this->context->createUsersImages()
			->where("galleryID", $this->galleryID);

		//vytahnu konkretni vybranou fotku podle imageID - objekt
		$image = $this->context->createUsersImages()
			->find($this->imageID)
			->fetch();

		//vytahnu konkretni galerie podle galleryID
		$gallery = $this->context->createUsersGalleries()
			->where("id", $this->galleryID)
			->fetch();


		$httpRequest = $this->context->httpRequest;
		$domain = $httpRequest->getUrl()->host;
		//$domain = "http://priznaniosexu.cz";

		return new \POSComponent\Galleries\Images\UsersGallery($images, $image, $gallery, $domain, TRUE);
	}

	protected function createComponentNavigation($name) {
		//Získání potřebných dat(user id, galerie daného usera)
		$id = $this->getUser()->id;
		$userModel = $this->userModel;
		$user = $userModel->findUser(array("id" => $id));

		//vytvoření navigace a naplnění daty
		$nav = new Navigation($this, $name);
		$navigation = $nav->setupHomepage($user->user_name, $this->link("Galleries:default"));
		//označí aktuální stránku jako aktivní v navigaci
		if ($this->isLinkCurrent("Galleries:default")) {
			$nav->setCurrentNode($navigation);
		}

		//získání dat pro přípravu galerii do breadcrumbs
		$galleries = $this->context->createUsersGalleries()
			->where("userId", $id);

		//příprava všech galerií pro možnost použití drobečkové navigace
		foreach ($galleries as $gallery) {
			$link = $this->link("Galleries:listUserGalleryImage", array("galleryID" => $gallery->id));
			$sec = $navigation->add($gallery->name, $link);

			if ($this->galleryID == $gallery->id) {
				$nav->setCurrentNode($sec);
			}
		}
	}

}