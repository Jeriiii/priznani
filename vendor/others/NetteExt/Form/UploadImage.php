<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt\Form\Upload;

use Nette\Http\FileUpload;
use NetteExt\Path\ImagePathCreator;
use NetteExt\Image;

/**
 * Slouží pro nahrávání souborů
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UploadImage extends UploadFile {

	/**
	 * Uloží obrázek do souboru
	 * @param \Form\Upload\FileUpload $image Instance nahraného obrázku
	 * @param int $id ID obrázku v databázi.
	 * @param string $suffix Koncovka obrázku v databázi.
	 * @param string $folder Složka galerie.
	 * @param int $max_height Maximální výška screenu.
	 * @param int $max_width Maximální šířka screenu.
	 * @param int $max_minheight Maximální výška miniatury.
	 * @param int $max_minwidth Maximální šířka miniatury.
	 */
	public static function upload(FileUpload $image, $id, $suffix, $folder, $max_height, $max_width, $max_minheight, $max_minwidth) {
		$path = ImagePathCreator::getImgPath($id, $suffix, $folder);
		$image->move($path);

		/* kontrola velikosti obrázku, proporcionální zmenšení a uložení */
		self::saveImgGalScrn($id, $suffix, $folder, $max_width, $max_height);

		/* vytvoření ořezu 200x200px */
		self::saveImgMinSqr($id, $suffix, $folder);

		/* vytvoří a uloží miniaturu */
		self::saveImgMin($id, $suffix, $folder, $max_minwidth, $max_minheight);

		/* přeuloží originální obrázek a smaže starý */
		self::resaveImgOriginal($image, $path);

		return $path;
	}

	/*	 * *********************** VYTVOŘENÍ A ULOŽENÍ OBRÁZKU **************** */

	/**
	 * Vytvoření a uložení PROPORCIONÁLNÍ miniatury.
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/3/50
	 * @param string $maxWidth Maximální šířka miniatury.
	 * @param string $maxHeight Maximální výška miniatury.
	 */
	public static function saveImgMin($imageID, $imageSuffix, $folder, $maxWidth, $maxHeight) {
		$path = ImagePathCreator::getImgPath($imageID, $imageSuffix, $folder);
		$pathMin = ImagePathCreator::getImgMinPath($imageID, $imageSuffix, $folder);

		$image = Image::fromFile($path);
		$image->resize($maxWidth, $maxHeight);
		$image->save($pathMin);
	}

	/**
	 * Vytvoření a uložení náhledu obrázku.
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/3/50
	 * @param string $maxWidth Maximální šířka obrázku.
	 * @param string $maxHeight Maximální výška obrázku.
	 */
	public static function saveImgGalScrn($imageID, $imageSuffix, $folder, $maxWidth, $maxHeight) {
		$path = ImagePathCreator::getImgPath($imageID, $imageSuffix, $folder);
		$pathGalleryScreen = ImagePathCreator::getImgScrnPath($imageID, $imageSuffix, $folder);

		$image = Image::fromFile($path);
		$image->resize($maxWidth, $maxHeight);
		$image->save($pathGalleryScreen);
	}

	/**
	 * Vytvoření a uložení ČTVERCOVÉ miniatury 200 x 200 px.
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/3/50
	 */
	public static function saveImgMinSqr($imageID, $imageSuffix, $folder) {
		$path = ImagePathCreator::getImgPath($imageID, $imageSuffix, $folder);
		$pathSqr = ImagePathCreator::getImgMinSqrPath($imageID, $imageSuffix, $folder);

		$image = Image::fromFile($path);
		$image->resizeMinSite(200);
		$image->cropSqr(200);
		$image->save($pathSqr);
	}

	/**
	 * Uložení originálního obrázku.
	 * @param Nette\Http\FileUpload Uploadovaný obrázek.
	 * @param string $path Cesta k uploadovanému obrázku.
	 */
	private static function resaveImgOriginal(FileUpload $image, $path) {
		$image = Image::fromFile($path);
		unlink($path);
		$image->save($path);
	}

}
