<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\BaseLikes;

use POS\Model\ImageLikesDao;
use POS\Model\UserImageDao;

/**
 * Komponenta pro lajkování obrázků, obstarává připočítávání lajků;
 * zaznamená, kdo lajkoval; a vykresluje obsluhu lajkování (tlačítko)
 *
 * @author Daniel Holubář
 */
class ImageLikes extends BaseLikes implements IBaseLikes {

	/**
	 * @var \POS\Model\ImageLikesDao
	 */
	public $imageLikesDao;

	/**
	 * @var \POS\Model\UserImageDao
	 */
	public $userImageDao;

	/**
	 * @var Nette\Database\Table\ActiveRow Obrázek pro lajknutí
	 */
	public $image;

	/**
	 * Konstruktor komponenty, předáváme potřebné proměnné
	 * @param \POS\Model\ImageLikesDao $imageLikesDao DAO pro záznam, kdo lajkl jaký obrázek
	 * @param \POS\Model\UserImageDao $userImageDao DAO pro práci s uživatelskými obrázky(kvůli připočtení lajku)
	 * @param Nette\Database\Table\ActiveRow $image obrázek, kterému se lajk přičte
	 * @param int $userID ID uživatele, který lajkuje
	 */
	public function __construct(ImageLikesDao $imageLikesDao, UserImageDao $userImageDao, $image, $userID) {
		$this->imageLikesDao = $imageLikesDao;
		$this->liked = $this->getLikedByUser($userID, $image->id);
		parent::__construct($imageLikesDao, $image, NULL, $userID, $this->liked);
		$this->image = $image;
		$this->userImageDao = $userImageDao;
	}

	/**
	 * Signál pro provedení lajku, přičte lajk obrázku a zaznamená, kdo lajkl
	 * @param int $userID ID uživatele, který lajkl obrázek
	 * @param int $imageID ID lajknutého obrázku
	 */
	public function handleLike($userID, $imageID) {
		if ($this->liked == FALSE) {
			$this->imageLikesDao->addLiked($imageID, $userID);
		}

		$this->redrawControl();
	}

	/**
	 * Vrátí informaci, zda uživatel již dal like (= sexy)
	 * @param int $userID ID užovatele, kterého hledáme
	 * @param int $imageID ID obrázku, který hledáme
	 * @return bool
	 */
	public function getLikedByUser($userID, $imageID) {
		$liked = $this->imageLikesDao->likedByUser($userID, $imageID);
		return $liked;
	}

}
