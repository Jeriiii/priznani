<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 15.7.2015
 */

namespace POS\Ext\SimpleMenu;

/**
 * Skupina položek v menu (např. Profil, zábava ...)
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Group implements IItem {

	/** @var string Název položky */
	public $name;

	public function __construct($name) {
		$this->name = $name;
	}

	public function isGroup() {
		return TRUE;
	}

	public function isItem() {
		return FALSE;
	}

}
