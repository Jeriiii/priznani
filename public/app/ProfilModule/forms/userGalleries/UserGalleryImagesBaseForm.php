<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use NetteExt\Image;
use NetteExt\Form\Upload\UploadImage;
use NetteExt\Arrays;
use NetteExt\File;
use Nette\ArrayHash;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\StreamDao;
use NetteExt\Uploader\ImageUploader;
use NetteExt\Uploader\ImagesToUpload;
use NetteExt\Uploader\ImageToUpload;

/**
 * Základní formulář pro nahrávání a ukládání obrázků
 */
class UserGalleryImagesBaseForm extends BaseForm {

	/** @var \POS\Model\UserGalleryDao */
	public $userGalleryDao;

	/** @var \POS\Model\UserImageDao */
	public $userImageDao;

	/** @var \POS\Model\StreamDao */
	public $streamDao;

	/** @var ImageUploader Třída pro nahrávání obrázků. */
	private $imageUploader;

	const IMAGE_NAME = "ImageName";
	const IMAGE_FILE = "ImageFile";
	const IMAGE_DESCRIPTION = "ImageDescription";

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, StreamDao $streamDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->streamDao = $streamDao;
	}

	public function suffix($filename) {
		return pathinfo($filename, PATHINFO_EXTENSION);
	}

	/**
	 * Přidá určitý počet polí pro vložení obrázku do formuláře
	 * @param int $count Počet polý obrázků.
	 * aby nedocházelo ke kolizím názvů.
	 * @param boolean $displayName Má se zobrazit pole pro název obrázku.
	 * @param boolean $dislplayDesc Má se zobrazit pole pro popis obrázku.
	 */
	public function addImageFields($count, $displayName = TRUE, $dislplayDesc = TRUE) {
		/* Pro unikátnost názvu pole - pro testy */
		$prefixImgName = $this->getImgNamePrefix();
		$imageAlert = 'Přílohou musí být obrázek formátu .jpg, .gif nebo .png';
		if ($this->deviceDetector->isMobile()) {
			$imageAlert = $imageAlert . ' Je možné, že typ vašeho mobilu nepodporuje nahrávání formátu JPG.';
		}

		for ($i = 0; $i < $count; $i++) {
			$this->addUpload($prefixImgName . self::IMAGE_FILE . $i, 'Přidat fotku:')
				->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 * 1024)
				->addCondition(Form::FILLED)
				->addRule(Form::IMAGE, $imageAlert);
			if ($displayName) {
				$this->addText($prefixImgName . self::IMAGE_NAME . $i, 'Název:')
					->addConditionOn($this[$prefixImgName . self::IMAGE_FILE . $i], Form::FILLED)
					->addRule(Form::MAX_LENGTH, "Maximální délka jména fotky je %d znaků", 40);
			}
			if ($dislplayDesc) {
				$this->addText($prefixImgName . self::IMAGE_DESCRIPTION . $i, 'Popis:')
					->addConditionOn($this[$prefixImgName . self::IMAGE_FILE . $i], Form::FILLED)
					->addRule(Form::MAX_LENGTH, "Maximální délka popisu fotky je %d znaků", 500);
			}
		}
	}

	/**
	 * Uloží obrázky do databáze a na disk.
	 * @param array $images Obrázky k uložení v předzpracovaném poli.
	 * @param \Nette\ArrayHash $values Všechny hodnoty z formuláře.
	 * @param int $userID ID uživatele.
	 * @param int $galleryID ID galerie.
	 * @param boolean $profilePhoto TRUE = jde o profilovou fotku jinak FALSE
	 * @return boolean TRUE pokud byly fotky automaticky schválené, jinak FALSE
	 */
	public function saveImages(array $images, $userID, $galleryID, $profilePhoto = FALSE) {
		$imagesToUpload = new ImagesToUpload($userID, $galleryID);

		foreach ($images as $image) {
			$imageToUpload = new ImageToUpload($image[self::IMAGE_FILE], $image[self::IMAGE_NAME], $image[self::IMAGE_DESCRIPTION]);

			if ($profilePhoto) {
				$imageToUpload->setProfileType();
			}

			$imagesToUpload->addImage($imageToUpload);
		}

		return $this->saveImagesFast($imagesToUpload);
	}

	/**
	 * Lepší a novější a snazší způsob nahrávání obrázků.
	 * @param ImagesToUpload $imagesToUpload Obrázky co se mají nahrát.
	 * @return boolean TRUE pokud byly fotky automaticky schválené, jinak FALSE
	 */
	public function saveImagesFast(ImagesToUpload $imagesToUpload) {
		$uploader = new ImageUploader($this->userGalleryDao, $this->userImageDao, $this->streamDao);
		$allow = $uploader->saveImages($imagesToUpload);

		return $allow;
	}

	/**
	 * Uloží verifikační formulář do databáze
	 * @param type $values data z formuláře
	 * @param type $userID ID uživatele, jemuž obrázek patří
	 * @param type $galleryID ID galerie, do které obrázek uložíme
	 */
	public function saveVerificationImage($values, $userID, $galleryID) {
		$image = $values->verificationFormImageFile0;
		if ($image->isOK()) {
			//koncovka souboru
			$suffix = $this->suffix($image->getName());
			//Uloží obrázek do databáze
			$imageDB = $this->saveImageToDB($galleryID, "Ověřovací fotka", "", $suffix, 0);

			//nahraje soubor
			$this->upload($image, $imageDB->id, $suffix, $galleryID, $userID, 500, 700, 100, 130);
			unset($image);
		}
	}

	/**
	 * Vraci vsechny obrázky v poli
	 * @param \Nette\ArrayHash $values
	 * @param int $num
	 * @return array - pole obrázků
	 */
	public function getArrayWithImages(ArrayHash $values, $num) {
		$images = array();
		/* Pro unikátnost názvu pole - pro testy */
		$prefixImgName = $this->getImgNamePrefix();
		for ($i = 0; $i < $num; $i++) {
			$images[$i][self::IMAGE_FILE] = $values[$prefixImgName . self::IMAGE_FILE . $i];
			$images[$i][self::IMAGE_NAME] = Arrays::getVal($values, $prefixImgName . self::IMAGE_NAME . $i);
			$images[$i][self::IMAGE_DESCRIPTION] = Arrays::getVal($values, $prefixImgName . self::IMAGE_DESCRIPTION . $i);
		}
		return $images;
	}

	/**
	 * Metoda kontroluje zda uzivatel neodeslal formular s nevyplnenou fotkou.
	 * $item->error == 0 znamena vyplnena fotka
	 * @param type $images
	 * @return boolean
	 */
	public function isFillImage(array $images) {
		foreach ($images as $image) {
			if ($image[self::IMAGE_FILE]->isOk()) {
				/* alespoň jedna fotka je vyplněná */
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Vrátí prefix k obrázku - používá se pro unikátní název polí.
	 * @return preffix k obrázku.
	 */
	private function getImgNamePrefix() {
		return $this->name;
	}

}
