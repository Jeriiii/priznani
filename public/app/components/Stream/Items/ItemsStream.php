<?php

namespace POSComponent\Stream;

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 22.5.2015
 */

/**
 * Uchovává příspěvky ze streamu, donačítá data.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class ItemsStream {

	/** @var array Pole příspěvků. */
	private $itemsStream;

	public function __construct($items) {
		parse($items);
	}

	/**
	 * Naparsuje data ze streamu a vytvoří z nich objekty - přízpěvky.
	 * @param \Nette\ArrayHash|\Nette\Database\Table\Selection $items Pole příspěvků.
	 */
	private function parse($items) {
		foreach ($items as $item) {
			if (isset($item->confession)) {
				$itemStream = new Confession($item);
			} else if (isset($item->userGallery)) {
				$itemStream = new UserGallery($item);
			} else if (isset($item->status)) {
				$itemStream = new Status($item);
			}
			$this->itemsStream[] = $itemStream;
		}
	}

}
