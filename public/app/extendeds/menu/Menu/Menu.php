<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 15.7.2015
 */

namespace POS\Ext\SimpleMenu;

/**
 * Menu v levé části stránky na one page, nebo v mobilní verzi v layoutu
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Menu implements \Iterator {

	/** @var int Pozice při procházení iterátorem. */
	private $position = 0;

	/** @var array Pole položek a nadpisů v menu */
	private $items = array();

	/** @var boolean TRUE = uživatel je přihlášen, jinak FALSE. */
	private $loggetIn;

	public function __construct($loggetIn = FALSE) {
		$this->loggetIn = $loggetIn;
	}

	/**
	 * Přidání položky do menu.
	 * @param \POS\Ext\Menu\OnePageLeft\Item $item
	 * @param boolean $isLoggetIn Zobrazí se jen pokud je uživatel přihlášen / odhlášen.
	 * Null = nereaguje na při. /odhlášení.
	 */
	public function addItem(Item $item, $isLoggetIn = null) {
		$item->setLoggetIn($isLoggetIn);

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

	/*	 * ************* Metody pro iteraci položek v menu ****************** */

	public function rewind() {
		/* čištění položek pro přihlášené / nepřihlášené uživatele */
		foreach ($this->items as $key => $item) {
			if ($item instanceof Item) {
				if ($item->showForLoggetIn === null) {
					continue;
				}
				if ($item->showForLoggetIn != $this->loggetIn) {
					unset($this->items[$key]);
				}
			}
		}

		$this->items = array_values($this->items); //resetuje klíče od 0

		$this->position = 0;
	}

	public function current() {
		return $this->items[$this->position];
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		++$this->position;
	}

	public function valid() {
		return isset($this->items[$this->position]);
	}

}
