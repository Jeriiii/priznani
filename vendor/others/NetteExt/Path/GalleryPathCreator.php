<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt\Path;

/**
 * Vytváří cesty k souborům galerií
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class GalleryPathCreator extends PathCreator {
	/* složky galerií */

	const FOLDER_GALLERY = "galleries";
	const FOLDER_USER_GALLERY = "userGalleries";

	/**
	 * Vytvoří celou cestu ke složce uživatelské galerie
	 * @param int $galleryID ID galerie.
	 * @param int $userID ID uživatele.
	 * @param string $basePath Základní cesta do WWW (dá se naplnit třeba $basePath z šablony)
	 * @return string Celá cesta ke složce uživatelské galerie.
	 */
	public static function getUserGalleryPath($galleryID, $userID, $basePath = parent::BASE_PATH) {
		return self::getBasePath($basePath) . self::getUserGalleryFolder($galleryID, $userID);
	}

	/**
	 * Vytvoří relativní cestu ke složce uživatelské galerie.
	 * @param int $galleryID ID galerie.
	 * @param int $userID ID uživatele.
	 * @return string Relativní cesta ke složce.
	 */
	public static function getUserGalleryFolder($galleryID, $userID) {
		return self::FOLDER_USER_GALLERY . "/" . $userID . "/" . $galleryID;
	}

	/**
	 * Vytvoří celou cestu ke složce galerie
	 * @param int $galleryID ID galerie.
	 * @param string $basePath Základní cesta do WWW (dá se naplnit třeba $basePath z šablony)
	 * @return string Celá cesta ke složce uživatelské galerie.
	 */
	public static function getGalleryPath($galleryID, $basePath = parent::BASE_PATH) {
		return self::getBasePath($basePath) . self::getGalleryFolder($galleryID);
	}

	/**
	 * Vrátí složku kde jsou uloženy VŠECHNY galerie daného uživatele
	 * @param int $userID ID uživatele.
	 * @param string $basePath Základní cesta do WWW (dá se naplnit třeba $basePath z šablony)
	 * @return string Celá cesta ke složce se všemi uživatelskými galeriemi
	 */
	public static function getBasePathForAllUserGalleries($userID, $basePath = parent::BASE_PATH) {
		return self::getBasePath($basePath) . self::FOLDER_USER_GALLERY . "/" . $userID;
	}

	/**
	 * Vytvoří relativní cestu ke složce galerie.
	 * @param int $galleryID ID galerie.
	 * @return string Relativní cesta ke složce.
	 */
	public static function getGalleryFolder($galleryID) {
		return self::FOLDER_GALLERY . "/" . $galleryID;
	}

	/**
	 * Vrací celou základní cestu do obecné složky s obrázky
	 * @param string $basePath Základní cesta do WWW (dá se naplnit třeba $basePath z šablony)
	 * @return string obecná složka z obrázky
	 */
	private static function getBasePath($basePath = parent::BASE_PATH) {
		return $basePath . "/images/";
	}

}
