<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\BaseLikes;

use POS\Model\LikeStatusDao;

/**
 * Komponenta pro lajkování statusů, obstarává připočítávání lajků;
 * zaznamená, kdo lajkoval; a vykresluje obsluhu lajkování (tlačítko)
 *
 * @author Daniel Holubář
 */
class StatusLikes extends BaseLikes implements IBaseLikes {

	/**
	 * @const STATUS_LABEL text do informace o přihlášení kvůli hodnocení statusu
	 */
	const STATUS_LABEL = "statusu";

	/**
	 * Kontruktor komponenty, předáváme potřebné proměnné
	 * @param \POS\Model\LikeStatusDao $likeStatusDao DAO pro práci s lajky statusů
	 * @param Nette\Database\Table\ActiveRow $status status, který se bude lajkovat
	 * @param int $userID ID uživatele, který lajkuje status
	 */
	public function __construct(LikeStatusDao $likeStatusDao, $status, $userID) {
		parent::__construct($likeStatusDao, $status, $userID, self::STATUS_LABEL);
	}

	/**
	 * Signál pro provedení lajku, přičte lajk statusu a zaznamená, kdo lajkl
	 * @param int $userID ID uživatele, který lajkl status
	 * @param int $statusID ID lajknutého statusu
	 */
	public function handleLike($userID, $statusID) {
		if ($this->liked == FALSE) {
			$this->justLike = TRUE;
			$this->liked = TRUE;
			$this->likeDao->addLiked($statusID, $userID, $this->ownerID);
		}

		$this->redrawControl();
	}

}
