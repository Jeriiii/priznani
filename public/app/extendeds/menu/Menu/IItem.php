<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 24.7.2015
 */

namespace POS\Ext\SimpleMenu;

/**
 * Rozhraní pro správu typů položek v menu
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
interface IItem {

	/**
	 * Vrací TRUE, když je objekt třídy Item
	 */
	public function isItem();

	/**
	 * Vrací TRUE, když je objekt třídy Item
	 */
	public function isGroup();
}
