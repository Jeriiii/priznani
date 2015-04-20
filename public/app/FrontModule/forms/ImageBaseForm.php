<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use NetteExt\Image;
use Nette\Http\FileUpload;
use NetteExt\Form\Upload\UploadImage;

class ImageBaseForm extends Form {

	/**
	 * Zkontroluje obrázek a uloží ho do souboru
	 * @param \Form\Upload\FileUpload $image Instance nahraného obrázku
	 * @param int $id ID obrázku v databázi.
	 * @param string $suffix Koncovka obrázku v databázi.
	 * @param string $folder Složka galerie.
	 * @param int $max_height Maximální výška screenu.
	 * @param int $max_width Maximální šířka screenu.
	 * @param int $max_minheight Maximální výška miniatury.
	 * @param int $max_minwidth Maximální šířka miniatury.
	 */
	public function upload(FileUpload $image, $id, $suffix, $folder, $max_height, $max_width, $max_minheight, $max_minwidth) {
		if ($image->isOK() & $image->isImage()) {
			$infoImg = UploadImage::upload($image, $id, $suffix, $folder, $max_height, $max_width, $max_minheight, $max_minwidth);
			$scrnPath = $infoImg[0];
		} else {
			$this->addError('Chyba při nahrávání souboru. Zkuste to prosím znovu.');
			$scrnPath = FALSE;
		}
		return $scrnPath;
	}

	/**
	 * Vrátí suffix obrázku.
	 * @param string $filename Celý název souboru i s příponou.
	 * @return string Přípona souboru.
	 */
	public function suffix($filename) {
		return pathinfo($filename, PATHINFO_EXTENSION);
	}

}
