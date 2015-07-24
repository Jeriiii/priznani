<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 15.7.2015
 */

namespace POS\Ext\SimpleMenu;

/**
 * Jedna položka v menu
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Item implements IItem {

	/** @var string Název položky */
	public $name;

	/** @var string Odkaz položky v menu */
	public $link;

	/** @var string Titulek položky v menu */
	public $tittle;

	/** @var string Další atributy html elementu jako Id a data ... */
	public $attributes = array();

	/** @var string Třída položky */
	public $elClass;

	/** @var boolean Položka se zobrazí pouze pokud je
	 * true = už. přihlášen.
	 * false = uživatel odhlášen
	 * null = nereaguje na přihlášení a odhlášení uživatele
	 */
	public $showForLoggetIn = null;

	public function __construct($name, $link = null, $elClass = null, array $attributes = array(), $tittle = null) {
		$this->name = $name;
		$this->link = $link;
		$this->attributes = $attributes;
		$this->elClass = $elClass;
		$this->tittle = $tittle == null ? $name : $tittle;
	}

	public function setLoggetIn($showForLoggetIn) {
		$this->showForLoggetIn = $showForLoggetIn;
	}

	public function isGroup() {
		return FALSE;
	}

	public function isItem() {
		return TRUE;
	}

}
