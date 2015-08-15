<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 14.8.2015
 */

namespace NetteExt\Path;

/**
 * Vytváří cestu k obrázkům v blogu
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class BlogImagePathCreator extends PathCreator {

	const BLOG_FOLDER_NAME = 'blog';

	/**
	 * Vrátí celou cestu k obrázku ve článku.
	 * @param int $articleId Id článku.
	 * @param int $imageId Id obrázku.
	 * @param string $imageSuffix Přípona obrázku.
	 * @return string Cesta k obrázku.
	 */
	public static function getImgPath($articleId, $imageId, $imageSuffix, $basePath = null) {
		return self::getArticleFolderPath($articleId, $basePath) . '/' . $imageId . '.' . $imageSuffix;
	}

	/**
	 * Vrátí cestu ke složce kde jsou obrázky daného článku.
	 * @param int $articleId Id článku.
	 * @return string Cesta ke složce s obrázky článku.
	 */
	public static function getArticleFolderPath($articleId, $basePath = null) {
		if ($basePath == null) {
			$basePath = self::BASE_PATH;
		}

		return $basePath . "/images/" . self::BLOG_FOLDER_NAME . '/' . $articleId;
	}

}
