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
use NetteExt\Watermarks;
use NetteExt\Path\ImagePathCreator;
use NetteExt\Path\GalleryPathCreator;
use Nette\Http\FileUpload;

/**
 * Základní formulář pro nahrávání a ukládání obrázků
 */
class UserGalleryImagesBaseForm extends BaseForm {

	/**
	 * @var \POS\Model\UserGalleryDao
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\UserImageDao
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\StreamDao
	 */
	public $streamDao;

	const IMAGE_NAME = "ImageName";
	const IMAGE_FILE = "ImageFile";
	const IMAGE_DESCRIPTION = "ImageDescription";

	/**
	 * @var int Pokud má uživatel alespoň 1 schválené fotky, schvaluj další automaticky
	 */
	const AllowLimitForImages = 1;

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, StreamDao $streamDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->streamDao = $streamDao;
	}

	/**
	 * Uloží obrázek do souboru, pokud je v pořádku.
	 * @param FileUpload $image Instance nahraného obrázku
	 * @param int $id ID obrázku v databázi.
	 * @param string $suffix Koncovka obrázku v databázi.
	 * @param string $folder Složka galerie.
	 * @param int $galleryID ID galerie.
	 * @param int $userID ID uživatele.
	 * @param int $max_height Maximální výška screenu.
	 * @param int $max_width Maximální šířka screenu.
	 * @param int $max_minheight Maximální výška miniatury.
	 * @param int $max_minwidth Maximální šířka miniatury.
	 * @param bool $addWatermark přidání/nepřidání watermarku
	 */
	private function upload($image, $id, $suffix, $galleryID, $userID, $max_height, $max_width, $max_minheight, $max_minwidth, $addWatermarks = TRUE) {
		if ($image instanceof Image || ($image->isOK() && $image->isImage())) {
			/* uložení souboru a renačtení */
			$galleryPath = GalleryPathCreator::getUserGalleryPath($galleryID, $userID);
			File::createDir($galleryPath);

			$galleryFolder = GalleryPathCreator::getUserGalleryFolder($galleryID, $userID);

			if ($image instanceof FileUpload) {
				$paths = UploadImage::upload($image, $id, $suffix, $galleryFolder, $max_height, $max_width, $max_minheight, $max_minwidth);
			} else if ($image instanceof Image) {
				$paths = UploadImage::moveImage($image, $id, $suffix, $galleryFolder, $max_height, $max_width, $max_minheight, $max_minwidth);
			} else {
				throw new Exception('variable $image must by instance of FileUpload or Image');
			}

			if ($addWatermarks) {
				foreach ($paths as $path) {
					Watermarks::addFullWatermark($path, WWW_DIR . '/images/watermarks/mark_pos.png');
					Watermarks::addBottomRightWatermark($path, WWW_DIR . '/images/watermarks/domain_pos.png', 10, 10, 100, 3);
				}
			}
		} else {
			$this->addError('Chyba při nahrávání souboru. Zkuste to prosím znovu.');
		}
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

		for ($i = 0; $i < $count; $i++) {
			$this->addUpload($prefixImgName . self::IMAGE_FILE . $i, 'Přidat fotku:')
				->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 * 1024)
				->addCondition(Form::MIME_TYPE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/jpg,image/png,image/jpeg,image/gif');
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
		//získání počtu user obrázků, které mají allow 1
		$allowedImagesCount = $this->userImageDao->countAllowedImages($userID);

		if ($profilePhoto == TRUE) {
			$allow = TRUE;
		} else {
			//pokud je 1 a více schválených, schválí i nově přidávanou
			$allow = $allowedImagesCount >= self::AllowLimitForImages ? TRUE : FALSE;
		}

		foreach ($images as $image) {
			if ($image[self::IMAGE_FILE] instanceof Image || $image[self::IMAGE_FILE]->isOK()) {
				//název obrázku zadaný uživatelem
				$name = !empty($image[self::IMAGE_NAME]) ? $image[self::IMAGE_NAME] : "";
				//koncovka souboru
				if ($image[self::IMAGE_FILE] instanceof Image) {
					$suffix = $this->suffix($image[self::IMAGE_NAME]);
					$name = '';
				} else {
					$suffix = $this->suffix($image[self::IMAGE_FILE]->getName());
				}

				//popis obrázku zadaný uživatelem
				$description = !empty($image[self::IMAGE_DESCRIPTION]) ? $image[self::IMAGE_DESCRIPTION] : "";

				//Uloží obrázek do databáze
				$imageDB = $this->saveImageToDB($galleryID, $name, $description, $suffix, $allow, $profilePhoto);

				//nahraje soubor
				$this->upload($image[self::IMAGE_FILE], $imageDB->id, $suffix, $galleryID, $userID, 525, 700, 100, 130);

				//zaznamenání velikosti screnu do proměných width/heightGalScrn
				$this->changeSizeGalScrnDB($galleryID, $userID, $imageDB->id, $suffix);

				unset($image);
			}
		}

		return $allow;
	}

	/**
	 * Zaznamenání velikosti gal. screenu do DB po resizu obrázku.
	 * @param int $galleryID ID galerie.
	 * @param int $userID ID uživatele.
	 * @param int $imageID ID obrázku.
	 * @param string $suffix Přípona obrázku.
	 */
	private function changeSizeGalScrnDB($galleryID, $userID, $imageID, $suffix) {
		$galleryFolder = GalleryPathCreator::getUserGalleryFolder($galleryID, $userID);
		$imagePath = ImagePathCreator::getImgScrnPath($imageID, $suffix, $galleryFolder);
		$imageFile = Image::fromFile($imagePath);
		$this->userImageDao->update($imageID, array(
			UserImageDao::COLUMN_GAL_SCRN_HEIGHT => $imageFile->height,
			UserImageDao::COLUMN_GAL_SCRN_WIDTH => $imageFile->width,
		));
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
	 * Uloží obrázek do databáze.
	 * @param int $galleryID ID galerie.
	 * @param string $name Název obrázku zadaný uživatelem.
	 * @param string $description Popis obrázku zadaný uživatelem.
	 * @param string $suffix Koncovka obrázku.
	 * @param boolean $allow Automatické schvalování obrázků.
	 * @param boolean $profilePhoto TRUE = jde o profilovou fotku jinak FALSE
	 * @return Database\Table\IRow
	 */
	private function saveImageToDB($galleryID, $name, $description, $suffix, $allow, $profilePhoto = FALSE) {
		$approved = $allow == TRUE ? 1 : 0;
		$checkApproved = $approved;
		$image = $this->userImageDao->insertImage($name, $suffix, $description, $galleryID, $approved, $checkApproved);
		$this->userGalleryDao->updateBestAndLastImage($galleryID, $image->id, $image->id);

		//aktualizace streamu - vyhodí galerii ve streamu nahoru
		if ($allow) {
			$user = $image->gallery->user;
			$this->streamDao->aliveGallery($image->galleryID, $user->id, $user->property->preferencesID);
		}
		/* nastavení fotky jako profilové */
		if ($profilePhoto) {
			$image->gallery->user->update(array(
				\POS\Model\UserDao::COLUMN_PROFIL_PHOTO_ID => $image->id
			));
		}
		return $image;
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
