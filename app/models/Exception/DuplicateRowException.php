<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Description of DuplicateRowException
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POS\Exception;

class DuplicateRowException extends \Exception {

	public function __construct() {
		parent::__construct("Rows are duplicate.");
	}

}
