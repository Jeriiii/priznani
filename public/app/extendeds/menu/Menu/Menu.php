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
	/* ukázat položku jen na určitých stránkách */

	const SHOW_ALL_PRES = 0;
	const SHOW_ONE_PAGE = 1;

	/** @var int Pozice při procházení iterátorem. */
	private $position = 0;

	/** @var array Pole položek a nadpisů v menu */
	private $items = array();

	/** @var boolean TRUE = uživatel je přihlášen, jinak FALSE. */
	private $loggetIn;

	/** @var int Na jakém presenteru se dá položka zobrazit. */
	private $showOn;

	public function __construct($loggetIn = FALSE, $showOn = self::SHOW_ALL_PRES) {
		$this->loggetIn = $loggetIn;
		$this->showOn = $showOn;
	}

	/**
	 * Přidání položky do menu.
	 * @param \POS\Ext\Menu\OnePageLeft\Item $item
	 * @param boolean $isLoggetIn Zobrazí se jen pokud je uživatel přihlášen / odhlášen.
	 * Null = nereaguje na při. /odhlášení.
	 * @param int $showOn Na jakém presenteru se dá položka zobrazit.
	 */
	public function addItem(Item $item, $isLoggetIn = null, $showOn = self::SHOW_ALL_PRES) {
		$item->setLoggetIn($isLoggetIn);

		$this->add($item, $isLoggetIn, $showOn);
	}

	/**
	 * Přidá skupinu k příspěvkům (přidává se nejdříve skupina a teprve
	 * pak příspěvky v ní).
	 * @param \POS\Ext\Menu\OnePageLeft\Group $group
	 * @param boolean $isLoggetIn Zobrazí se jen pokud je uživatel přihlášen / odhlášen.
	 * @param int $showOn Na jakém presenteru se dá položka zobrazit.
	 */
	public function addGroup(Group $group, $isLoggetIn = null, $showOn = self::SHOW_ALL_PRES) {
		$this->add($group, $isLoggetIn, $showOn);
	}

	/**
	 * Přidání položky do menu.
	 * @param \POS\Ext\Menu\OnePageLeft\Item $generalItem Skupina nebo položka
	 * @param boolean $isLoggetIn Zobrazí se jen pokud je uživatel přihlášen / odhlášen.
	 * Null = nereaguje na při. /odhlášení.
	 * @param int $showOn Na jakém presenteru se dá položka zobrazit.
	 */
	private function add(IItem $generalItem, $isLoggetIn = null, $showOn = self::SHOW_ALL_PRES) {
		/* vyřadí položky specifické jen pro určité presentery */
		if ($showOn != self::SHOW_ALL_PRES && $showOn != $this->showOn) {
			return;
		}

		if ($isLoggetIn === null) {
			$this->items[] = $generalItem;
			return;
		}

		if ($isLoggetIn == $this->loggetIn) {
			$this->items[] = $generalItem;
		}
	}

	/*	 * ************* Metody pro iteraci položek v menu ****************** */

	public function rewind() {
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
