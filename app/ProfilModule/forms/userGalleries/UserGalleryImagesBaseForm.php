<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	NetteExt\Image;

class UserGalleryImagesBaseForm extends BaseBootstrapForm {

	const LimitForImages = 3;
	
	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
	}

	public function upload($image, $id, $suffix, $folder, $max_height, $max_width, $max_minheight, $max_minwidth) {
		if ($image->isOK() & $image->isImage()) {
			/* uložení souboru a renačtení */
			$dir = WWW_DIR . "/images/" . $folder . "/";
			if (!file_exists($dir)) {
				mkdir($dir, 0752);
			}
			$file = $id . '.' . $suffix;
			// originální obrázek
			$path = $dir . $file;
			// obrázek pro náhled v galerii
			$pathGalleryScreen = $dir . "galScrn" . $file;
			// čtvercový výřez
			$pathSqr = $dir . "minSqr" . $file;
			// miniatura - proporcionální
			$pathMin = $dir . "min" . $file;

			$image->move($path);

			/* kontrola velikosti obrázku, proporcionální zmenšení */
			$image = Image::fromFile($path);
			$image->resize($max_width, $max_height);
			$image->save($pathGalleryScreen);

			/* vytvoření ořezu 200x200px */
			$image = Image::fromFile($path);
			$image->resizeMinSite(200);
			$image->cropSqr(200);
			$image->save($pathSqr);

			/* vytvoření miniatury */
			$image = Image::fromFile($path);
			$image->resize($max_minwidth, $max_minheight);
			$image->save($pathMin);
		} else {
			$this->addError('Chyba při nahrávání souboru. Zkuste to prosím znovu.');
		}
	}

	public function suffix($filename) {
		return pathinfo($filename, PATHINFO_EXTENSION);
	}

	public function addImagesFile($count, $displayName = TRUE, $dislplayDesc = TRUE) {
		for ($i = 0; $i < $count; $i++) {
			$this->addUpload('foto' . $i, 'Přidat fotku:')
				->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 * 1024)
				->AddCondition(Form::MIME_TYPE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/jpg,image/png,image/jpeg,image/gif');
			if ($displayName) {
				$this->addText('image_name' . $i, 'Jméno:')
					->AddConditionOn($this['foto' . $i], Form::FILLED)
					->addRule(Form::MAX_LENGTH, "Maximální délka jména fotky je %d znaků", 40);
			}
			if ($dislplayDesc) {
				$this->addText('description_image' . $i, 'Popis:')
					->AddConditionOn($this['foto' . $i], Form::FILLED)
					->addRule(Form::MAX_LENGTH, "Maximální délka popisu fotky je %d znaků", 500);
			}
		}
	}

	public function addImages($arr, $values, $uID, $idGallery) {

		foreach ($arr as $key => $image) {
			if ($image->isOK()) {

				$valuesDB['suffix'] = $this->suffix($image->getName());

				$valuesDB['name'] = !empty($values->name) ? $values->name : "";
				$valuesDB['description'] = !empty($values->description) ? $values->description : "";

				$valuesDB['galleryID'] = $idGallery;
				
				//získání user galerii
				$userGalleries = $this->getPres()->context->createUsersGalleries()->where('userID',$uID);
				//counter
				$allowedImagesCount = 0;
				//cyklus prochází obrázky z uživatelových galerií a počítá schválené fotky
				foreach($userGalleries as $userGallery) {
					$allowedImagesCount += count($this->getPres()->context->createUsersImages()->where(array("galleryID" => $userGallery->id, "allow" => 1)));
				}
				//pokud je 3 a více schválených, schválí i nově přidávanou
				if($allowedImagesCount >= self::LimitForImages) {
					$valuesDB["allow"] = 1;
				}
				
				
				
				$id = $this->getPres()->context->createUsersImages()
					->insert($valuesDB);

				$this->getPres()->context->createUsersGalleries()
					->where('id', $idGallery)
					->update(array(
						"bestImageID" => $id,
						"lastImageID" => $id
				));

				$this->upload($image, $id, $valuesDB['suffix'], "userGalleries" . "/" . $uID . "/" . $idGallery, 500, 700, 100, 130);
				unset($image);
			}
		}
	}

	/**
	 * Zjistuji zda mam element, zajinajici na foto0.....n => pak z toho ziskavam pocet fotek
	 * @param type $values
	 * @return int - pocet fotek ve formulari
	 */
	public function getNumberOfPhotos($values) {
		$i = 0;
		foreach ($values as $key => $value) {
			if (strpos($key, 'foto') === 0) {
				$i++;
			}
		}
		return $i;
	}

	/**
	 * Vraci vsechny fotky v poli
	 * @param type $values
	 * @param type $num
	 * @return array - pole fotek
	 */
	public function getArrayWithPhotos($values, $num) {
		$arrayWithPhotos = array();
		for ($i = 0; $i < $num; $i++) {
			$foto = 'foto' . $i;
			array_push($arrayWithPhotos, $values->$foto);
		}
		return $arrayWithPhotos;
	}

	/**
	 * Metoda kontroluje zda uzivatel neodeslal formular s nevyplnenou fotkou. $item->error == 0 znamena vyplnena fotka
	 * @param type $arr
	 * @return boolean
	 */
	public function getOkUploadedPhotos($arr) {
		$ok = false;
		$checked = array();
		foreach ($arr as $item) {
			if (in_array(($item->error != 0), $arr)) {
				array_push($checked, $item);
			}
		}
		//jsou-li vsechny fotky prazdne => vyhodim chybu
		if (count($arr) == count($checked)) {
			$ok = false;
		} else {
			$ok = true;
		}
		return $ok;
	}

}
