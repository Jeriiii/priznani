<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POS\Ajax;

/**
 * Příklad Handlu pro AjaxObserver
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ExampleHandle extends \Nette\Object implements IObserverHandle {

	public function getData() {
		return "data";
	}

}
