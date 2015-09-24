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
use POS\Model\UserDao;

class EmailNotify extends Email {

	/** @var int Počet zpráv pro uživatele. */
	private $countMessages = 0;

	/** @var int Počet JSI SEXY */
	private $countYouAreSexy = 0;

	/** @var int Další upozornění */
	private $countOthersActivities;

	/** @var string Text přiznání ke zobrazení */
	private $confessionText = NULL;

	/** @var string Odkaz na týdenní změnu odesílání info emailu */
	private $setWeeklyLink;

	/** @var array uživatelé, co se mají ukázat v emailu. Pole, jehož hodnoty jsou cesty k profilovým fotkám */
	private $usersToShow;

	public function __construct(ActiveRow $user, $setWeeklyLink, $confessionText, $usersToShow = array()) {
		parent::__construct($user);
		$this->confessionText = $confessionText;
		$this->setWeeklyLink = $setWeeklyLink;
		$this->usersToShow = $usersToShow;
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

		return "DateNode - " . $title . '';
	}

	/**
	 * Vrtátí zprávu co se má odeslat uživateli.
	 */
	public function getEmailBody() {
		$this->htmlBody = TRUE;

		$messagesNotice = $this->getMessageTitle();
		$andNotice = $this->getAndToTitle();
		$notificationsNotice = $this->getActivityTitle();
		$adminPhoto = $this->renderProfilePhoto('http://datenode.cz/', 'http://datenode.cz/images/email/admin-user.jpg');
		$messageBody = "$adminPhoto
			<h2 style='width: auto; float: left; height: 30px; font-size: 30px; padding: 70px 0px 0px 10px; margin: 0; font-weight: 200;'>AHOJ,</h2>
			<div style='clear: both'></div>
			<p style='float: left; margin: 0; padding: 5px 10px 10px 110px; width: auto; display: block;'>
				právě jsi dostal na datenode.cz <span style='color: #ae1212; font-style: italic;'>$messagesNotice</span>$andNotice<span style='color: #ae1212; font-style: italic;'>$notificationsNotice</span>.<br />
				<b>Přihlaš se</b>, ať ti nic neuteče. Nikdy nevíš, kdo na tebe čeká.
			</p>
			<div style='clear: both'></div>
		";
		$body = $this->renderHeader()
			. "<div style='padding: 20px 10%; font-family: Arial;'>"
			. "$messageBody"
			. $this->renderLinkButton('http://datenode.cz/', 'PŘEČÍST NOVÉ ZPRÁVY')
			. $this->renderFriendsList()
			. "</div>"
			. $this->renderQuote()
			. $this->renderDivider();

		$userPeriod = $this->user->offsetGet(UserDao::COLUMN_EMAIL_NEWS_PERIOD);
		if ($userPeriod == UserDao::EMAIL_PERIOD_DAILY && !empty($this->setWeeklyLink)) {
			$body = $body . '<p style="text-align: center; margin: 0 20%; color: #aaa;">Dostáváš od nás příliš mnoho e-mailů? Zde můžeš <a style="color: #cf0707; text-decoration: underline;" href="' . $this->setWeeklyLink . '">přepnout na jeden e-mail týdně</a>. Jenom pozor, abys o něco nepřišel/la.</p>';
		}

		return $body;
	}

	/**
	 * Metoda tvořící seznam vhodných lidí
	 * TODO dodělat platné odkazy na každou fotku a na šipku, dodělat platná url obrázků uživatelů
	 * @return string html
	 */
	private function renderFriendsList() {
		if (empty($this->usersToShow)) {
			return;
		}
		$html = "<div style='float: left; width: auto; margin: 15px 0;'>";
		$itemStyle = 'float: left; margin: 5px; position: relative;';
		foreach ($this->usersToShow as $photoUrl) {
			$html = $html . "<a href ='http://datenode.cz/' style = '$itemStyle'><img src='$photoUrl' width = '60' height = '60' alt = '' /></a>";
		}

		$html = $html . "</div><div style = 'clear: both'></div>";
		return $html;
	}

