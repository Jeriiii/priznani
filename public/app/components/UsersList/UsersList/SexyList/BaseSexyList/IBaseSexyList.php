<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Třída která dědí od BaseSexyList by měla implementovat toto rozhraní
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POSComponent\UsersList\SexyList;

interface IBaseSexyList {

	/**
	 * Vrací seznam uživatelů, co se mají vykreslit
	 * @return \Nette\Database\Table\Selection
	 */
	public function getSexyUsers();

	public function render();
}
