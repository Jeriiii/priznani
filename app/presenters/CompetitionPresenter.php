<?php

/**
 * Homepage presenter.
 *
 * Zobrayení úvodní stránky systému
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */
use Nette\Application\UI\Form as Frm;
use Nette\Http\Request;
use NetteExt\Image;
use POSComponent\Galleries\Images\CompetitionGallery;
use POS\Model\GalleryDao;
use NetteExt\Path\ImagePathCreator;
use NetteExt\Path\GalleryPathCreator;
use NetteExt\Form\Upload\UploadImage;
use WebLoader\Nette\JavaScriptLoader;
use WebLoader\FileCollection;
use WebLoader\Compiler;

class CompetitionPresenter extends BasePresenter {

	public $imageID;
	public $image;
	public $gallery;
	public $galleryID;
	public $domain;

	/**
	 * @var \POS\Model\ImageDao
	 * @inject
	 */
	public $imageDao;

	/**
	 * @var \POS\Model\GalleryDao
	 * @inject
	 */
	public $galleryDao;

	/**
	 * @var \POS\Model\StreamDao
	 * @inject
	 */
	public $streamDao;

	/**
	 * @var POS\Model\UsersCompetitionsDao
	 * @inject
	 */
	public $usersCompetitionsDao;

	/**
	 * @var POS\Model\UserGalleryDao
	 * @inject
	 */
	public $userGalleryDao;

	/**
	 * @var POS\Model\UserImageDao
	 * @inject
	 */
	public $userImageDao;

	/**
	 * @var POS\Model\CompetitionsImagesDao
	 * @inject
	 */
	public $competitionsImagesDao;

	public function startup() {
		parent::startup();

		$httpRequest = $this->context->httpRequest;
		$this->domain = $httpRequest
				->getUrl()
			->host;

		if (strpos($this->domain, "priznanizparby") !== false) {
			$this->setPartyMode();
		} else {
			$this->setSexMode();
		}
	}

	public function beforeRender() {
		parent::beforeRender();
	}

	public function renderList($justCompetition = FALSE, $withoutCompetition = FALSE) {
		if ($this->partymode) {
			$mode = GalleryDao::COLUMN_PARTY_MODE;
		} else {
			$mode = GalleryDao::COLUMN_SEX_MODE;
		}

		$this->template->competitions = $this->galleryDao->getGallery($mode);
		$this->template->galleries = $this->galleryDao->getCompetition($mode);
		$this->template->userCompetitions = $this->usersCompetitionsDao->getAll();
	}

	public function actionListImages($galleryID) {

		/* pokud není specifikovaná galerie, stránka se přesměruje */
		if (empty($galleryID)) {
			$this->galleryMissing();
		}

		$this->gallery = $this->galleryDao->find($galleryID);

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
		$this->redirect("Competition:");
	}

	public function renderListImages($galleryID) {
		$this->template->images = $this->imageDao->getApproved($galleryID);
		$this->template->gallery = $this->gallery;
	}

	public function actionImagesClip() {
		$galleries = $this->galleryDao->getAll();

		foreach ($galleries as $gallery) {
			$images = $this->imageDao->getInGallery($gallery->id);
			$galleryFolder = GalleryPathCreator::getGalleryFolder($gallery->id);

			foreach ($images as $image) {
				$this->clipAndSaveImage($image, $galleryFolder);
			}
		}

		die("miniatury byly vytvořeny");
	}

	/**
	 * Ořízne obrázek a uloží ho.
	 * @param \Nette\Database\Table\ActiveRow $image Obrázek z databáze.
	 * @param string $galleryFolder Složka galerie.
	 */
	private function clipAndSaveImage($image, $galleryFolder) {
		$imagePath = ImagePathCreator::getImgPath($image->id, $image->suffix, $galleryFolder);
		$imageGalScrnPath = ImagePathCreator::getImgScrnPath($image->id, $image->suffix, $galleryFolder);

		if (file_exists($imagePath) && !file_exists($imageGalScrnPath)
		/* && ($image->widthGalScrn == 1280) /*!file_exists($imageGalScrnPath) */) {
			$imageFile = Image::fromFile($imagePath);
			$width = $imageFile->getWidth();
			$height = $imageFile->getHeight();
			$this->imageDao->updateScrnWidthHeight($image->id, $width, $height);

			echo $imagePath . " <br />";

//			pro (velký) náhled obrázku v galerii
//			$image->resize(700,500);
//			pro čtvercový výřez
//			$image->resizeMinSite(200);
//			$image->cropSqr(200);

			$imageFile->save($imageGalScrnPath);
		}
	}

