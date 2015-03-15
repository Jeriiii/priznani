<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Základní komponenta pro zobrazení seznamu sexy uživatelů
 *
 * @author Petr Kukrál
 */

namespace POSComponent\UsersList\SexyList;

use POS\Model\YouAreSexyDao;
use POSComponent\UsersList\UsersList;

abstract class BaseSexyList extends UsersList {

	/** @var \POS\Model\YouAreSexyDao */
	public $youAreSexyDao;

	/** @var int ID uživatele - zobrazují se uživatelé, které tento uživatel označil / kteří ho označili */
	protected $userID;

	public function __construct(YouAreSexyDao $youAreSexyDao, $userID, $parent, $name) {
		parent::__construct($parent, $name);

		$this->userID = $userID;
		$this->youAreSexyDao = $youAreSexyDao;
	}

}
