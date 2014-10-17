<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

/**
 * Description of FooListener
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class FooListener extends \Nette\Object implements \Kdyby\Events\Subscriber {

	private $dao;

	public function __construct(\POS\Model\ChatMessagesDao $dao) {
		$dao->setMessageReaded(54, FALSE);
		$this->dao = $dao;
		//$this->mailer = $mailer;
	}

	public function onPoo() {
		$this->dao->setMessageReaded(53, FALSE);
	}

	public function onStartup() {
		$this->dao->setMessageReaded(52, FALSE);
	}

	public function process() {
		$this->dao->setMessageReaded(50, FALSE);
	}

	public function getSubscribedEvents() {

		return array('Nette\Application\Application::onStartup',
			'\POS\Model\FriendDao::onMess' => 'process',
			'BasePresenter::onPoo');
	}

}
