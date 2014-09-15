<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\BaseLikes;

use POS\Model\LikeCommentDao;

/**
 * Komponenta pro lajkování commentů, obstarává připočítávání lajků;
 * zaznamená, kdo lajkoval; a vykresluje obsluhu lajkování (tlačítko)
 *
 * @author Daniel Holubář
 */
class CommentLikes extends BaseLikes implements IBaseLikes {

	/**
	 * @var \POS\Model\LikeCommentDao
	 */
	public $likeCommentDao;
	public $comment;

	/**
	 * Kontruktor komponenty, předáváme potřebné proměnné
	 * @param \POS\Model\LikeStatusDao $likeCommentDao DAO pro práci s lajky commentů
	 * @param Nette\Database\Table\ActiveRow $comment comment, který se bude lajkovat
	 * @param int $userID ID uživatele, který lajkuje comment
	 */
	public function __construct(LikeCommentDao $likeCommentDao, $comment, $userID) {
		$this->likeCommentDao = $likeCommentDao;
		$this->liked = $this->getLikedByUser($userID, $comment->id);
		parent::__construct($likeCommentDao, NULL, NULL, $comment, $userID, $this->liked);
		$this->comment = $comment;
	}

	/**
	 * Signál pro provedení lajku, přičte lajk statusu a zaznamená, kdo lajkl
	 * @param int $userID ID uživatele, který lajkl comment
	 * @param int $commentID ID lajknutého commentu
	 */
	public function handleLike($userID, $commentID) {
		if ($this->liked == FALSE) {
			$this->likeCommentDao->addLiked($commentID, $userID);
		}
		$this->redrawControl();
	}

	/**
	 * Vrátí informaci, zda uživatel již dal like
	 * @param int $userID ID užovatele, kterého hledáme
	 * @param int $commentID ID commentu, který hledáme
	 * @return bool
	 */
	public function getLikedByUser($userID, $commentID) {
		$liked = $this->likeCommentDao->likedByUser($userID, $commentID);
		return $liked;
	}

}
