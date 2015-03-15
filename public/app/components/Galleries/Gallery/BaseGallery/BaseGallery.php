<?php

/**
 * @author Petr Kukrál <p.kukral@kukral.eu>
 *
 * Základ komponenty, umožňující zobrazení obrázku, listování a další funkce
 */

namespace POSComponent\Galleries\Images;

use POS\Model\ImageDao;
use POS\Model\UserImageDao;
use POSComponent\BaseProjectControl;

class BaseGallery extends BaseProjectControl {
	/* vsechny obrazky z galerie */

	private $images;
	/* aktualni obrazek */
	protected $image;
	/* aktualni galerie */
	protected $gallery;
	/* aktualni domena */
	private $domain;
	/* jsme na priznani z parby */
	private $partymode;
	private $beforeImageID;
	private $afterImageID;

	/**
	 * @var \POS\Model\UserImageDao
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\ImageDao
	 */
	public $imageDao;

	public function __construct($images, $image, $gallery, $domain, $partymode, $parent, $name) {
		parent::__construct($parent, $name);
		$this->images = $images->order("id DESC");
		$this->image = $image;
		$this->gallery = $gallery;
		$this->domain = $domain;
		$this->partymode = $partymode;
	}

	public function renderBaseGallery($templateName) {
		$this->template->partymode = $this->partymode;

		$this->setBeforeAndAfterImage();

		$this->template->beforeImageID = $this->beforeImageID;
		$this->template->image = $this->image;
		$this->template->afterImageID = $this->afterImageID;

		$this->template->gallery = $this->gallery;

		$this->template->images = $this->images;

		$this->template->imageLink = $this->getPresenter()->link("this", array("imageID" => $this->image->id, "galleryID" => null));

		/* pomocí paddingů zarovná obrázek do prostřed obrazovky */
		$this->template->imgPaddingTopBottom = (525 - $this->image->heightGalScrn) / 2;
		$this->template->imgPaddingLeftRight = (700 - $this->image->widthGalScrn) / 2;

		$this->template->setFile(dirname(__FILE__) . '/' . $templateName);
		$this->template->render();
	}

	/**
	 * nastavuje proměnné třídy beforeImageID a afterImageID
	 */
	private function setBeforeAndAfterImage() {
		$imageID = $this->image->id;
		$beforeImageID = FALSE;
		$afterImageID = FALSE;
		$setAfter = FALSE;

		foreach ($this->images as $image) {
			if ($setAfter) {
				$afterImageID = $image->id;
				break;
			}

			if ($image->id == $imageID)
				$setAfter = TRUE; // pri dalsi obratce nastavi nasledujici prvek
			else
				$beforeImageID = $image->id; //nevyplni se pri nalezeni hledaneho obrazku
		}

		$this->beforeImageID = $beforeImageID;
		$this->afterImageID = $afterImageID;
	}

	/**
	 * přepne na další obrázek
	 * @param type $imageID ID dalšího obrázku
	 */
	public function handleNext($imageID) {
		$this->setImage($imageID);
	}

	/**
	 * přepne na předchozí obrázek
	 * @param type $imageID ID předchozího obrázku
	 */
	public function handleBack($imageID) {
		$this->setImage($imageID);
	}

	/**
	 * nastaví nový obrázek po přechodu doleva/doprava jako aktuální a invaliduje
	 * šablonu
	 * @param type $imageID ID obrázku který má být aktuální
	 */
	public function setImage($imageID) {
		$this->image = $this->getImages()->find($imageID);
		$this->invalidateControl();
	}

	/**
	 *
	 * @param type $imageID ID obrázku, který se má odstranit
	 */

	/**
	 * odstranění obrázku
	 * @param type $image záznam obrázku z tabulky, který se má odstranit
	 * @param type $folderPath cesta do složky s obrázkem
	 * @param type $imageFileName jméno souboru obrázku (bez předpony mini, sqr a pod)
	 */
	public function removeImage($image, $folderPath, $imageFileName) {
		$preffixs = array("", "mini", "sqr");

		// mazání souborů
		foreach ($preffixs as $preffix) {
			$path = $folderPath . $preffix . $imageFileName;

			if (file_exists($path)) {
				unlink($path);
			}
		}

		$galleryID = $this->gallery->id;
		$this->setBeforeAndAfterImage();

		$this->getImages()->delete($image->id);

		if (!empty($this->beforeImageID)) {
			$this->presenter->redirect("this", array("imageID" => $this->beforeImageID));
		}
		if (!empty($this->afterImageID)) {
			$this->presenter->redirect("this", array("imageID" => $this->afterImageID));
		}
		$this->presenter->flashMessage("Byl smazán poslední obrázek z galerie.");
		$this->presenter->redirect(":OnePage:", array("galleryID" => $galleryID));
	}

	protected function setUserImageDao(UserImageDao $userImageDao) {
		$this->userImageDao = $userImageDao;
	}

	protected function setImageDao(ImageDao $imageDao) {
		$this->imageDao = $imageDao;
	}

	/**
	 * vrátí tabulku s obrázky
	 * @return \POS\Model\BaseGalleryDao
	 */
	protected function getImages() {
		if (isset($this->userImageDao)) {
			return $this->userImageDao;
		}
		if (isset($this->imageDao)) {
			return $this->imageDao;
		}

		throw new Exception("You must set Dao");
	}

	protected function createComponentAddToFBPageControl() {
		return new AddToFBPage($imageID);
	}

}
