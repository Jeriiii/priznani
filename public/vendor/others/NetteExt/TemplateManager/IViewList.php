<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 15.3.2015
 */

namespace NetteExt\TemplateManager;

/**
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
interface IViewList {

	/**
	 * Je tato kombinace v seznamu mobilních šablon?
	 * @param string $presenterName jméno presenteru
	 * @param string $viewName jméno viewu
	 * @return bool
	 */
	public function hasView($presenterName, $viewName);

	/**
	 * Je tato kombinace v seznamu mobilních layoutů?
	 * @param string $presenterName jméno presenteru
	 * @param string $viewName jméno viewu
	 * @return bool
	 */
	public function hasLayout($presenterName, $viewName);

	/**
	 * Vrátí jméno souboru pro layout nebo NULL pokud neexistuje
	 * @param string $presenterName jméno presenteru
	 * @param string $viewName jméno viewu
	 * @return string|NULL
	 */
	public function getLayoutName($presenterName, $viewName);

	/**
	 * Vrátí jméno souboru pro šablonu nebo NULL pokud neexistuje
	 * @param string $presenterName jméno presenteru
	 * @param string $viewName jméno viewu
	 * @return string|NULL
	 */
	public function getViewName($presenterName, $viewName);
}
