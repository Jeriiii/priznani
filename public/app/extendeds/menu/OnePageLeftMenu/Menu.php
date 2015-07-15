<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 15.7.2015
 */

namespace POS\Ext\Menu\OnePageLeft;

/**
 * Menu v levé části stránky na one page, nebo v mobilní verzi v layoutu
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Menu {

	/** @var array Pole položek a nadpisů v menu */
	private $items = array();

	/**
	 * Přidání položky do menu.
	 * @param \POS\Ext\Menu\OnePageLeft\Item $item
	 * @param boolean $isLoggetIn Zobrazí se jen pokud je uživatel přihlášen / odhlášen.
	 * Null = nereaguje na při. /odhlášení.
	 */
	public function addItem(Item $item, $isLoggetIn = null) {
		if ($isLoggetIn !== null) {
			$item->setLoggetIn($isLoggetIn);
		}

		$this->items[] = $item;
	}

	/**
	 * Přidá skupinu k příspěvkům (přidává se nejdříve skupina a teprve
	 * pak příspěvky v ní).
	 * @param \POS\Ext\Menu\OnePageLeft\Group $group
	 */
	public function addGroup(Group $group) {
		$this->items[] = $group;
	}

}
