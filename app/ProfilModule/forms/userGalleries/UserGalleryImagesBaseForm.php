<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use NetteExt\Image;
use NetteExt\Form\Upload\UploadImage;
use NetteExt\Path\GalleryPathCreator;
use NetteExt\File;
use Nette\ArrayHash;

/**
 * Základní formulář pro nahrávání a ukládání obrázků
 */
class UserGalleryImagesBaseForm extends BaseBootstrapForm {

	/**
	 * @var \POS\Model\UserGalleryDao
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\UserImageDao
	 */
	public $userImageDao;

	const IMAGE_NAME = "imageName";
	const IMAGE_FILE = "imageFile";
	const IMAGE_DESCRIPTION = "imageDescription";

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
	}

	/**
	 * Uloží obrázek do souboru, pokud je v pořádku.
	 * @param \Form\Upload\FileUpload $image Instance nahraného obrázku
	 * @param int $id ID obrázku v databázi.
	 * @param string $suffix Koncovka obrázku v databázi.
	 * @param string $folder Složka galerie.
	 * @param int $galleryID ID galerie.
	 * @param int $userID ID uživatele.
	 * @param int $max_height Maximální výška screenu.
	 * @param int $max_width Maximální šířka screenu.
	 * @param int $max_minheight Maximální výška miniatury.
	 * @param int $max_minwidth Maximální šířka miniatury.
	 */
	private function upload($image, $id, $suffix, $galleryID, $userID, $max_height, $max_width, $max_minheight, $max_minwidth) {
		if ($image->isOK() & $image->isImage()) {
			/* uložení souboru a renačtení */
			$galleryPath = GalleryPathCreator::getUserGalleryPath($galleryID, $userID);
			File::createDir($galleryPath);

			$galleryFolder = GalleryPathCreator::getUserGalleryFolder($galleryID, $userID);
			UploadImage::upload($image, $id, $suffix, $galleryFolder, $max_height, $max_width, $max_minheight, $max_minwidth);
		} else {
			$this->addError('Chyba při nahrávání souboru. Zkuste to prosím znovu.');
		}
	}

	public function suffix($filename) {
		return pathinfo($filename, PATHINFO_EXTENSION);
	}

	/**
	 * Přidá určitý počet polý pro vložení obrázku do formuláře
	 * @param type $count Počet polý obrázků.
	 * @param type $displayName Má se zobrazit pole pro název obrázku.
	 * @param type $dislplayDesc Má se zobrazit pole pro popis obrázku.
	 */
	public function addImageFields($count, $displayName = TRUE, $dislplayDesc = TRUE) {
		for ($i = 0; $i < $count; $i++) {
			$this->addUpload(self::IMAGE_FILE . $i, 'Přidat fotku:')
				->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 * 1024)
				->addCondition(Form::MIME_TYPE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/jpg,image/png,image/jpeg,image/gif');
			if ($displayName) {
				$this->addText(self::IMAGE_NAME . $i, 'Jméno:')
					->addConditionOn($this[self::IMAGE_FILE . $i], Form::FILLED)
					->addRule(Form::MAX_LENGTH, "Maximální délka jména fotky je %d znaků", 40);
			}
			if ($dislplayDesc) {
				$this->addText(self::IMAGE_DESCRIPTION . $i, 'Popis:')
					->addConditionOn($this[self::IMAGE_FILE . $i], Form::FILLED)
					->addRule(Form::MAX_LENGTH, "Maximální délka popisu fotky je %d znaků", 500);
			}
		}
	}

	/**
	 * Uloží obrázky do databáze a na disk.
	 * @param array $images Obrázky k uložení.
	 * @param \Nette\ArrayHash $values Všechny hodnoty z formuláře.
	 * @param type $userID ID uživatele.
	 * @param type $galleryID ID galerie.
	 */
	public function saveImages(array $images, $userID, $galleryID) {
		foreach ($images as $image) {
			if ($image[self::IMAGE_FILE]->isOK()) {
				$imageName = !empty($image[self::IMAGE_NAME]) ? $image[self::IMAGE_NAME] : "";
				$imageSuffix = $this->suffix($image[self::IMAGE_FILE]->getName());
				$imageDescription = !empty($image[self::IMAGE_DESCRIPTION]) ? $image[self::IMAGE_DESCRIPTION] : "";

				$imageID = $this->userImageDao->insertImage($imageName, $imageSuffix, $imageDescription, $galleryID)->id;
				$this->userGalleryDao->updateBestAndLastImage($imageID, $imageID);

				$this->upload($image, $imageID, $imageSuffix, $galleryID, $userID, 500, 700, 100, 130);
				unset($image);
			}
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
		for ($i = 0; $i < $num; $i++) {
			$images[$i][self::IMAGE_NAME] = $values[self::IMAGE_NAME . $i];
			$images[$i][self::IMAGE_FILE] = $values[self::IMAGE_FILE . $i];
			$images[$i][self::IMAGE_DESCRIPTION] = $values[self::IMAGE_DESCRIPTION . $i];
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
		foreach ($images as $item) {
			if ($item->error == 0) {
				/* alespoň jedna fotka je vyplněná */
				return TRUE;
			}
		}
		return FALSE;
	}

}
