<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */
/**
 * Zaručuje, že bude komponenta použitelná pro plugin stream. Tedy
 * že bude obsahovat všechny potřebné metody, který tento plugin
 * vyžaduje.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POSComponent\UsersList;

use POSComponent\BaseProjectControl;

abstract class AjaxList extends BaseProjectControl implements \IAjaxBox {

	/** @var int Posun příspěvků při rolování */
	protected $offset = 0;

	/** @var int Limit na jedno načtení */
	protected $limit;

	/**
	 * Uloží předaný ofsset jako parametr třídy a invaliduje snippet s příspěvky
	 * @param int $offset O kolik příspěvků se mám při načítání dalších příspěvků z DB posunout.
	 */
	public function handleGetMoreData($offset, $limit) {
		$this->offset = $offset;
		$this->limit = $limit;
		$this->setData($this->offset);

		if ($this->presenter->isAjax()) {
			$this->invalidateControl($this->getSnippetName());
		} else {
			$this->redirect('this');
		}
	}

}
