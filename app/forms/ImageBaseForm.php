<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use NetteExt\Image;
use Nette\Http\FileUpload;

class ImageBaseForm extends Form {
	/* prefixy obrázků */

	const PREFFIX_GALLERY_SCREEN = "galScrn";
	const PREFFIX_MIN_SQUERE = "minSqr";
	const PREFFIX_MIN = "min";

	/* složky galerií */
	const FOLDER_GALLERY = "galleries";
	const FOLDER_USER_GALLERY = "userGalleries";

	public function upload(FileUpload $image, $id, $suffix, $folder, $max_height, $max_width, $max_minheight, $max_minwidth) {
		if ($image->isOK() & $image->isImage()) {
			/* uložení souboru a renačtení */

			$path = self::getImgPath($id, $suffix, $folder);
			$image->move($path);

			/* kontrola velikosti obrázku, proporcionální zmenšení a uložení */
			self::saveImgGalScrn($id, $suffix, $folder, $max_width, $max_height);

			/* vytvoření ořezu 200x200px */
			self::saveImgMinSqr($id, $suffix, $folder);

			/* vytvoří a uloží miniaturu */
			self::saveImgMin($id, $suffix, $folder, $max_minwidth, $max_minheight);

			/* přeuloží originální obrázek a smaže starý */
			self::resaveImgOriginal($image, $path);
		} else {
			$this->addError('Chyba při nahrávání souboru. Zkuste to prosím znovu.');
		}
	}

	/**
	 * Vrátí suffix obrázku.
	 * @param string $filename Celý název souboru i s příponou.
	 * @return string Přípona souboru.
	 */
	public function suffix($filename) {
		return pathinfo($filename, PATHINFO_EXTENSION);
	}

	/*	 * ******************* METODY CO VRÁTÍ CESTU K ******************* */

	/**
	 * Vrátí celou cestu k ORIGINÁLNÍMU obrázku
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/50
	 * @return string Celá cesta k ORIGINÁLNÍMU obrázku.
	 */
	public static function getImgPath($imageID, $imageSuffix, $folder) {
		return self::getBaseImgPath($imageID, $imageSuffix, "", $folder);
	}

	/**
	 * Vrátí celou cestu k obrázku SCREEN
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/50
	 * @return string Celá cesta k obrázku SCREEN.
	 */
	public static function getImgScrnPath($imageID, $imageSuffix, $folder) {
		return self::getBaseImgPath($imageID, $imageSuffix, self::PREFFIX_GALLERY_SCREEN, $folder);
	}

	/**
	 * Vrátí celou cestu k obrázku miniatury
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/50
	 * @return string Celá cesta k obrázku miniatury.
	 */
	public static function getImgMinPath($imageID, $imageSuffix, $folder) {
		return self::getBaseImgPath($imageID, $imageSuffix, self::PREFFIX_MIN, $folder);
	}

	/**
	 * Vrátí celou cestu k obrázku miniatury ořízlou do čtverce
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/50
	 * @return string Celá cesta k obrázku miniatury ořízlou do čtverce.
	 */
	public static function getImgMinSqrPath($imageID, $imageSuffix, $folder) {
		return self::getBaseImgPath($imageID, $imageSuffix, self::PREFFIX_MIN_SQUERE, $folder);
	}

	/**
	 * Vrátí celou cestu k obrázku, slouží jen pro vnitřní použití. Pro
	 * cestu ke konkrétnímu oprázku použijte jednu z public metod.
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param type $preffix Preffix obrázku např. min|galScrn|... .
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/50
	 * @return string Celá cesta k obrázku.
	 */
	private static function getBaseImgPath($imageID, $imageSuffix, $preffix, $folder) {
		return self::getBasePath($folder) . $preffix . $imageID . '.' . $imageSuffix;
	}

	/**
	 * Vrátí cestu ke složce, kde jsou uloženy všechny obrázky
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/50
	 * @return string Cesta do galerie.
	 */
	public static function getBasePath($folder) {
		return WWW_DIR . "/images/" . $folder . "/";
	}

	/*	 * *********************** VYTVOŘENÍ A ULOŽENÍ OBRÁZKU **************** */

	/**
	 * Vytvoření a uložení PROPORCIONÁLNÍ miniatury.
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/50
	 * @param string $maxWidth Maximální šířka miniatury.
	 * @param string $maxHeight Maximální výška miniatury.
	 */
	public static function saveImgMin($imageID, $imageSuffix, $folder, $maxWidth, $maxHeight) {
		$path = self::getImgPath($imageID, $imageSuffix, $folder);
		$pathMin = self::getImgMinPath($imageID, $imageSuffix, $folder);

		$image = Image::fromFile($path);
		$image->resize($maxWidth, $maxHeight);
		$image->save($pathMin);
	}

	/**
	 * Vytvoření a uložení náhledu obrázku.
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/50
	 * @param string $maxWidth Maximální šířka obrázku.
	 * @param string $maxHeight Maximální výška obrázku.
	 */
	public static function saveImgGalScrn($imageID, $imageSuffix, $folder, $maxWidth, $maxHeight) {
		$path = self::getImgPath($imageID, $imageSuffix, $folder);
		$pathGalleryScreen = self::getImgScrnPath($imageID, $imageSuffix, $folder);

		$image = Image::fromFile($path);
		$image->resize($maxWidth, $maxHeight);
		$image->save($pathGalleryScreen);
	}

	/**
	 * Vytvoření a uložení ČTVERCOVÉ miniatury 200 x 200 px.
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/50
	 */
	public static function saveImgMinSqr($imageID, $imageSuffix, $folder) {
		$path = self::getImgPath($imageID, $imageSuffix, $folder);
		$pathSqr = self::getImgMinSqrPath($imageID, $imageSuffix, $folder);

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
