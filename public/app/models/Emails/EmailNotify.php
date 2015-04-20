<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Oznámení, které se mají odeslat emailem.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace Notify;

use Nette\Database\Table\ActiveRow;
use Nette\Mail\IMailer;
use Nette\Mail\Message;

class EmailNotify extends Email {

	/**
	 * @var int Počet zpráv pro uživatele.
	 */
	private $countMessages = 0;

	/**
	 * @var int Počet JSI SEXY
	 */
	private $countYouAreSexy = 0;

	/**
	 * @var int Další upozornění
	 */
	private $countOthersActivities;

	/** @var string Odkaz na týdenní změnu odesílání info emailu */
	private $setWeeklyLink;

	public function __construct($user, $setWeeklyLink) {
		parent::__construct($user);

		$this->setWeeklyLink = $setWeeklyLink;
	}

	/**
	 * Zvýší počet zpráv pro uživatele - aby uživatel věděl, mi napsalo 20 lidí
	 * @param string $message Zpráva příjemnce
	 */
	public function addMessage() {
		$this->countMessages ++;
	}

	/**
	 * Přidání aktivity do upozornění.
	 * @param ActiveRow $activity
	 */
	public function addActivity($activity) {
		if (!($activity instanceof ActiveRow)) {
			throw new Exception("Activity must be instance of active row");
		}

		if (!empty($activity->type) && $activity->type == "sexy") {
			$this->countYouAreSexy ++;
		} else {
			$this->countOthersActivities ++;
		}
	}

	/**
	 * Vrátí předmět emailu
	 */
	public function getEmailSubject() {
		$title = $this->getTittle();

		return "Máte " . $title;
	}

	/**
	 * Vrtátí zprávu co se má odeslat uživateli.
	 */
	public function getEmailBody() {
		$title = $this->getTittle();

		$body = "Ahoj, \n\nmáš " . $title . " na http://datenode.cz/. Neváhej a ozvi se.\nTvé Datenode";
		$body = $body . '\n\n Změna posílání informací na týdenní ' . $this->setWeeklyLink;
		return $body;
	}

	/*	 * **************************** generování titulku emailu ***************************************** */

	private function getTittle() {
		$messagesNotify = $this->getMessageTitle();
		$activitiesNotify = $this->getActivityTitle();
		$and = $this->getAndToTitle();

		return $messagesNotify . $and . $activitiesNotify;
	}

	private function getAndToTitle() {
		$and = "";

		if ($this->countMessages > 0 && ($this->countOthersActivities > 0 || $this->countYouAreSexy > 0)) {
			$and = " a ";
		}

		return $and;
	}

	private function getActivityTitle() {
		$activitiesNotify = "";

		if ($this->countOthersActivities > 0 || $this->countYouAreSexy > 0) {
			$activitiesNotify = $this->countOthersActivities + $this->countYouAreSexy . " nových upozornění";
		}

		return $activitiesNotify;
	}

	private function getMessageTitle() {
		$messagesNotify = "";

		if ($this->countMessages > 0) {
			$messagesNotify = $this->countMessages . " nových zpráv";
		}

		return $messagesNotify;
	}

}
