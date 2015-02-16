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

	public function __construct($user) {
		parent::__construct($user, true);
	}

	/**
	 * Vrátí emailovou adresu příjemce
	 * @return string Emailová adresa
	 */
	public function getEmailBody() {
		return '<html><head><title>Přiznání o sexu = Nová erotická sociální síť!</title><meta charset="UTF-8"></head><body style="font-family: Helvetica Neue,Helvetica,Lucida Grande,tahoma,verdana,arial,sans-serif;"><img style="width: 30px;float: left;" src="http://datenode.cz/marketing/lips.jpg"><h1 style="color: #cf0707;font-size: 20px;padding: 6px 0 0 40px;">Přiznání o sexu <span style="color: gray;font-size: 16px;">- nově Datenode.cz!</span></h1><div><p>Ahoj,<br/><br/>rádi bychom Tě pozvali do naší nové české erotické sociální sítě <a href="http://datenode.cz/dating-registration/" style="color: #cf0707;">Datenode.CZ</a>.Jedná se o jedinečný  seznamovací kanál, kde máte možnost najít <span style="color: #cf0707;">ženu</span>, <span style="color: #cf0707;">muže</span> i <span style="color: #cf0707;">PÁR!</span>, ať už na povídání, výměnu fotek či na osobní setkání. Dále zde najdete všechny <span style="color: #cf0707;">Vaše přiznání</span> a <span style="color: #cf0707;">fotky z erotických soutěží</span>.<br/><br/>Pro nově registrované jsme připravili soutěž o 5.000,- Kč, proto s <a href="http://datenode.cz/dating-registration/" style="color: #cf0707;">novou registrací</a> dlouho neváhejte :-)<br/><br/>Pokud budete hledat naše facebookové stránky Přiznání o sexu, najdete nás pod novou, komornější <a href="https://www.facebook.com/groups/1505466003062259/1508722929403233/?notif_t=group_comment" style="color: #cf0707;">fb skupinou</a>.<br/><br/>Váš tým Přiznání o sexu - nově Datenode.cz</p></div><div><h2 style="color: #cf0707;font-size: 16px;">Čekají na Tebe:</h2><div><img style="height: 60px;" src="http://datenode.cz/marketing/oldUsers/m1.jpg" /><img style="height: 60px;" src="http://datenode.cz/marketing/oldUsers/m2.jpg" /><img style="height: 60px;" src="http://datenode.cz/marketing/oldUsers/z1.jpg" /><img style="height: 60px;" src="http://datenode.cz/marketing/oldUsers/z2.jpg" /> ...</div><div><a href="http://datenode.cz/dating-registration/" style="background: #cf0707;padding: 7px 12px; color: white;margin: 5px 10px; display: inline-block;text-decoration: none;">Registrace</a></div></div></body></html>';
	}

	/**
	 * Vrátí zprávu co se má odeslat uživateli.
	 */
	public function getEmailSubject() {
		return "Seznam se na síti přiznání o sexu!";
	}

}
