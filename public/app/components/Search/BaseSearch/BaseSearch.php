<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @author Petr Kukrál <p.kukral@kukral.eu>
 *
 * Základ komponenty, umožňující zobrazení uživatelů při vyhledávání
 */

namespace POSComponent\Search;

use POSComponent\BaseProjectControl;
use Nette\Database\Table\Selection;

class BaseSearch extends BaseProjectControl {

	const LIMIT_LIST_FEW = 7;
	const LIMIT_LIST_MOBILE = 5; /* na mobilu */

	protected $users;

	public function __construct($users, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->users = $users;
	}

	public function renderBase($mode) {
		$this->template->setFile(dirname(__FILE__) . '/baseSearch.latte');

		if ($mode == 'listFew') {
			$this->users = array_slice((array) $this->users, 0, self::LIMIT_LIST_FEW - 1);
		}
		if ($mode == 'listMobile') {
			$this->users = array_slice((array) $this->users, 0, self::LIMIT_LIST_MOBILE - 1);
		}

		$this->template->items = $this->users;
		$this->template->mode = $mode;
		$this->template->render();
	}

}

?>
