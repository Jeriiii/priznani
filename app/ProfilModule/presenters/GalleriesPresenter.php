<?php

namespace ProfilModule;

/**
 * Base presenter for all profile application presenters.
 */
use Nette\Security\User;
use Nette\Http\Request;
use Nette\Application\UI\Form as Frm;
use Navigation\Navigation;
use POSComponent\Galleries\UserGalleries\MyUserGalleries;
use POSComponent\Galleries\UserGalleries\UserGalleries;
use POSComponent\Galleries\UserImagesInGallery\UserImagesInGallery;
use POSComponent\Galleries\UserImagesInGallery\MyUserImagesInGallery;
use POSComponent\Galleries\Images\UsersGallery;
use NetteExt\File;
use NetteExt\Path\GalleryPathCreator;
use NetteExt\Path\ImagePathCreator;

class GalleriesPresenter extends \BasePresenter {

	public $galleryID;
	public $imageID;
	public $id_image;
	private $images;

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	/**
	 * @var \POS\Model\UserGalleryDao
	 * @inject
	 */
	public $userGaleryDao;

	/**
	 * @var \POS\Model\UserImageDao
	 * @inject
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\UsersCompetitionsDao
	 * @inject
	 */
	public $usersCompetitionsDao;

	/**
	 * @var \POS\Model\StreamDao
	 * @inject
	 */
	public $streamDao;

	/**
	 * @var \POS\Model\CompetitionsImagesDao
	 * @inject
	 */
	public $competitionsImagesDao;

	public function startup() {
		parent::startup();

		$this->addToCssVariables(array(
			"img-height" => "200px",
			"img-width" => "200px",
			"text-padding-top" => "10px"
		));

		$user = $this->getUser();

		if (!$user->isLoggedIn()) {
			if ($user->getLogoutReason() === User::INACTIVITY) {
				$this->flashMessage('Uplynula doba neaktivity! Systém vás z bezpečnostních důvodů odhlásil.', 'warning');
			}
			$this->redirect(':Sign:in', array('backlink' => $this->backlink()));
		} else { //kontrola opravnění pro vztup do příslušné sekce
			if (!$user->isAllowed($this->name, $this->action)) {
				$this->flashMessage('Nejdříve se musíte přihlásit.', 'warning');
				$this->redirect(':Sign:in', array('backlink' => $this->backlink()));
			}
		}
	}

	public function actionEditGalleryImage($imageID, $galleryID) {
		$this->galleryID = $galleryID;
		$this->imageID = $imageID;

		$competitionData = $this->usersCompetitionsDao->getLastCompetitionNameAndId();
		$competitionImage = $this->competitionsImagesDao->findByImgAndCmpId($this->imageID, $competitionData->id);

		// Ochrana před opakovaným vložením
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
		$gallery = $this->userGaleryDao->find($galleryID);

		//je vlastník
		if ($gallery->userID == $this->getUser()->id) {
			$myGallery = TRUE;
		} else {
			$myGallery = FALSE;
		}

		$this->template->galleryOwner = $gallery->userID;
		$this->template->myGallery = $myGallery;
	}

	public function actionImage($imageID, $galleryID = NULL, $userID = NULL) {
		$this->imageID = $imageID;
		/* nastaví obrázky podle uživatele nebo podle galerie */
		if (!empty($galleryID)) {
			$this->images = $this->userImageDao->getInGallery($galleryID);
		} elseif (!empty($userID)) {
			$this->images = $this->userImageDao->getAllFromUser($userID);
		} else {
			throw new Exception("Musíte nastavit buď ID uživatele, nebo ID galerie.");
		}
	}

	public function handledeleteGallery($galleryID) {
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
		return new Frm\UserGalleryNewForm($this->userGaleryDao, $this->userImageDao, $this->streamDao, $this, $name);
	}

	protected function createComponentUserGalleryChange($name) {
		return new Frm\UserGalleryChangeForm($this->userGaleryDao, $this->userImageDao, $this->streamDao, $this->galleryID, $this, $name);
	}

	protected function createComponentNewImage($name) {
		return new Frm\NewImageForm($this->userGaleryDao, $this->userImageDao, $this->streamDao, $this->galleryID, $this, $name);
	}

	protected function createComponentUserGalleryImageChange($name) {
		return new Frm\UserGalleryImageChangeForm($this->userGaleryDao, $this->userImageDao, $this->streamDao, $this->imageID, $this->galleryID, $this, $name);
	}

	public function createComponentUserGalleries() {
		return new UserGalleries($this->userDao, $this->userGaleryDao);
	}

	public function createComponentMyUserGalleries() {
		return new MyUserGalleries($this->userDao, $this->userGaleryDao);
	}

	/**
	 * vykresluje obrázky v galerii uživatel (VLASTNÍKA)
	 */
	protected function createComponentMyUserImagesInGallery() {
		$images = $this->userImageDao->getInGallery($this->galleryID);

		return new MyUserImagesInGallery($images, $this->userDao);
	}

	/**
	 * vykresluje obrázky v galerii
	 */
	protected function createComponentUserImagesInGallery() {
		$images = $this->userImageDao->getInGallery($this->galleryID);

		return new UserImagesInGallery($images, $this->userDao);
	}

	protected function createComponentGallery() {
		//vytahnu konkretni vybranou fotku podle imageID - objekt
		$image = $this->userImageDao->find($this->imageID);

		//vytahnu konkretni galerie podle galleryID
		$gallery = $this->userGaleryDao->find($image->galleryID);

		$httpRequest = $this->context->httpRequest;
		$domain = $httpRequest->getUrl()->host;
		//$domain = "http://priznaniosexu.cz";

		return new UsersGallery($this->images, $image, $gallery, $domain, TRUE, $this->userImageDao);
	}

	protected function createComponentNavigation($name) {
		//Získání potřebných dat(user id, galerie daného usera)
		$userID = $this->getUser()->id;
		$user = $this->userDao->find($userID);

		//vytvoření navigace a naplnění daty
		$nav = new Navigation($this, $name);
		$navigation = $nav->setupHomepage($user->user_name, $this->link("Galleries:default"));
		//označí aktuální stránku jako aktivní v navigaci
		if ($this->isLinkCurrent("Galleries:default")) {
			$nav->setCurrentNode($navigation);
		}

		//získání dat pro přípravu galerii do breadcrumbs
		$galleries = $this->userGaleryDao->getInUser($userID);

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
