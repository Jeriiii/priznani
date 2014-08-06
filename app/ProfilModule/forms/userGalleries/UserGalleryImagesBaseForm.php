<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use NetteExt\Image;
use NetteExt\Form\Upload\UploadImage;
use NetteExt\Path\GalleryPathCreator;
use NetteExt\Arrays;
use NetteExt\File;
use Nette\ArrayHash;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\StreamDao;

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

	const IMAGE_NAME = "imageName";
	const IMAGE_FILE = "imageFile";
	const IMAGE_DESCRIPTION = "imageDescription";

	/**
	 * @var int Pokud má uživatel alespoň 3 schválené fotky, schvaluj další automaticky
	 */
	const AllowLimitForImages = 3;

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, StreamDao $streamDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->streamDao = $streamDao;
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
	 * Přidá určitý počet polí pro vložení obrázku do formuláře
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
	 * @param array $images Obrázky k uložení v předzpracovaném poli.
	 * @param \Nette\ArrayHash $values Všechny hodnoty z formuláře.
	 * @param int $userID ID uživatele.
	 * @param int $galleryID ID galerie.
	 * @return boolean TRUE pokud byly fotky automaticky schválené, jinak FALSE
	 */
	public function saveImages(array $images, $userID, $galleryID) {
		//získání počtu user obrázků, které mají allow 1
		$allowedImagesCount = $this->userImageDao->countAllowedImages($userID);

		//pokud je 3 a více schválených, schválí i nově přidávanou
		$allow = $allowedImagesCount >= self::AllowLimitForImages ? TRUE : FALSE;

		foreach ($images as $image) {
			if ($image[self::IMAGE_FILE]->isOK()) {
				//název obrázku zadaný uživatelem
				$name = !empty($image[self::IMAGE_NAME]) ? $image[self::IMAGE_NAME] : "";
				//koncovka souboru
				$suffix = $this->suffix($image[self::IMAGE_FILE]->getName());
				//popis obrázku zadaný uživatelem
				$description = !empty($image[self::IMAGE_DESCRIPTION]) ? $image[self::IMAGE_DESCRIPTION] : "";

				//Uloží obrázek do databáze
				$imageDB = $this->saveImageToDB($galleryID, $name, $description, $suffix, $allow);

				//nahraje soubor
				$this->upload($image[self::IMAGE_FILE], $imageDB->id, $suffix, $galleryID, $userID, 500, 700, 100, 130);
				unset($image);
			}
		}

		return $allow;
	}

	/**
	 * Uloží obrázek do databáze.
	 * @param int $galleryID ID galerie.
	 * @param string $name Název obrázku zadaný uživatelem.
	 * @param string $description Popis obrázku zadaný uživatelem.
	 * @param string $suffix Koncovka obrázku.
	 * @param boolean $allow Automatické schvalování obrázků.
	 * @return Database\Table\IRow
	 */
	private function saveImageToDB($galleryID, $name, $description, $suffix, $allow) {
		$image = $this->userImageDao->insertImage($name, $suffix, $description, $galleryID, $allow);
		$this->userGalleryDao->updateBestAndLastImage($galleryID, $image->id, $image->id);

		//aktualizace streamu - vyhodí galerii ve streamu nahoru
		if ($allow) {
			$this->streamDao->aliveGallery($image->galleryID, $image->gallery->userID);
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
		for ($i = 0; $i < $num; $i++) {
			$images[$i][self::IMAGE_FILE] = $values[self::IMAGE_FILE . $i];
			$images[$i][self::IMAGE_NAME] = Arrays::getVal($values, self::IMAGE_NAME . $i);
			$images[$i][self::IMAGE_DESCRIPTION] = Arrays::getVal($values, self::IMAGE_DESCRIPTION . $i);
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

	public function genderCheckboxValidation($form) {
		$values = $form->getValues();

		if (empty($values['man']) && empty($values['women']) && empty($values['couple']) && empty($values['more'])) {
			$form->addError("Musíte vybrat jednu z kategorií");
		}
	}

	public function genderCheckboxes() {
		$this->addCheckbox('man', 'jen muži');

		$this->addCheckbox('women', 'jen ženy');

		$this->addCheckbox('couple', 'pár');

		$this->addCheckbox('more', '3 a více');
	}

}
