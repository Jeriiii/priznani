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

	private $mailer;

	public function __construct(IMailer $mailer) {
		//$this->mailer = $mailer;
	}

	public function render() {
		//$this->mailer->send(new Message());
	}

	public function getSubscribedEvents() {
		return array();
	}

}
