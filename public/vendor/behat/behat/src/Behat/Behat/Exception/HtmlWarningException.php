<?php

namespace Behat\Behat\Exception;

use Nette\Utils\Html;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Warning exception with html.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class HtmlWarningException extends BehaviorException {

	/**
	 * Initializes pending exception.
	 *
	 * @param string $html Html
	 */
	public function __construct($html) {
		parent::__construct($html);
	}

}
