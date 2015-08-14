<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 22.5.2015
 */

namespace POSComponent\Stream;

use POS\Model\ILikeDao;

/**
 * Příspěvek ve streamu.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
abstract class Item implements IItemStream {

	/** @var \Nette\Database\Table\ActiveRow|\Nette\ArrayHash Description Uživatel - vlastník */
	protected $user;

	/** @var int Počet liků. */
	protected $countLikes;

	/** @var int Počet komentářů. */
	protected $countComments;

	/**
	 * Nastaví uživatele z dat ve streamu.
	 * @param \Nette\ArrayHash|\Nette\Database\Table\ActiveRow $item Příspěvek ze streamu.
	 */
	protected function parseUser($item) {
		if (isset($item->user)) {
			$this->user = $item->user;
		}
	}

	/**
	 * Uloží počet liků.
	 * @param \Nette\ArrayHash|\Nette\Database\Table\ActiveRow $specItem Již specifický objekt, například galerie či přiznání.
	 */
	protected function parseLikes($specItem) {
		$this->countLikes = $specItem->likes;
	}

	/**
	 * Uloží počet komentářů.
	 * @param \Nette\ArrayHash|\Nette\Database\Table\ActiveRow $specItem Již specifický objekt, například galerie či přiznání.
	 */
	protected function parseComments($specItem) {
		$this->countComments = $specItem->comments;
	}

}
