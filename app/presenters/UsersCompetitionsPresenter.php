<?php

use POSComponent\Galleries\Images\UsersCompetitionsGallery;
use POSComponent\Galleries\UserImagesGalleryThumbnails\CompetitionsGalleryIamgesThumbnails;
use Nette\Application\UI\Form as Frm;

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Obstarává komponenty vykreslující soutěžní fotky.
 */
class UsersCompetitionsPresenter extends BasePresenter {

	public $imageID;
	public $compImage;
	public $gallery;
	public $domain;
	public $galleryID;

	/**
	 * @var POS\Model\UsersCompetitionsDao
	 * @inject
	 */
	public $usersCompetitionsDao;

	/**
	 * @var POS\Model\CompetitionsImagesDao
	 * @inject
	 */
	public $competitionsImagesDao;

	/**
	 * @var POS\Model\UserImageDao
	 * @inject
	 */
	public $userImageDao;

	/**
	 * @var POS\Model\userDao
	 * @inject
	 */
	public $userDao;

	/**
	 * @var POS\Model\GalleryDao
	 * @inject
	 */
	public $galleryDao;

	/**
	 * @var POS\Model\UserGalleryDao
	 * @inject
	 */
	public $userGalleryDao;

	/**
	 * @var POS\Model\StreamDao
	 * @inject
	 */
	public $streamDao;

	/**
	 * @var POS\Model\ImageLikesDao
	 * @inject
	 */
	public $imageLikesDao;

	/**
	 * @var POS\Model\CommentImagesDao
	 * @inject
	 */
	public $commentImagesDao;

	/**
	 * @var POS\Model\LikeImageCommentDao
	 * @inject
	 */
	public $likeImageCommentDao;

	public function actionDefault($imageID, $galleryID) {
		//určitá galerie
		if (!empty($galleryID)) {
			$this->gallery = $this->usersCompetitionsDao->find($galleryID);
		}

		/* id obrázku je uloženo v odkaze od galerie */
		if (empty($imageID)) {
			/* pokus o naplneni promenne z gallerie */
			$httpRequest = $this->context->httpRequest;
			$imageID = $httpRequest->getQuery('gallery-imageID');
		}
		if (empty($imageID)) {
			// je na hlavní stránce soutěží ?
			if (empty($galleryID)) {
				$this->gallery = $this->getLastCompetition();
			}

			// vrátí obrázek z galerie, pokud existuje alespoň jeden
			$imageID = $this->getImageIDFromCompetition($this->gallery);
		}

		if (empty($imageID)) {
			/* zatím žádný obrázek v galerii není */
			$this->redirect("Competition:uploadImage");
		} else {
			// obrázek nalezen, nastavení obrázku do presenteru
			$this->imageID = $imageID;
			$this->compImage = $this->competitionsImagesDao->findByImgId($this->imageID);

			$this->gallery = $this->usersCompetitionsDao->find($this->compImage->competitionID);
			$this->domain = $this->partymode ? "http://priznanizparby.cz" : "http://priznaniosexu.cz";
		}
	}

	public function renderDefault($imageID, $galleryID) {
		if (!empty($this->imageID)) {
			$this->template->imageLink = $this->domain . "/images/galleries/" . $this->gallery->id . "/" . $this->compImage->image->id . "." . $this->compImage->image->suffix;
		} else {
			$this->template->imageLink = null;
		}
	}

	public function actionListImages($galleryID) {

		/* pokud není specifikovaná galerie, stránka se přesměruje */
		if (empty($galleryID)) {
			$this->galleryMissing();
		}

		$this->gallery = $this->usersCompetitionsDao->find($galleryID);

		/* galerie nebyla podle ID nalezena */
		if (empty($this->gallery)) {
			$this->galleryMissing();
		}

		$this->addToCssVariables(array(
			"img-height" => "200px",
			"img-width" => "200px",
			"text-padding-top" => "40%"
		));
	}

	public function actionUploadImage($galleryID) {
		$this->galleryID = $galleryID;
	}

	private function galleryMissing() {

		$this->flashMessage("Galerie nebyla nalezena");
		$this->redirect("UsersCompetitions:");
	}

	/**
	 * Vytvoří komponentu pro procházení fotek galerie
	 * @return \POSComponent\Galleries\Images\UsersCompetitionsGallery
	 */
	public function createComponentGallery($name) {
		$imagesID = $this->competitionsImagesDao->getApprovedByComp($this->gallery->id);
		$iID = array_keys($imagesID);
		$images = $this->userImageDao->getAllById($iID);
		$httpRequest = $this->context->httpRequest;
		$domain = $httpRequest->getUrl()->host;
		return new UsersCompetitionsGallery($images, $this->compImage->image, $this->gallery, $domain, $this->partymode, $this->likeImageCommentDao, $this->userImageDao, $this->commentImagesDao, $this->imageLikesDao, $this->loggedUser, $this, $name);
	}

	/**
	 * Komponenta pro seznam fotek jako kostičky
	 * @return \POSComponent\Galleries\UserImagesInGallery\CompetitionsImagesInGallery
	 */
	protected function createComponentUsersImagesInGallery() {
		$imagesID = $this->competitionsImagesDao->getApprovedByComp($this->gallery->id);
		$iID = array_keys($imagesID);
		$images = $this->userImageDao->getAllById($iID);
		return new CompetitionsGalleryIamgesThumbnails($images, $this->userDao);
	}

	public function createComponentNewImageForm($name) {
		return new Frm\NewCompetitionImageForm($this->userGalleryDao, $this->userImageDao, $this->streamDao, $this->galleryID, $this->usersCompetitionsDao, $this->competitionsImagesDao, $this, $name);
	}

	/**
	 * vrátí poslední soutěž
	 */
	public function getLastCompetition() {
		return $this->usersCompetitionsDao->findLast();
	}

	/**
	 * Vrátí obrázek z galerie, pokud takový je
	 */
	public function getImageIDFromCompetition($competition) {
		$image = $this->competitionsImagesDao->findByApproved($competition->id);
		if (!empty($image)) {
			return $image->id;
		}

		return null;
	}

}
