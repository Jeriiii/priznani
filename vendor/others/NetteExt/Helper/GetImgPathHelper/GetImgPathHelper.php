<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Vrací celou cestu k obrázku uživatele z tabulky user_images
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace NetteExt\Helper;

use Nette\Http\Url;
use NetteExt\Path\ImagePathCreator;
use NetteExt\Path\GalleryPathCreator;
use Nette\Database\Table\ActiveRow;
use NetteExt\Helper\Image;

/**
 * Vytvoří cestu k obrázku.
 */
class GetImgPathHelper {

	const NAME = "getImgPath";
	const NAME_MIN = "getImgMinPath";
	const NAME_MIN_SQR = "getImgSqrPath";
	const NAME_SCRN = "getImgScrnPath";
	const NAME_STREAM = "getStrmImgPath";

	/* image types */
	const TYPE_IMG = 1;
	const TYPE_MIN_IMG = 2;
	const TYPE_SCRN_IMG = 3;
	const TYPE_MIN_SQR_IMG = 4;
	const TYPE_STREAM = 5;
	/* gallery types */
	const TYPE_GALLERY = "g";
	const TYPE_USER_GALLERY = "ug";
	/* typ defaultní fotky */
	const TYPE_DEF_PHOTO_MAN = 0;
	const TYPE_DEF_PHOTO_WOMAN = 1;
	const TYPE_DEF_PHOTO_COUPLE = 2;

	/** @var \Nette\Http\Url */
	private $url;

	public function __construct(Url $url) {
		$this->url = $url;
	}

	/**
	 * Vrací cestu k uživatelskému obrázku.
	 * @param Nette\Database\Table\ActiveRow $image Řádek z databáze s daným obrázkem.
	 * @param string $galleryType Typ galerie (uživatelská nebo normální)
	 * @return string
	 */
	public function getImgPath($image, $galleryType) {
		return $this->createImgPath($image, $galleryType, self::TYPE_IMG);
	}

	/**
	 * Vrátí cestu k defaultní profilové fotce.
	 * @return string
	 */
	public function getImgDefProf($type = 1) {
		$basePath = $this->getBasePath();
		if ($type == self::TYPE_DEF_PHOTO_MAN) {
			$name = "man.jpg";
		} elseif ($type == self::TYPE_DEF_PHOTO_MAN) {
			$name = "woman.jpg";
		} else {
			$name = "couple.jpg";
		}
		return ImagePathCreator::getBasePath("users", $basePath) . $name;
	}

	/**
	 * Vrací cestu k uživatelskému obrázku.
	 * @param Nette\Database\Table\ActiveRow $streamItem Řádek z databáze ze streamu.
	 * @param string $galleryType Typ galerie (uživatelská nebo normální)
	 * @return string
	 */
	public function getStreamImgPath($streamItem, $galleryType) {
		return $this->createImgPath($streamItem, $galleryType, self::TYPE_STREAM);
	}

	/**
	 * Vrací cestu k miniatuře uživatelského obrázku.
	 * @param Nette\Database\Table\ActiveRow $image Řádek z databáze s daným obrázkem.
	 * @param string $galleryType Typ galerie (uživatelská nebo normální)
	 * @return string
	 */
	public function getImgMinPath($image, $galleryType) {
		return $this->createImgPath($image, $galleryType, self::TYPE_MIN_IMG);
	}

	/**
	 * Vrací cestu k miniatuře ořízlé do čtverce uživatelského obrázku.
	 * @param Nette\Database\Table\ActiveRow $image Řádek z databáze s daným obrázkem.
	 * @param string $galleryType Typ galerie (uživatelská nebo normální)
	 * @return string
	 */
	public function getImgSqrPath($image, $galleryType) {
		return $this->createImgPath($image, $galleryType, self::TYPE_MIN_SQR_IMG);
	}

	/**
	 * Vrací cestu k náhledu do galerie uživatelského obrázku.
	 * @param Nette\Database\Table\ActiveRow $image Řádek z databáze s daným obrázkem.
	 * @param string $galleryType Typ galerie (uživatelská nebo normální)
	 * @return string
	 */
	public function getImgScrnPath($image, $galleryType) {
		return $this->createImgPath($image, $galleryType, self::TYPE_SCRN_IMG);
	}

	/**
	 * Vytvoří cestu k obrázku.
	 * @param Nette\Database\Table\ActiveRow $image Řádek z databáze s daným obrázkem.
	 * @param string $galleryType Typ galerie (uživatelská nebo normální)
	 * @param int $type Typ vytvořené cesty (k normálnímu obr., k miniatuře ...)
	 * @return string
	 * @throws Exception Nezná daný typ obrázku.
	 */
	private function createImgPath($image, $galleryType, $type) {
		$basePath = $this->getBasePath();
		$image = new Image($image, $galleryType, $type);
		$galleryFolder = $this->getGalleryPath($image, $galleryType);

		switch ($type) {
			case self::TYPE_IMG:
				return ImagePathCreator::getImgPath($image->id, $image->suffix, $galleryFolder, $basePath);
			case self::TYPE_MIN_IMG:
				return ImagePathCreator::getImgMinPath($image->id, $image->suffix, $galleryFolder, $basePath);
			case self::TYPE_SCRN_IMG:
			case self::TYPE_STREAM:
				return ImagePathCreator::getImgScrnPath($image->id, $image->suffix, $galleryFolder, $basePath);
			case self::TYPE_MIN_SQR_IMG:
				return ImagePathCreator::getImgMinSqrPath($image->id, $image->suffix, $galleryFolder, $basePath);
			default:
				throw new Exception("Unknow image type " . $type);
		}
	}

	/**
	 * Vrátí základní cestu k obrázku ve formátu host://basepath/
	 * @return string
	 */
	private function getBasePath() {
		$basePath = $this->url->getBasePath();
		$host = $this->url->hostUrl;
		return $host . $basePath;
	}

	/**
	 *
	 * @param Nette\Database\Table\ActiveRow $image Řádek z databáze s daným obrázkem.
	 * @param type $galleryType
	 * @return type
	 */
	private function getGalleryPath($image, $galleryType) {
		if ($galleryType == self::TYPE_USER_GALLERY) {
			return GalleryPathCreator::getUserGalleryFolder($image->galleryID, $image->userID);
		} else {
			return GalleryPathCreator::getGalleryFolder($image->galleryID);
		}
	}

}
