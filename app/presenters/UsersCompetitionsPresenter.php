<?php

use POSComponent\Galleries\Images\UsersCompetitionsGallery;
use POSComponent\Galleries\UserImagesInGallery\CompetitionsImagesInGallery;
use POS\Model\GalleryDao;

/**
 * TempPresenter Description
 */
class UsersCompetitionsPresenter extends BasePresenter {

	public $imageID;
	public $image;
	public $gallery;
	public $galleryID;
	public $domain;

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

	public function actionDefault($imageID, $competitionID) {
		//určitá galerie
		if (!empty($competitionID)) {
			$this->gallery = $this->usersCompetitionsDao->find($competitionID);
		}

		/* id obrázku je uloženo v odkaze od galerie */
		if (empty($imageID)) {
			/* pokus o naplneni promenne z gallerie */
			$httpRequest = $this->context->httpRequest;
			$imageID = $httpRequest->getQuery('gallery-imageID');
		}
		if (empty($imageID)) {
			// je na hlavní stránce soutěží ?
			if (empty($competitionID)) {
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
			$this->image = $this->competitionsImagesDao->findByImgId($this->imageID);

			$this->gallery = $this->usersCompetitionsDao->find($this->image->competitionID);
			$this->domain = $this->partymode ? "http://priznanizparby.cz" : "http://priznaniosexu.cz";
		}
	}

	public function renderDefault($imageID, $galleryID) {
		if (!empty($this->imageID)) {
			$this->template->imageLink = $this->domain . "/images/galleries/" . $this->gallery->id . "/" . $this->image->image->id . "." . $this->image->image->suffix;
		} else {
			$this->template->imageLink = null;
		}
	}

	public function renderList($justCompetition = FALSE, $withoutCompetition = FALSE) {
		if ($this->partymode) {
			$mode = GalleryDao::COLUMN_PARTY_MODE;
		} else {
			$mode = GalleryDao::COLUMN_SEX_MODE;
		}

		$this->template->oldCompetitions = $this->galleryDao->getGallery($mode);
		$this->template->galleries = $this->galleryDao->getCompetition($mode);
		$this->template->competitions = $this->usersCompetitionsDao->getAll();
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

	private function galleryMissing() {

		$this->flashMessage("Galerie nebyla nalezena");
		$this->redirect("UsersCompetitions:");
	}

	public function renderListImages($galleryID) {
//		$this->template->images = $this->imageDao->getApproved($galleryID);
//		$this->template->gallery = $this->gallery;
	}

	public function createComponentGallery() {
		$imagesID = $this->competitionsImagesDao->getByCompetitions($this->gallery->id);
		$iID = array_keys($imagesID);
		$images = $this->userImageDao->getAllById($iID);
		$httpRequest = $this->context->httpRequest;
		$domain = $httpRequest->getUrl()->host;
		return new UsersCompetitionsGallery($images, $this->image->image, $this->gallery, $domain, $this->partymode, $this->userImageDao);
	}

	protected function createComponentUsersImagesInGallery() {
		$imagesID = $this->competitionsImagesDao->getByCompetitions($this->gallery->id);
		$iID = array_keys($imagesID);
		$images = $this->userImageDao->getAllById($iID);
		return new CompetitionsImagesInGallery($this->gallery->id, $images, $this->userDao);
	}

	/**
	 * vrátí poslední soutěž
	 */
	public function getLastCompetition() {
		return $this->usersCompetitionsDao->findLast();
	}

	public function getImageIDFromCompetition($competition) {
		$image = $this->competitionsImagesDao->findByApproved($competition->id);
		if (!empty($image)) {
			return $image->id;
		}

		return null;
	}

}
