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

	/** @var string Odkaz na týdenní změnu odesílání info emailu */
	private $setWeeklyLink;
	private $quotations = array(
		array('quote' => 'Tajemství lze udržet mezi třemi osobami. Za předpokladu, že jsou dvě již na pravdě boží.', 'author' => 'Benjamin Franklin'),
		array('quote' => 'Tajemství lze udržet mezi třemi osobami. Za předpokladu, že jsou dvě již na pravdě boží.2', 'author' => 'Benjamin Franklin2'),
		array('quote' => 'Tajemství lze udržet mezi třemi osobami. Za předpokladu, že jsou dvě již na pravdě boží.3', 'author' => 'Benjamin Franklin3')
	);

	public function __construct(ActiveRow $user, $setWeeklyLink) {
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
		$adminPhoto = $this->renderProfilePhoto('', 'http://lorempixel.com/output/people-q-c-100-100-9.jpg'); /* TODO změnit na fotku admina + odkaz pokud je třeba */
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
			. $this->renderLinkButton('', 'PŘEČÍST NOVÉ ZPRÁVY')/* TODO dopsat tlačítku href odkaz sem do parametru - kam? na zprávy? */
			. $this->renderFriendsList()
			. "</div>"
			. $this->renderQuote()
			. $this->renderDivider();

		$userPeriod = $this->user->offsetGet(UserDao::COLUMN_EMAIL_NEWS_PERIOD);
		if ($userPeriod == UserDao::EMAIL_PERIOD_DAILY) {
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
		$html = "<div style='float: left; width: auto; margin: 15px 0;'>";
		$itemStyle = 'float: left; margin: 5px; position: relative;';
		$friends = array(0 => 'http://lorempixel.com/output/people-q-c-100-100-9.jpg',
			2 => 'http://lorempixel.com/output/people-q-c-100-100-9.jpg',
			5 => 'http://lorempixel.com/output/people-q-c-100-100-9.jpg',
			7 => 'http://lorempixel.com/output/people-q-c-100-100-9.jpg'
		);
		foreach ($friends as $friendId => $photoUrl) {
			$html = $html . "<a href ='#$friendId' style = '$itemStyle'><img src='$photoUrl' width = '60' height = '60' alt = '' /></a>";
		}
		$arrowUrl = 'http://datenode.cz/images/userSearch/search-arrow.png';
		$html = $html . "<a href = '' style = 'background-image: url($arrowUrl); float: left; position: relative; z-index: 5; left: -35px; top: 5px; cursor: pointer;width: 28px;height: 58px;display: block;background-size: 28px 58px;background-position: 0 0;background-color: rgba(0, 0, 0, 0);background-repeat: no-repeat;border: 1px solid #C9C9C9;'></a><div style = 'clear: both'></div>
</div>
<div style = 'clear: both'></div>";
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
		/* TODO dopsat tlačítku href odkaz - kam? na zprávy? */
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
	 * Vykreslí citát.
	 * TODO udělat citáty tak, aby byl ten den stejný, pole quotations je jen příklad
	 * @return String vygenerované html
	 */
	private function renderQuote() {
		$quote = $this->quotations[array_rand($this->quotations)];
		$quoteMessage = $quote['quote'];
		$quoteAuthor = $quote['author'];
		$quoteHtml = $this->renderLine() . "<div style='clear: both; text-align: center; padding: 20px 10%; color: #888; font-family: 'Times new roman', serif;'>
			<strong style='font-size: 24px;'>Citát pro dnešní den</strong>
			<br />
			<p style='font-style: italic;'>$quoteMessage</p>
			<strong> - $quoteAuthor - </strong>
		</div>";
		return $quoteHtml . $this->renderLine();
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
