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

	protected $users;

	public function __construct($users, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->users = $users;
	}

	public function render() {
		$this->template->setFile(dirname(__FILE__) . '/baseSearch.latte');

		$this->template->items = $this->users;
		$this->template->render();
	}

}

?>
