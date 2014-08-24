<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Zobrazuje seznam uživatelů, které uživatele označili jako sexy
 *
 * @author Petr Kukrál
 */

namespace POSComponent\UsersList\SexyList;

class MarkedFromOther extends BaseSexyList implements IBaseSexyList {

	public function getSexyUsers() {
		return $this->youAreSexyDao->getAllToUser($this->userID);
	}

	public function render() {
		parent::baseRender("MarkedFromOther/markedFromOther.latte");
	}

}
