<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\BaseLikes;

use POS\Model\ImageLikesDao;
use POS\Model\UserImageDao;
use POS\UserPreferences\StreamUserPreferences;

/**
 * Komponenta pro lajkování obrázků, obstarává připočítávání lajků;
 * zaznamená, kdo lajkoval; a vykresluje obsluhu lajkování (tlačítko)
 *
 * @author Daniel Holubář
 */
class ImageLikes extends BaseLikes implements IBaseLikes {

	/**
	 * @const IMAGE_LABEL text do informace o přihlášení kvůli hodnocení obrázku
	 */
	const IMAGE_LABEL = "obrázku";

	/**
	 * @const IMAGE_LIKE_BUTTON text lajkovacího tlačítka pro obrázky
	 */
	const IMAGE_LIKE_BUTTON = "Sexy";

	/**
	 * Konstruktor komponenty, předáváme potřebné proměnné
	 * @param \POS\Model\ImageLikesDao $imageLikesDao DAO pro záznam, kdo lajkl jaký obrázek
	 * @param \POS\Model\UserImageDao $userImageDao DAO pro práci s uživatelskými obrázky(kvůli připočtení lajku)
	 * @param Nette\Database\Table\ActiveRow $image obrázek, kterému se lajk přičte
	 * @param int $userID ID uživatele, který lajkuje
	 * @param int $ownerID ID uživatele, kterýmu obrázek patří.
	 * @param \POS\UserPreferences\StreamUserPreferences $cachedStreamPreferences objekt obsahující položky ve streamu, pokud se používá cachování. Pokud se nepoužívá, pak je NULL
	 */
	public function __construct(ImageLikesDao $imageLikesDao, $image, $userID, $ownerID, StreamUserPreferences $cachedStreamPreferences = NULL) {
		parent::__construct($imageLikesDao, $image, $userID, $ownerID, self::IMAGE_LABEL, self::IMAGE_LIKE_BUTTON, $cachedStreamPreferences);
	}

	/**
	 * Signál pro provedení lajku, přičte lajk obrázku a zaznamená, kdo lajkl
	 * @param int $userID ID uživatele, který lajkl obrázek
	 * @param int $imageID ID lajknutého obrázku
	 */
	public function handleLike($userID, $imageID) {
		/* musí se přepočítat, protože se sice v handleru pošle správné ID obrázku, ale špatné id presenteru */
		$this->liked = $this->getLikedByUser($userID, $imageID);

		if ($this->liked == FALSE) {
			$this->justLike = TRUE;
			$this->liked = TRUE;
			$this->likeDao->addLiked($imageID, $userID, $this->ownerID);
		}
		$this->redrawControl();
	}

}
