<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

use NetteExt\TemplateManager\MobileTemplates\MobileViewList;

/**
 * Třída dědící od seznamu mobilních šablon použitých v presenterech. Slouží jako seznam presenterů a viewů, které se mají přepnout na mobilní
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class Mvl extends MobileViewList {

	function __construct() {
		parent::__construct();
	}

}
