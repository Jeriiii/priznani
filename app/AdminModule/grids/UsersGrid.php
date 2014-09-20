<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 *  UsersGrid pro editaci uživatelů (změna práv, smazání) a filtrování dle emailu a nicku
 *
 * @author Christine Baierová
 */

namespace POS\Grids;

use Grido\Grid;
use POS\Model\UserDao;

class UsersGrid extends Grid {

	/** @var \POS\Model\UserDao */
	public $userDao;

	public function __construct(UserDao $userDao, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->userDao = $userDao;

		$this->setModel($userDao->getAll());

		$this->addColumns();
		$this->addActionHrefs();
	}

	/**
	 * Přidá sloupce do grida.
	 */
	private function addColumns() {

		$this->addColumnText("user_name", "Nick")
			->setColumn('user_name')
			->setFilterText();

		$this->addColumnText("email", "Email")
			->setColumn('email')
			->setFilterText();
	}

	/**
	 * Přidá tlačítka s akcemi do grida.
	 */
	private function addActionHrefs() {
		$this->addActionHref('change_role', 'Změnit práva', 'changeRole!');

		$this->addActionHref('delete_user', 'Smazat', 'deleteUser!')
			->setConfirm(function($item) {
				return "Opravdu chcete smazat {$item->email} ?";
			});
	}

}
