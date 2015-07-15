<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 15.7.2015
 */

namespace POS\Ext\Menu\OnePageLeft;

/**
 * Skupina položek v menu (např. Profil, zábava ...)
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Group {

	/** @var string Název položky */
	private $name;

	public function __construct($name) {
		$this->name = $name;
	}

}
