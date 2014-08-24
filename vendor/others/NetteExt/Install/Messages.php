<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt\Install;

/**
 * Přepravka na zprávy.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
use Nette\Utils\Html;

class Messages {

	/** @var array Seznam zpráv. */
	private $messages = array();

	/**
	 * Přidá další zprávu.
	 * @param string $message Zpráva
	 */
	public function addMessage($message, $type = "info") {
		$this->messages[] = array(
			"message" => $message,
			"type" => $type
		);
	}

	public function flash($presenter) {
		foreach ($this->messages as $m) {
			$presenter->flashMessage($m["message"], $m["type"]);
		}
	}

}
