<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt\Path;

/**
 * Vytváří cesty k obrázkům
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class ImagePathCreator extends PathCreator {
	/* prefixy obrázků */

	const PREFFIX_GALLERY_SCREEN = "galScrn";
	const PREFFIX_MIN_SQUERE = "minSqr";
	const PREFFIX_MIN = "min";

	/*	 * ******************* METODY CO VRÁTÍ CESTU K ******************* */

	/**
	 * Vrátí celou cestu k ORIGINÁLNÍMU obrázku
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/3/50
	 * @param string $basePath Základní cesta do WWW (dá se naplnit třeba $basePath z šablony)
	 * @return string Celá cesta k ORIGINÁLNÍMU obrázku.
	 */
	public static function getImgPath($imageID, $imageSuffix, $folder, $basePath = parent::BASE_PATH) {
		return self::getBaseImgPath($imageID, $imageSuffix, "", $folder, $basePath);
	}

	/**
	 * Vrátí celou cestu k obrázku SCREEN
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/2/50
	 * @param string $basePath Základní cesta do WWW (dá se naplnit třeba $basePath z šablony)
	 * @return string Celá cesta k obrázku SCREEN.
	 */
	public static function getImgScrnPath($imageID, $imageSuffix, $folder, $basePath = parent::BASE_PATH) {
		return self::getBaseImgPath($imageID, $imageSuffix, self::PREFFIX_GALLERY_SCREEN, $folder, $basePath);
	}

	/**
	 * Vrátí celou cestu k obrázku miniatury
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/5/50
	 * @param string $basePath Základní cesta do WWW (dá se naplnit třeba $basePath z šablony)
	 * @return string Celá cesta k obrázku miniatury.
	 */
	public static function getImgMinPath($imageID, $imageSuffix, $folder, $basePath = parent::BASE_PATH) {
		return self::getBaseImgPath($imageID, $imageSuffix, self::PREFFIX_MIN, $folder, $basePath);
	}

	/**
	 * Vrátí celou cestu k obrázku miniatury ořízlou do čtverce
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/3/50
	 * @param string $basePath Základní cesta do WWW (dá se naplnit třeba $basePath z šablony)
	 * @return string Celá cesta k obrázku miniatury ořízlou do čtverce.
	 */
	public static function getImgMinSqrPath($imageID, $imageSuffix, $folder, $basePath = parent::BASE_PATH) {
		return self::getBaseImgPath($imageID, $imageSuffix, self::PREFFIX_MIN_SQUERE, $folder, $basePath);
	}

	/**
	 * Vrátí celou cestu k obrázku, slouží jen pro vnitřní použití. Pro
	 * cestu ke konkrétnímu oprázku použijte jednu z public metod.
	 * @param int $imageID ID obrázku.
	 * @param string $imageSuffix Suffix obrázku.
	 * @param type $preffix Preffix obrázku např. min|galScrn|... .
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/3/50
	 * @param string $basePath Základní cesta do WWW (dá se naplnit třeba $basePath z šablony)
	 * @return string Celá cesta k obrázku.
	 */
	private static function getBaseImgPath($imageID, $imageSuffix, $preffix, $folder, $basePath = parent::BASE_PATH) {
		return self::getBasePath($folder, $basePath) . $preffix . $imageID . '.' . $imageSuffix;
	}

	/**
	 * Vrátí cestu ke složce, kde jsou uloženy všechny obrázky
	 * @param string $folder Složka dané galerie, např. galleries/12|userGalleries/3/50
	 * @param string $basePath Základní cesta do WWW (dá se naplnit třeba $basePath z šablony)
	 * @return string Cesta do galerie.
	 */
	public static function getBasePath($folder, $basePath = parent::BASE_PATH) {
		return $basePath . "/images/" . $folder . "/";
	}

}
