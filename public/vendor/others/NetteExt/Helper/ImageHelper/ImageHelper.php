<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Helper pro zobrazení uživatele se jménem a miniaturou obrázku
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace NetteExt\Helper;

use \Nette\Database\Table\ActiveRow;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Nette\DateTime;
use POS\Model\UserDao;

class ImageHelper {
	/* názvy helperů */

	const NAME = "img";
	const NAME_MIN = "imgMin";
	const NAME_MIN_SQR = "imgSqr";
	const NAME_SCRN = "imgScrn";
	const NAME_STREAM = "imgStrm";

	/** @var GetImgPathHelper */
	private $getImgPathHelper;

	public function __construct(GetImgPathHelper $getImgPathHelper) {
		$this->getImgPathHelper = $getImgPathHelper;
	}

	/**
	 * Vrací HTML obrázku.
	 * @param Nette\Database\Table\ActiveRow $image Řádek z databáze s daným obrázkem.
	 * @param string $galleryType Typ galerie (uživatelská nebo normální)
	 * @param string $class Html třídy, které se vážou k obrázku.
	 * @param string $style Přímé styly.
	 * @return Nette\Utils\Html
	 */
	public function img($image, $galleryType, $class, $style) {
		return $this->createImg($image, $galleryType, GetImgPathHelper::TYPE_IMG, $class, $style);
	}

	/**
	 * Vrací HTML obrázku ve streamu.
	 * @param Nette\Database\Table\ActiveRow $image Řádek z databáze ze streamu.
	 * @param string $galleryType Typ galerie (uživatelská nebo normální)
	 * @param string $class Html třídy, které se vážou k obrázku.
	 * @param string $style Přímé styly.
	 * @return Nette\Utils\Html
	 */
	public function imgStream($image, $galleryType, $class, $style) {
		return $this->createImg($image, $galleryType, GetImgPathHelper::TYPE_STREAM, $class, $style);
	}

	/**
	 * Vrací HTML obrázku miniatury.
	 * @param Nette\Database\Table\ActiveRow $image Řádek z databáze s daným obrázkem.
	 * @param string $galleryType Typ galerie (uživatelská nebo normální)
	 * @param string $class Html třídy, které se vážou k obrázku.
	 * @param string $style Přímé styly.
	 * @return Nette\Utils\Html
	 */
	public function imgMin($image, $galleryType, $class, $style) {
		return $this->createImg($image, $galleryType, GetImgPathHelper::TYPE_MIN_IMG, $class, $style);
	}

	/**
	 * Vrací HTML obrázku miniatury ořízlé do čtverce.
	 * @param Nette\Database\Table\ActiveRow $image Řádek z databáze s daným obrázkem.
	 * @param string $galleryType Typ galerie (uživatelská nebo normální)
	 * @param string $class Html třídy, které se vážou k obrázku.
	 * @param string $style Přímé styly.
	 * @return Nette\Utils\Html
	 */
	public function imgSqr($image, $galleryType, $class, $style) {
		return $this->createImg($image, $galleryType, GetImgPathHelper::TYPE_MIN_SQR_IMG, $class, $style);
	}

	/**
	 * Vrací HTML náhledu obrázku.
	 * @param Nette\Database\Table\ActiveRow $image Řádek z databáze s daným obrázkem.
	 * @param string $galleryType Typ galerie (uživatelská nebo normální)
	 * @param string $class Html třídy, které se vážou k obrázku.
	 * @param string $style Přímé styly.
	 * @return Nette\Utils\Html
	 */
	public function imgScrn($image, $galleryType, $class, $style) {
		return $this->createImg($image, $galleryType, GetImgPathHelper::TYPE_SCRN_IMG, $class, $style);
	}

	/**
	 * Vytvoří cestu k obrázku.
	 * @param Nette\Database\Table\ActiveRow $image Řádek z databáze s daným obrázkem.
	 * @param string $galleryType Typ galerie (uživatelská nebo normální)
	 * @param int $type Typ vytvořené cesty (k normálnímu obr., k miniatuře ...)
	 * @param string $class Html třídy, které se vážou k obrázku.
	 * @param string $style Přímé styly.
	 * @return Nette\Utils\Html
	 */
	private function createImg($image, $galleryType, $type, $class, $style) {
		$img = Html::el('img');
		$img->src = $this->getImgPathHelper->createImgPath($image, $galleryType, $type);
		$img->alt = $image->name;
		$img['class'] = $class;
		$img->style = $style;

		return $img;
	}

}
