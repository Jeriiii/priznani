<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Zobrazuje seznam sexy uživatelů, které označil uživatel
 *
 * @author Petr Kukrál
 */

namespace POSComponent\UsersList\SexyList;

class IMarked extends BaseSexyList {

	public function getSexyUsers() {
		return $this->youAreSexyDao->getAllFromUser($this->userID);
	}

	public function render() {
		parent::baseRender("IMarked/iMarked.latte");
	}

}
