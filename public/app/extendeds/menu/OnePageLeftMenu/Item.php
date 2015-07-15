<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 15.7.2015
 */

namespace POS\Ext\Menu\OnePageLeft;

/**
 * Jedna položka v menu
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Item {

	/** @var string Název položky */
	public $name;

	/** @var string Odkaz položky v menu */
	public $link;

	/** @var string Titulek položky v menu */
	public $tittle;

	/** @var string Id položky */
	public $elId;

	/** @var string Třída položky */
	public $elClass;

	/** @var boolean Položka se zobrazí pouze pokud je
	 * true = už. přihlášen.
	 * false = uživatel odhlášen
	 * null = nereaguje na přihlášení a odhlášení uživatele
	 */
	public $showForLoggetIn = null;

	public function __construct($name, $link = null, $elClass = null, $elId = null, $tittle = null) {
		$this->name = $name;
		$this->link = $link;
		$this->elId = $elId;
		$this->elClass = $elClass;
		$this->tittle = $tittle == null ? $name : $tittle;
	}

	public function setLoggetIn($showForLoggetIn) {
		$this->showForLoggetIn = $showForLoggetIn;
	}

}
