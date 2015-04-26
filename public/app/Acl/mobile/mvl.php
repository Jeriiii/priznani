<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

use NetteExt\TemplateManager\MobileTemplates\MobileViewList;

/**
 * Třída dědící od seznamu mobilních šablon použitých v presenterech.
 * Slouží jako seznam presenterů a viewů, které se mají přepnout na mobilní.
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class Mvl extends MobileViewList {

	function __construct() {
		parent::__construct();

		$this->addView("OnePage", "default");

		$this->addView("Chat", "conversations");
		$this->addView("Chat", "default");

		$this->addView("Activities", "default");

		$this->addView("Friends", "requests");
		$this->addView("Friends", "list");

		$this->addView("Sign", "in");

		//profilModule
		$this->addLayout("Profil:Show", "default");
	}

}