	/**
	 * Vytvoří html velkého zeleného tlačítka
	 * @param String $href odkaz, na který tlačítko vede
	 * @param String $buttonText text v tlačítku
	 * @return String vygenerované html
	 */
	private function renderLinkButton($href, $buttonText) {
		$linkButtonStyle = 'clear: both; display: block; text-align: center; cursor: pointer; color: white !important; text-decoration: none !important; font-family: Arial; font-size: 24px; margin: 25px 20%; text-decoration: none; padding: 8px 5%; font-weight: 600; background: #90cb00; background: #90cb00;background: -moz-linear-gradient(top,#cf0707 0,#5a8700 100%);background: -webkit-gradient(linear,left top,left bottom,color-stop(0,#90cb00),color-stop(100%,#5a8700));background: -webkit-linear-gradient(top,#90cb00 0,#5a8700 100%);background: -o-linear-gradient(top,#cf0707 0,#5a8700 100%);background: -ms-linear-gradient(top,#cf0707 0,#5a8700 100%);background: linear-gradient(to bottom,#90cb00 0,#5a8700 100%);';
		return "<a href = '$href' style = '$linkButtonStyle' >$buttonText</a><div style='clear:both;'></div>";
	}

	/**
	 * Vytvoří html profilové fotky
	 * @param String $href odkaz na fotce
	 * @param String $photoUrl url obrázku fotky
	 * @return String vygenerované html
	 */
	private function renderProfilePhoto($href, $photoUrl) {
		return "<a href = '$href'><img src = '$photoUrl' width = '100' height = '100' alt = '' style = 'border-radius: 50%; float:left;' /></a>";
	}

	/**
	 * Vytvoří html horní lišty s logem
	 * @return String vytvořené html
	 */
	private function renderHeader() {
		$headerStyle = 'height: 47px; background: #CF0707;background: -moz-linear-gradient(top,#cf0707 0,#890302 100%);background: -webkit-gradient(linear,left top,left bottom,color-stop(0,#CF0707),color-stop(100%,#890302));background: -webkit-linear-gradient(top,#CF0707 0,#890302 100%);background: -o-linear-gradient(top,#cf0707 0,#890302 100%);background: -ms-linear-gradient(top,#cf0707 0,#890302 100%);background: linear-gradient(to bottom,#CF0707 0,#890302 100%);text-shadow: 0 0 1px #FFF;color: #FFF;display: block;font-size: 36px;';
		return "<div style = '$headerStyle'>
<h1 style = 'line-height: 47px; padding: 0 10%; font-family: Arial; font-size: 36px; font-weight: 200;'><b style = 'font-weight: 600;'>DATE</b>NODE<span style = 'font-size: 16px;'>.cz</span></h1>
<div class = 'clear'></div>
</div>";
	}

	/**
	 * Vykreslí citát (přiznání).
	 * TODO udělat citáty tak, aby byl ten den stejný, pole quotations je jen příklad
	 * @return String vygenerované html
	 */
	private function renderQuote() {
		if (!empty($this->confessionText)) {
			$quoteHtml = $this->renderLine() . "<div style='clear: both; text-align: center; padding: 20px 10%; color: #888; font-family: 'Times new roman', serif;'>
				<strong style='font-size: 24px;'>Přiznání pro dnešní den</strong>
				<br />
				<p style='font-style: italic;'>$this->confessionText</p>
			</div>";
			return $quoteHtml . $this->renderLine();
		} else {
			return '';
		}
	}

	/**
	 * Vrátí html pro vodorovnou čáru
	 * @return string vygenerované html
	 */
	private function renderLine() {
		return '<div style="clear: both; height: 1px; margin: 0 10%; border-top: 1px solid #aaa; background: #eee;"></div>';
	}

	/**
	 * Vytvoří html oddělovače (čárkovaná čára)
	 * @return string vygenerované html
	 */
	private function renderDivider() {
		return '<div style="height: 1px; margin: 30px 45%; border-top: 1px dashed #aaa;"></div>';
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
