<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace NetteExt;

use Nette\Image;

/**
 * Slouží pro přidávání watermarků k obrázkům
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class Watermarks extends \Nette\Object {

	/**
	 * Přidá k danému obrázku watermark na zadanou pozici
	 * @param Image $image obrázek, ke kterému se přidává
	 * @param String $watermarkURL url watermarku
	 * @param int $top odsazení watermarku shora
	 * @param int $left odsazení watermarku zleva
	 * @param int $opacity průhlednost v procentech
	 * @return Image upravený obrázek
	 */
	public static function addWatermark(Image $image, $watermarkURL, $top = 0, $left = 0, $opacity = 100) {
		$watermark = Image::fromFile($watermarkURL);
		return $image->place($watermark, $left, $top, $opacity); // vložíme na pozici 0px, 0px
	}

	/**
	 * Přidá k obrázku watermark, který se dle možností roztáhne přes celý obrázek
	 * @param Image $image obrázek
	 * @param string $watermarkURL url watermarku
	 * @param string $opacity průhlednost v procentech
	 * @return Image upravený obrázek
	 */
	public static function addFullWatermark(Image $image, $watermarkURL, $opacity = 100) {
		$watermark = Image::fromFile($watermarkURL);
		$watermark->resize(NULL, $image->getHeight());
		if ($watermark->getWidth() > $image->getWidth()) {
			$watermark->resize($image->getWidth(), NULL);
		}
		$positionX = ($image->getWidth() - $watermark->getWidth()) / 2;
		$positionY = ($image->getHeight() - $watermark->getHeight()) / 2;
		return $image->place($watermark, $positionX, $positionY, $opacity); // vložíme na pozici 0px, 0px
	}

	/**
	 * Přidá k danému obrázku watermark na pozici zadanou od pravého dolního rohu
	 * @param Image $image obrázek, ke kterému se přidává
	 * @param String $watermarkURL url watermarku
	 * @param int $bottom odsazení watermarku zespoda
	 * @param int $right odsazení watermarku zprava
	 * @param int $opacity průhlednost v procentech
	 * @return Image upravený obrázek
	 */
	public static function addBottomRightWatermark(Image $image, $watermarkURL, $bottom = 0, $right = 0, $opacity = 100) {
		$watermark = Image::fromFile($watermarkURL);
		$left = $image->getWidth() - $watermark->getWidth() - $right;
		$top = $image->getHeight() - $watermark->getHeight() - $bottom;
		return $image->place($watermark, $left, $top, $opacity); // vložíme na pozici 0px, 0px
	}

}