	/* bacha, id je url - tedy nazev stranky */

	public function actionDefault($imageID, $galleryID) {
		//určitá galerie
		if (!empty($galleryID)) {
			$this->gallery = $this->galleryDao->find($galleryID);
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
				$this->gallery = $this->getLastGallery();
			}

			// vrátí obrázek z galerie, pokud existuje alespoň jeden
			$imageID = $this->getImageIDFromGallery($this->gallery);
		}

		if (empty($imageID)) {
			/* zatím žádný obrázek v galerii není */
			$this->redirect("Competition:uploadImage");
		} else {
			// obrázek nalezen, nastavení obrázku do presenteru
			$this->imageID = $imageID;

			$this->image = $this->imageDao->find($this->imageID);
			$this->gallery = $this->galleryDao->find($this->image->galleryID);

			$this->domain = $this->partymode ? "http://priznanizparby.cz" : "http://priznaniosexu.cz";
		}
	}

	/**
	 * Vrátí ID schváleného obrázku když je v této galerii alespoň jeden.
	 * @param \Nette\Database\Table\ActiveRow $gallery Galerie.
	 * @return null|int ID obrázku když existuje.
	 */
	public function getImageIDFromGallery($gallery) {
		$image = $this->imageDao->findByApproved($gallery->id);

		if (!empty($image)) {
			return $image->id;
		}

		return null;
	}

	/**
	 * vrátí poslední galerii podle modu sex/pařba
	 */
	public function getLastGallery() {
		if ($this->partymode) {
			$mode = GalleryDao::COLUMN_PARTY_MODE;
		} else {
			$mode = GalleryDao::COLUMN_SEX_MODE;
		}
		return $this->galleryDao->findByMode($mode);
	}

	public function renderDefault($imageID, $galleryID) {
		if (!empty($this->imageID)) {
			if ($this->image->videoID != 0) {
				$this->template->imageLink = "http://img.youtube.com/vi/" . $this->image->video->code . "/0.jpg";
			} else {
				$this->template->imageLink = $this->domain . "/images/galleries/" . $this->gallery->id . "/" . $this->image->id . "." . $this->image->suffix;
			}
			/* předělat - rychlá úprava */
			if ($this->gallery->id == 3) {
				$this->template->galleryMode = TRUE;
			}
		} else {
			$this->template->imageLink = null;
		}
	}

	public function actionUploadImage($galleryID) {
		$this->galleryID = $galleryID;
	}

	public function actionUploadCompetitionImage($galleryID) {
		$this->galleryID = $galleryID;
	}

	public function renderUploadImage($galleryID) {
		$photos = $this->imageDao->getAll("DESC");
		$this->template->photo1 = $photos->fetch();
		$this->template->photo2 = $photos->fetch();

		$this->template->galleryID = $this->getLastGallery()->id;
		if ($this->partymode) {
			$this->template->images = array(1, 2, 3);
		} else {
			$this->template->images = array(1, 2, 3);
		}
	}

	protected function createComponentGallery() {
		if ($this->getUser()->isInRole("admin") || $this->getUser()->isInRole("superadmin")) {
			$images = $this->imageDao->getInGallery($this->gallery->id);
		} else {
			$images = $this->imageDao->getApproved($this->gallery->id);
		}

		return new CompetitionGallery($images, $this->image, $this->gallery, $this->domain, $this->partymode, $this->imageDao, $this->galleryDao, $this->streamDao);
	}

	protected function createComponentImageNew($name) {
		return new Frm\ImageNewForm($this->imageDao, $this->galleryID, $this, $name);
	}

	public function createComponentNewCompImageForm($name) {
		return new Frm\NewCompetitionImageForm($this->userGalleryDao, $this->userImageDao, $this->streamDao, $this->galleryID, $this->usersCompetitionsDao, $this->competitionsImagesDao, $this, $name);
	}

	public function createComponentJs() {
		$files = new FileCollection(WWW_DIR . '/js');
		$files->addFiles(array(
			'netteForms.js'
		));

		$compiler = Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new JavaScriptPacker($code, "None");
			return $packer->pack();
		});
		return new JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	public function handleincLike($id_confession) {

	}

	public function handledecLike($id_confession) {

	}

	public function handleincComment($id_confession) {

	}

	public function handledecComment($id_confession) {

	}

}
