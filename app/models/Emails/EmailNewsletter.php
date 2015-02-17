<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Notify;

/**
 * Email který se má odeslat uživatelům, kteří u nás již byli dříve.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class EmailNewsletter extends Email {

	public function __construct($user) {
		parent::__construct($user, true);
	}

	/**
	 * Vrátí emailovou adresu příjemce
	 * @return string Emailová adresa
	 */
	public function getEmailBody() {
		return "Soutěž Přiznání o sexu<br /><br />Pojď se znovu účastnit naší <strong style='color: #cf0707'>(po)Valentýnské soutěže</strong> na Datenode.cz (<strong style='color: #cf0707'>Přiznání o sexu</strong>). Ve hře jsou opravdu hodnotné <strong style='color: #cf0707'>ceny až do 1390,- Kč</strong> od našeho sponzora, sexshopu Růžový slon. <br />Jednoduchá pravidla najdeš <a href='http://datenode.cz/competition/upload-competition-image?galleryID=2' style='color: #cf0707'>ZDE</a>, fotku mohou vložit muži i ženy, soutěž platí do 28. února 2015.<br /><br />Váš tým Přiznání o sexu";
	}

	/**
	 * Vrátí zprávu co se má odeslat uživateli.
	 */
	public function getEmailSubject() {
		return "Přiznání o sexu: FOTOSOUTĚŽ o hodnotné ceny";
	}

	/**
	 * Vrátí emailovou adresu příjemce
	 * @return string Emailová adresa
	 */
	public function getEmailAddress() {
		return $this->user->user_email;
	}

}
