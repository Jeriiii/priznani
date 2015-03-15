<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POS\Ajax;

/**
 * Příklad Handlu pro AjaxObserver
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ChatConversationsHandle extends \Nette\Object implements IObserverHandle {

	/**
	 *
	 * @var \POS\Chat\ChatManager
	 */
	private $manager;
	private $userId;

	public function __construct(\POS\Chat\ChatManager $manager, $userId) {
		$this->manager = $manager;
		$this->userId = $userId;
	}

	/**
	 * Implementace rozhrani IObserverHandle
	 */
	public function getData() {
		return $this->manager->getAllUnreadedMessages($this->userId)
				->count();
	}

}
