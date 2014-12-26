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
class EmailForOldUsers extends Email {

	/**
	 * Vrátí emailovou adresu příjemce
	 * @return string Emailová adresa
	 */
	public function getEmailBody() {
		return "Ahoj,\n"
			. "rádi bychom Tě pozvali do naší nové erotické sociální sítě Datenode.CZ (http://datenode.cz/dating-registration/)."
			. "Jedná se o unikátní, v Čechách jedinečný seznamovací kanál, kde máte možnost najít ženu, muže i pár, ať už na povídání, výměnu fotek či na "
			. "osobní setkání. Dále zde najdete všechny Vaše přiznání, fotky ze soutěží, erotické hry pro dva i více za symbolickou cenu 69,- Kč. "
			. "Pro nově registrované jsme připravili soutěž o 5.000,- Kč, proto s novou registrací dlouho neváhejte\n\n"
			. "Pokud budete hledat naše facebookové stránky Přiznání o sexu, najdete nás pod novou, komornější skupinou https://www.facebook.com/groups/. " . "1505466003062259/1508722929403233/?notif_t=group_comment.\n\n"
			. "Váš tým Přiznání o sexu - Datenode.cz";
	}

	/**
	 * Vrátí zprávu co se má odeslat uživateli.
	 */
	public function getEmailSubject() {
		return "Seznam se na síti přiznání o sexu!";
	}

}
