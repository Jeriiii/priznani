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
class GalleryPathCreator {
	/* složky galerií */

	const FOLDER_GALLERY = "galleries";
	const FOLDER_USER_GALLERY = "userGalleries";

	/**
	 * Vytvoří celou cestu ke složce uživatelské galerie
	 * @param int $galleryID ID galerie.
	 * @param int $userID ID uživatele.
	 * @return string Celá cesta ke složce uživatelské galerie.
	 */
	public static function getUserGalleryPath($galleryID, $userID) {
		return self::getBasePath() . self::getUserGalleryFolder($galleryID, $userID);
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
	 * @return string Celá cesta ke složce uživatelské galerie.
	 */
	public static function getGalleryPath($galleryID) {
		return self::getBasePath() . self::getGalleryFolder($galleryID);
	}

	/**
	 * Vrátí složku kde jsou uloženy VŠECHNY galerie daného uživatele
	 * @param int $userID ID uživatele.
	 * @return string Celá cesta ke složce se všemi uživatelskými galeriemi
	 */
	public static function getBasePathForAllUserGalleries($userID) {
		return self::getBasePath() . "/" . $userID;
	}

	/**
	 * Vytvoří relativní cestu ke složce galerie.
	 * @param int $galleryID ID galerie.
	 * @return string Relativní cesta ke složce.
	 */
	public static function getGalleryFolder($galleryID) {
		return self::FOLDER_USER_GALLERY . "/" . $galleryID;
	}

	private static function getBasePath() {
		return WWW_DIR . "/images/";
	}

}
