<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\BaseLikes;

use POS\Model\LikeConfessionDao;
use POS\UserPreferences\StreamUserPreferences;

/**
 * Komponenta pro lajkování přiznání, obstarává připočítávání lajků;
 * zaznamená, kdo lajkoval; a vykresluje obsluhu lajkování (tlačítko)
 *
 * @author Daniel Holubář
 */
class ConfessionLikes extends BaseLikes implements IBaseLikes {

	/**
	 * @const STATUS_LABEL text do informace o přihlášení kvůli hodnocení statusu
	 */
	const STATUS_LABEL = "přiznání";

	/**
	 * Kontruktor komponenty, předáváme potřebné proměnné
	 * @param \POS\Model\LikeStatusDao $likeConfessionDao DAO pro práci s lajky přiznání
	 * @param Nette\Database\Table\ActiveRow $confession přiznání, které se bude lajkovat
	 * @param int $userID ID uživatele, který lajkuje
	 * @param int $ownerID ID uživatele, kterýmu obrázek patří.
	 * @param \POS\UserPreferences\StreamUserPreferences $cachedStreamPreferences objekt obsahující položky ve streamu, pokud se používá cachování. Pokud se nepoužívá, pak je NULL
	 */
	public function __construct(LikeConfessionDao $likeConfessionDao, $confession, $userID, StreamUserPreferences $cachedStreamPreferences = NULL) {
		parent::__construct($likeConfessionDao, $confession, $userID, 0, self::STATUS_LABEL, self::DEFAULT_NAME_LIKE_BUTTON, $cachedStreamPreferences);
	}

	/**
	 * Signál pro provedení lajku, přičte lajk statusu a zaznamená, kdo lajkl
	 * @param int $userID ID uživatele, který lajkl status
	 * @param int $confessionID ID lajknutého přiznání
	 */
	public function handleLike($userID, $confessionID) {
		if ($this->liked == FALSE) {
			$this->justLike = TRUE;
			$this->liked = TRUE;
			$this->likeDao->addLiked($confessionID, $userID, $this->ownerID);
		}

		$this->redrawControl();
	}

}
