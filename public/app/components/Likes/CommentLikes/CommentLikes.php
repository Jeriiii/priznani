<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\BaseLikes;

use POS\Model\AbstractDao;
use POS\UserPreferences\StreamUserPreferences;

/**
 * Komponenta pro lajkování commentů, obstarává připočítávání lajků;
 * zaznamená, kdo lajkoval; a vykresluje obsluhu lajkování (tlačítko)
 *
 * @author Daniel Holubář
 */
class CommentLikes extends BaseLikes implements IBaseLikes {

	/** Název tlačítka */
	const COMMON_LIKE_BUTTON = "Líbí";

	/** Název funkce */
	const COMMENT_LABEL = "komentáře";

	/**
	 * Kontruktor komponenty, předáváme potřebné proměnné
	 * @param \POS\Model\LikeImageCommentDao $likeImageCommentDao DAO pro práci s lajky commentů
	 * @param Nette\Database\Table\ActiveRow $comment comment, který se bude lajkovat
	 * @param int $userID ID uživatele, který lajkuje comment
	 * @param int $ownerID ID uživatele, kterýmu obrázek patří.
	 * @param \POS\UserPreferences\StreamUserPreferences $cachedStreamPreferences objekt obsahující položky ve streamu, pokud se používá cachování. Pokud se nepoužívá, pak je NULL
	 */
	public function __construct(AbstractDao $likeImageCommentDao, $comment, $userID, $ownerID, $cachedStreamPreferences = NULL) {
		if ($cachedStreamPreferences instanceof StreamUserPreferences) {
			parent::__construct($likeImageCommentDao, $comment, $userID, $ownerID, self::COMMENT_LABEL, self::COMMON_LIKE_BUTTON, $cachedStreamPreferences);
		} else {
			parent::__construct($likeImageCommentDao, $comment, $userID, $ownerID, self::COMMENT_LABEL, self::COMMON_LIKE_BUTTON, NULL);
		}
	}

	/**
	 * Signál pro provedení lajku, přičte lajk statusu a zaznamená, kdo lajkl
	 * @param int $userID ID uživatele, který lajkl comment
	 * @param int $commentID ID lajknutého commentu
	 */
	public function handleLike($userID, $commentID) {
		if ($this->liked == FALSE) {
			$this->justLike = TRUE;
			$this->liked = TRUE;
			$this->likeDao->addLiked($commentID, $userID, $this->ownerID);
		}
		$this->redrawControl();
	}

}
