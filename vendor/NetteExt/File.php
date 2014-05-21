<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

/**
 * Třída pro práci se soubory
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace NetteExt;

use NetteExt\Path\ImagePathCreator;

class File extends Nette\Object {

	/**
	 * Vytvoř složku když neexistuje.
	 * @param string $dirPath Celá cesta k adresáři.
	 * @param int $rules Práva nové složky.
	 */
	public static function createDir($dirPath, $rules = 0752) {
		if (!file_exists($dirPath)) {
			mkdir($dirPath, $rules);
		}
	}

	/**
	 * Odstraní soubor.
	 * @param type $filePath Celá cesta k souboru.
	 * @param type $ifExist Odstraní soubor jen když existuje.
	 */
	public static function remove($filePath, $ifExist = TRUE) {
		if ($ifExist && file_exists($filePath)) {
			rmdir($filePath);
		}
	}

	/**
	 * Odstraní originální obrázek, když existuje
	 * @param type $imageID ID obrázku.
	 * @param type $imageSuffix Koncovka Obrázku.
	 * @param type $galleryFolder Adresář danné galerie.
	 */
	public static function removeImage($imageID, $imageSuffix, $galleryFolder) {
		self::removeMinImage($imageID, $imageSuffix, $galleryFolder);
		self::removeOriginalImage($imageID, $imageSuffix, $galleryFolder);
		self::removeScrnImage($imageID, $imageSuffix, $galleryFolder);
		self::removeSqrMinImage($imageID, $imageSuffix, $galleryFolder);
	}

	/**
	 * Odstraní originální obrázek, když existuje
	 * @param type $imageID ID obrázku.
	 * @param type $imageSuffix Koncovka Obrázku.
	 * @param type $galleryFolder Adresář danné galerie.
	 */
	public static function removeOriginalImage($imageID, $imageSuffix, $galleryFolder) {
		$imagePath = ImagePathCreator::getImgPath($imageID, $imageSuffix, $galleryFolder);
		self::remove($imagePath);
	}

	/**
	 * Odstraní miniaturu, když existuje
	 * @param type $imageID ID obrázku.
	 * @param type $imageSuffix Koncovka Obrázku.
	 * @param type $galleryFolder Adresář danné galerie.
	 */
	public static function removeMinImage($imageID, $imageSuffix, $galleryFolder) {
		$imagePath = ImagePathCreator::getImgMinPath($imageID, $imageSuffix, $galleryFolder);
		self::remove($imagePath);
	}

	/**
	 * Odstraní čtvercovou miniaturu, když existuje
	 * @param type $imageID ID obrázku.
	 * @param type $imageSuffix Koncovka Obrázku.
	 * @param type $galleryFolder Adresář danné galerie.
	 */
	public static function removeSqrMinImage($imageID, $imageSuffix, $galleryFolder) {
		$imagePath = ImagePathCreator::getImgMinSqrPath($imageID, $imageSuffix, $galleryFolder);
		self::remove($imagePath);
	}

	/**
	 * Odstraní náhled obrázku, když existuje
	 * @param type $imageID ID obrázku.
	 * @param type $imageSuffix Koncovka Obrázku.
	 * @param type $galleryFolder Adresář danné galerie.
	 */
	public static function removeScrnImage($imageID, $imageSuffix, $galleryFolder) {
		$imagePath = ImagePathCreator::getImgScrnPath($imageID, $imageSuffix, $galleryFolder);
		self::remove($imagePath);
	}

}
