<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Popis komponenty
 *
 * @author Petr Kukrál
 */

namespace POSComponent\AddToList;

use POS\Model\FriendRequestDao;
use Nette\Application\UI\Form as Frm;
use POS\Model\ActivitiesDao;

class SendFriendRequest extends AddToList {

	/** @var \POS\Model\FriendRequestDao */
	public $friendRequestDao;

	/** @var int Má se zobrazit formulář */
	private $showForm = FALSE;

	/** @var \POS\Model\ActivitiesDao @inject */
	public $activitiesDao;

	public function __construct(ActivitiesDao $activitiesDao, FriendRequestDao $friendRequestDao, $userIDFrom, $userIDTo, $parent, $name) {
		parent::__construct($userIDFrom, $userIDTo, $parent, $name);
		$this->friendRequestDao = $friendRequestDao;
		$this->activitiesDao = $activitiesDao;
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render() {
		$this->template->showForm = $this->showForm;
		$this->template->setFile(dirname(__FILE__) . '/sendFriendRequest.latte');
		$this->template->isSendRequest = $this->friendRequestDao->isRequestSend($this->userIDFrom, $this->userIDTo);
		$this->template->render();
	}

	public function handleSendFriendRequest() {
		$this->showForm = TRUE;
		$this->redrawControl();
	}

	protected function createComponentRequestMessageForm($name) {
		return new Frm \ RequestMessageForm($this->activitiesDao, $this->friendRequestDao, $this->userIDFrom, $this->userIDTo, $this, $name);
	}

}
