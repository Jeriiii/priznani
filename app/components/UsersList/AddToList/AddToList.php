<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Základní komponenta pro přidání do listů uživatelů a pod.
 *
 * @author Petr Kukrál
 */

namespace POSComponent\AddToList;

use POSComponent\BaseProjectControl;

class AddToList extends BaseProjectControl {

	/**
	 * @var int ID žadatele
	 */
	public $userIDFrom;

	/**
	 * @var int ID příjemce
	 */
	public $userIDTo;

	public function __construct($userIDFrom, $userIDTo, $parent, $name) {
		parent::__construct($parent, $name);
		$this->userIDFrom = $userIDFrom;
		$this->userIDTo = $userIDTo;
	}

}
