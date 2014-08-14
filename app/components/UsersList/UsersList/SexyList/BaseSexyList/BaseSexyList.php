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

class BaseSexyList extends UsersList {

	/** @var \POS\Model\YouAreSexyDao */
	public $youAreSexyDao;

	/** @var int ID uživatele - zobrazují se uživatelé, které tento uživatel označil / kteří ho označili */
	protected $userID;

	/** @var \Nette\Database\Table\Selection Seznam uživatelů co se mají vykreslit */
	protected $sexyUsers;

	public function __construct(YouAreSexyDao $youAreSexyDao, $userID, $parent, $name) {
		parent::__construct($parent, $name);

		$this->userID = $userID;
		$this->youAreSexyDao = $youAreSexyDao;
		$this->sexyUsers = $this->getSexyUsers();
	}

	public function baseRender($templateName) {
		$this->template->setFile(dirname(__FILE__) . '/../' . $templateName);
		$this->template->sexyUsers = $this->sexyUsers;
		$this->template->render();
	}

}
