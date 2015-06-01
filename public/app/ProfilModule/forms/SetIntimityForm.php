<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 31.5.2015
 */

namespace Nette\Application\UI\Form;

use Nette\Forms\Form;
use Nette\Database\Table\ActiveRow;
use POS\Model\UserPropertyDao;
use NetteExt\Session\SessionManager;
use NetteExt\DaoBox;

/**
 * Formulář pro nastavení intimity.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class SetInitimityForm extends BaseForm {

	/** @var \Nette\Database\Table\ActiveRow Přihlášený uživatel. */
	private $loggedUser;

	/** @var SessionManager */
	private $sessionManager;

	/** @var DaoBox */
	private $daoBox;

	public function __construct(ActiveRow $loggedUser, SessionManager $sessionManager, DaoBox $daoBox, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->loggedUser = $loggedUser;
		$this->sessionManager = $sessionManager;
		$this->daoBox = $daoBox;

		$this->addGroup('Nastavení INTIMITY');

		$yesNo = array(1 => 'Ano', 0 => 'Ne');
		$intimity = $this->addRadioList('intimity', 'Chci vidět intimní fotky', $yesNo);

		$showIntim = $loggedUser->property->showIntim;
		if (!is_numeric($showIntim) && $showIntim == NULL) {
			$intimity->setDefaultValue(1);
		} else {
			$intimity->setDefaultValue($showIntim);
		}

		$this->addSubmit('send', 'Změnit')->setAttribute("class", "btn-main medium button button");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(SetInitimityForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$this->loggedUser->property->update(array(
			UserPropertyDao::COLUMN_SHOW_INTIM => $values->intimity
		));

		$daoBox = $this->daoBox;
		$sm = $this->sessionManager;
		/* přepočítání či smazání nacachovaných dat */
		$sm->cleanStreamPreferences($daoBox->userDao, $daoBox->streamDao, $daoBox->userCategoryDao);
		$sm->calculateLoggedUser($daoBox->userDao);

		$presenter->flashMessage('Nastavení bylo změněno');
		$presenter->redirect('this');
	}

}
