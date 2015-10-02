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

	const IMG_BASE_PATH = '\marketing\oldUsers';

	/** id uživatele v tabulce users_old */
	private $userId = -1;

	/**
	 * Emailová adresa odesílatele (stránky)
	 */
	const EMAIL_ADDRESS_SENDER = "Jana.Z.Datenode.cz@priznaniosexu.cz";

	public function __construct($user) {
		parent::__construct($user, true, WWW_DIR . self::IMG_BASE_PATH);
		$this->userId = $user->id;
	}

	/**
	 * Vrátí emailovou adresu příjemce
	 * @return string Emailová adresa
	 */
	public function getEmailBody() {
		return '<div style="margin:0;padding:0;min-width:100%;background-color:#f6f6f8"> <center style="display:table;table-layout:fixed;width:100%;background-color:#f6f6f8"> <div style="font-size:1px;line-height:20px;width:100%">&nbsp;</div><table style="border-collapse:collapse;border-spacing:0;Margin-left:auto;Margin-right:auto;width:100%;color:gray"> <tbody><tr> <td style="padding:0;vertical-align:middle">&nbsp;</td><td style="padding:0;vertical-align:middle;width:600px;padding-top:12px;padding-bottom:26px"> <div style="font-family:Avenir,sans-serif;color:#38434d;font-weight:bold;Margin-bottom:0;font-size:0px!important;line-height:0!important" align="left"><img style="border:0;display:block;max-width:380px" src="https://ci6.googleusercontent.com/proxy/_c5I3-MIfIliCN1AGbdFuvGydjg1l9oT505WkD4VwJ2JbSbrGycK7Rwwpa452CqGV9YEtAe4RNY9YEbmHptplgYftI_lDRXHvGoy8810oRy62rv2=s0-d-e1-ft#http://i1.cmail2.com/ei/i/89/28D/B30/223441/csfinal/Vstiek.PNG" alt="" width="300" height="36" class="CToWUd"></div></td><td style="padding:0;vertical-align:middle">&nbsp;</td></tr></tbody></table> <table style="border-collapse:collapse;border-spacing:0;width:100%"> <tbody><tr> <td style="padding:0;vertical-align:middle" align="center"> <table style="border-collapse:collapse;border-spacing:0;Margin-left:auto;Margin-right:auto;table-layout:fixed"> <tbody><tr> <td style="padding:0;vertical-align:middle;text-align:left"> <table style="border-collapse:collapse;border-spacing:0;table-layout:fixed;width:100%;background-color:#fff"> <tbody><tr> <td style="padding:0;vertical-align:middle"> <div><div style="font-size:40px;line-height:40px">&nbsp;</div></div><table style="border-collapse:collapse;border-spacing:0" width="100%"> <tbody><tr> <td style="padding:0;vertical-align:middle;padding-left:56px;padding-right:56px;word-break:break-word;word-wrap:break-word"> <h3 style="Margin-top:0;font-style:normal;font-weight:400;font-size:16px;line-height:24px;Margin-bottom:14px;font-family:&quot;PT Serif&quot;,Georgia,serif;color:#788991;text-align:center">&nbsp;</h3><h1 style="Margin-top:0;font-style:normal;font-weight:400;font-size:22px;line-height:30px;Margin-bottom:18px;font-family:Ubuntu,sans-serif;color:#3e4751;text-align:center">Právě Ti přišla (1)&nbsp;<strong style="font-weight:bold">nová zpráva</strong>&nbsp;od<strong style="font-weight:bold"> Tomáše</strong>!</h1><p style="Margin-top:0;font-style:normal;font-weight:400;max-width: 500px;margin: 0 auto; font-size:14px;line-height:21px;Margin-bottom:22px;font-family:&quot;PT Serif&quot;,Georgia,serif;color:#7c7e7f;text-align:center">Na Datenode.cz (dříve <a href="http://priznaniosexu.cz" target="_blank">priznaniosexu.cz</a>) Ti přišla nová zpráva. Pro přečtení se musíš přihlásit, zpráva bude dostupná jen 24 hodin.&nbsp;<span style="color:rgb(124,126,127)">Tak ať ti to neuteče!</span></p></td></tr></tbody></table> <table style="border-collapse:collapse;border-spacing:0" width="100%"> <tbody><tr> <td style="padding:0;vertical-align:middle;padding-left:56px;padding-right:56px;word-break:break-word;word-wrap:break-word"> <div style="Margin-bottom:22px;text-align:center"> <u></u><a style="border-radius:3px;display:inline-block;font-size:14px;font-weight:700;line-height:24px;padding:13px 35px 12px 35px;text-align:center;text-decoration:none!important;font-family:&quot;PT Serif&quot;,Georgia,serif;background-color:#4ecc4e;color:#fff" href="http://datenode.cz/registrace" target="_blank">Přečíst zprávu nyní</a><u></u> </div></td></tr></tbody></table> <table style="border-collapse:collapse;border-spacing:0" width="100%"> <tbody><tr> <td style="padding:0;vertical-align:middle;padding-left:56px;padding-right:56px;word-break:break-word;word-wrap:break-word"> <p style="margin: 0 auto; max-width: 500px;Margin-top:0;font-style:normal;font-weight:400;font-size:13px;line-height:22px;Margin-bottom:22px;font-family:&quot;PT Serif&quot;,Georgia,serif;color:#7c7e7f"><br>Za tým <strong style="font-weight:bold"><span style="color:#fa0a0a">Datenode.cz</span></strong><br>Jana</p></td></tr></tbody></table> <div style="font-size:9px;line-height:9px">&nbsp;</div></td></tr></tbody></table> </td></tr></tbody></table> </td></tr></tbody></table><div style="min-width: 200px; vertical-align: top;margin: 10px; margin-top: 20px; display: inline-block;border-collapse:collapse;border-spacing:0;padding:24px;table-layout:fixed;background-color:#fff; max-width: 290px" width="100%"><h2 style="Margin-top:0;font-style:normal;font-weight:bold;font-size:16px;line-height:24px;Margin-bottom:16px;font-family:Ubuntu,sans-serif;color:#3e4751">Číst <span style="color:#f50e0e">Přiznání:</span></h2><ul style="Margin-top:0;font-style:normal;font-weight:400;padding-left:0;font-size:13px;line-height:22px;Margin-bottom:21px;font-family:&quot;PT Serif&quot;,Georgia,serif;color:#7c7e7f;Margin-left:15px"><li style="text-align: left;Margin-top:0;padding-left:0;Margin-bottom:10px;font-size:13px;line-height:21px"><em>Nevím, jak je to možné, ale hrozně mě vzrušuje když si představím svého přítele, jak spí s jinou holkou...<a href="http://priznaniosexu.cz/page/confession?id=18869" style="font-weight:bold; color:#fc1212">více</a></em></li><li style="text-align: left;Margin-top:0;padding-left:0;Margin-bottom:10px;font-size:13px;line-height:21px"><em>Dneska jsem zastavil na parkovišti a naproti mne krásna slečna uklízela nákup do kufru auta měla legýny...<a href="http://priznaniosexu.cz/page/confession?id=18902" style="font-weight:bold; color:#fc1212">více</a></em></li><li style="text-align: left;Margin-top:0;padding-left:0;Margin-bottom:10px;font-size:13px;line-height:21px"><em>Šel jsem na diskotéku, kde byla i moje bývalá. Teď už ale měla delší dobu kluka a já čerstvě holku. Setkali jsme se u baru...<a href="http://priznaniosexu.cz/page/confession?id=18933" style="font-weight:bold; color:#fc1212">více</a></em></li></ul></div><div style="min-width: 200px; vertical-align: top;margin: 10px; margin-top: 20px;display: inline-block;border-collapse:collapse;border-spacing:0;padding:24px;table-layout:fixed;background-color:#fff; max-width: 290px" width="100%"><h2 style="Margin-top:0;font-style:normal;font-weight:bold;font-size:16px;line-height:24px;Margin-bottom:16px;font-family:Ubuntu,sans-serif;color:#3e4751">Datenode <span style="color:#f50a15">Blog</span></h2><ul style="max-width: 150px; Margin-top:0;font-style:normal;font-weight:400;padding-left:0;font-size:13px;line-height:22px;Margin-bottom:21px;font-family:&quot;PT Serif&quot;,Georgia,serif;color:#7c7e7f;Margin-left:15px"><li style="text-align: left;Margin-top:0;padding-left:0;Margin-bottom:10px"><a style="font-weight: bold;color: #fc1212;text-decoration: underline; font-style: italic;" href="http://datenode.cz/blog.article/default/5-zahad-sexualni-pritazlivosti">5 záhad sexuální přitažlivosti</a></li><li style="text-align: left;Margin-top:0;padding-left:0;Margin-bottom:10px"><a style="font-weight: bold;color: #fc1212;text-decoration: underline; font-style: italic;" href="http://datenode.cz/blog.article/default/co-udela-ranni-sex-pro-vase-zdravi">Co udělá ranní sex pro vaše zdraví?</a></li><li style="text-align: left;Margin-top:0;padding-left:0;Margin-bottom:10px"><a style="font-weight: bold;color: #fc1212;text-decoration: underline; font-style: italic;" href="http://datenode.cz/blog.article/default/6-veci-ktere-nikdy-nesmite-rict-svoji-holce">6 věcí, které nikdy nesmíte říct svojí holce</a></li></ul></div><table style="border-collapse:collapse;border-spacing:0;table-layout:fixed;width:100%; background-color:#ededf1"> <tbody><tr> <td style="padding:0;vertical-align:top;text-align:left;padding-top:40px;padding-bottom:75px;"> <table style="border-collapse:collapse;border-spacing:0;width:100%"> <tbody><tr> <td style="padding:0;vertical-align:top;font-family:Ubuntu,sans-serif;text-align:left;font-size:12px;line-height:20px;color:gray"><div style="padding: 5px 10px; max-width: 500px; margin: 0 auto;"><div style="line-height:22px;font-size:22px">&nbsp;</div><div style="font-size:1px;line-height:1px;background-color:#c2c2d0;width:13px">&nbsp;</div><div style="line-height:22px;font-size:22px">&nbsp;</div><div>Tento e-mail jsme ti poslali, protože ses registrovala na <a href="http://www.priznaniosexu.cz" target="_blank">www.priznaniosexu.cz</a>. Můžeš se od odběru odhlásit, ale bude nás to mrzet.</div><div> <span><a style="font-weight:bold;text-decoration:none;color:gray" href="http://datenode.cz/cron-email/unsubscribe?oldUserId=' . $this->userId . '" target="_blank"> Odhlásit odběr</a> </span></div></div></td></tr></tbody></table> </td></tr></tbody></table> </center> </div>';
	}

	/**
	 * Vrátí zprávu co se má odeslat uživateli.
	 */
	public function getEmailSubject() {
		return "Tomáš Ti napsal zprávu!";
	}

	protected function getEmailSender() {
		return self::EMAIL_ADDRESS_SENDER;
	}

}
