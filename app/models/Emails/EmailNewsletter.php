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

	/**
	 * Vrátí emailovou adresu příjemce
	 * @return string Emailová adresa
	 */
	public function getEmailBody() {
		return 'Pojď se znovu účastnit naší Valentýnské soutěže na Datenode.cz (Přiznání o sexu). Ve hře jsou opravdu hodnotné ceny až do 1390,- Kč od našeho sponzora, sexshopu Růžový slon. \nJednoduchá pravidla najdeš na http://datenode.cz/competition/upload-competition-image?galleryID=2, fotku mohou vložit muži i ženy, soutěž platí do 28. února 2015.';
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
