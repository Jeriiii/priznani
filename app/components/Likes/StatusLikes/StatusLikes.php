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
	 * @var \POS\Model\LikeStatusDao
	 */
	public $likeStatusDao;

	/**
	 * @var Nette\Database\Table\ActiveRow Status pro lajknutí
	 */
	public $status;

	/**
	 * Kontruktor komponenty, předáváme potřebné proměnné
	 * @param \POS\Model\LikeStatusDao $likeStatusDao DAO pro práci s lajky statusů
	 * @param Nette\Database\Table\ActiveRow $status status, který se bude lajkovat
	 * @param int $userID ID uživatele, který lajkuje status
	 */
	public function __construct(LikeStatusDao $likeStatusDao, $status, $userID) {
		$this->likeStatusDao = $likeStatusDao;
		$this->liked = $this->getLikedByUser($userID, $status->id);
		parent::__construct($likeStatusDao, NULL, $status, $userID, $this->liked);
		$this->status = $status;
	}

	/**
	 * Signál pro provedení lajku, přičte lajk statusu a zaznamená, kdo lajkl
	 * @param int $userID ID uživatele, který lajkl status
	 * @param int $statusID ID lajknutého statusu
	 */
	public function handleLike($userID, $statusID) {
		if ($this->liked == FALSE) {
			$this->likeStatusDao->addLiked($statusID, $userID);
		}

		$this->redrawControl();
	}

	/**
	 * Vrátí informaci, zda uživatel již dal like
	 * @param int $userID ID užovatele, kterého hledáme
	 * @param int $statusID ID statusu, který hledáme
	 * @return bool
	 */
	public function getLikedByUser($userID, $statusID) {
		$liked = $this->likeStatusDao->likedByUser($userID, $statusID);
		return $liked;
	}

}
