<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 13.6.2015
 */

namespace NetteExt\Uploader;

/**
 * Třída zapouzdřující obrázky k nahrání
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class ImagesToUpload {

	/** @var int ID uživatele. */
	private $userID;

	/** @var int ID Galerie. */
	private $galleryID;

	function __construct($userID, $galleryID) {
		$this->userID = $userID;
		$this->galleryID = $galleryID;
	}

	/** @var array Pole obrázků ImageToUpload */
	private $images = array();

	/**
	 * Přidá obrázek k uložení.
	 * @param \NetteExt\Uploader\ImageToUpload $image Obrázek, co se má uložit.
	 */
	public function addImage(ImageToUpload $image) {
		$this->images[] = $image;
	}

	/**
	 * Vyčistí si obrázky k uložení, je možné přidat jiné obrázky.
	 * @param int $newUserID Nové id uživatele, pro koho budeme nahrávat fotky.
	 * @param int $newGalleryID Nová galerie, do které chceme nahrávat fotky.
	 */
	public function clearImages($newUserID, $newGalleryID) {
		$this->images = array();
		$this->userID = $newUserID;
		$this->galleryID = $newGalleryID;
	}

	/**
	 * Vrátí obrázky k uložení.
	 * @return array
	 */
	public function getImages() {
		return $this->images;
	}

	function getUserID() {
		return $this->userID;
	}

	function getGalleryID() {
		return $this->galleryID;
	}

}
