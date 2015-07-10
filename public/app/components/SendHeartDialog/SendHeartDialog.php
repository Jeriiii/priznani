<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POSComponent;

use POS\Model\UserPropertyDao;
use POS\Model\ActivitiesDao;

/**
 * Dialog pro poslání srdíčka
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class SendHeartDialog extends BaseProjectControl {

	/**
	 * Přihlášený uživatel
	 * @var ArrayHash|ActiveRow
	 */
	public $loggedUser;

	/**
	 * @var \POS\Model\UserPropertyDao
	 * @inject
	 */
	public $propertyDao;

	/**
	 * @var \POS\Model\ActivitesDao
	 * @inject
	 */
	public $activitesDao;

	/**
	 * 	 Kolik mincí stojí poslání srdíčka.
	 */
	const HEART_PRICE = 5;

	public function __construct($loggedUser, UserPropertyDao $propertyDao, ActivitiesDao $activitesDao, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->loggedUser = $loggedUser;
		$this->propertyDao = $propertyDao;
		$this->activitesDao = $activitesDao;
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/sendHeartDialog.latte');

		$template->profID = $this->getPresenter()->getUserID();
		$template->canAfford = $this->haveEnaughCoins();
		$template->heartPrice = self::HEART_PRICE;
		$template->loggedUser = $this->loggedUser;
		$template->render();
	}

	/**
	 * Zpracuje potvrzení uživatele, že chce poslat srdíčko.
	 * @param $idReceiver id uživatele, který má srdíčko obdržet
	 */
	public function handleSendHeart($idReceiver) {
		$presenter = $this->getPresenter();
		if ($this->haveEnaughCoins()) {

			$presenter->getSession('loggedUser')->remove(); //odstranění cache uživatelských údajů
			$presenter->flashMessage('Srdíčko úspěšně odesláno', 'success');
			$presenter->redirect('this');
		} else {
			$presenter->flashMessage('Toto srdíčko si bohužel nemůžete dovolit.');
			$presenter->redirect('this');
		}
	}

	/**
	 * Má přihlášený uživatel dostatek zlatek na srdíčko?
	 * @return bool
	 */
	public function haveEnaughCoins() {
		return $this->loggedUser->property->coins >= self::HEART_PRICE;
	}

}
