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

	public function __construct() {
		//$this->mailer = $mailer;
	}

	public function getSubscribedEvents() {
		return array('Nette\Application\Application::onStartup');
	}

	public function onStartup(Application $app) {
		$myfile = fopen("testfile2.txt", "w");
	}

//	public function render() {
//		$myfile = fopen("testfile2.txt", "w");
//	}
//
//	public function getSubscribedEvents() {
//		return array('POSComponent\Chat\PosChat::render');
//	}
}
