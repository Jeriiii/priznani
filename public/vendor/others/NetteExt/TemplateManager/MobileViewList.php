<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt\TemplateManager\MobileTemplates;

use Nette\ArrayHash;
use NetteExt\TemplateManager\IViewList;
use Nette\Utils\Strings;

/**
 * Třída uchovávající seznam dvojic presenterů a jejich viewů, které mají mít mobilní šablonu a (nebo) layout. Je možné ji iterovat.
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class MobileViewList extends ArrayHash implements IViewList {

	/**
	 * Implicitní název použitého layoutu (použije se, pokud není specifikován layout)
	 */
	const DEFAULT_LAYOUT_FILENAME = "mobileLayout";

	/**
	 * Implicitní prefix k šabloně (použije se, pokud není specifikována přesná šablona)
	 */
	const DEFAULT_VIEW_PREFIX = "mobile";

	function __construct() {
		$this->views = new ArrayHash();
		$this->layouts = new ArrayHash();
	}

	/**
	 * Je tato kombinace v seznamu mobilních šablon?
	 * @param string $presenterName jméno presenteru
	 * @param string $viewName jméno viewu
	 * @return bool
	 */
	public function hasView($presenterName, $viewName) {
		return $this->views->offsetExists($presenterName . "/" . $viewName);
	}

	/**
	 * Je tato kombinace v seznamu mobilních layoutů?
	 * @param string $presenterName jméno presenteru
	 * @param string $viewName jméno viewu
	 * @return bool
	 */
	public function hasLayout($presenterName, $viewName) {
		return $this->layouts->offsetExists($presenterName . "/" . $viewName);
	}

	/**
	 * Vrátí jméno souboru pro layout nebo NULL pokud neexistuje
	 * @param string $presenterName jméno presenteru
	 * @param string $viewName jméno viewu
	 * @return string|NULL
	 */
	public function getLayoutName($presenterName, $viewName) {
		if ($this->hasLayout($presenterName, $viewName)) {
			return $this->layouts->offsetGet($presenterName . "/" . $viewName)->layoutName;
		} else {
			return NULL;
		}
	}

	/**
	 * Vrátí jméno souboru pro šablonu nebo NULL pokud neexistuje
	 * @param string $presenterName jméno presenteru
	 * @param string $viewName jméno viewu
	 * @return string|NULL
	 */
	public function getViewName($presenterName, $viewName) {
		if ($this->hasLayout($presenterName, $viewName)) {
			return $this->views->offsetGet($presenterName . "/" . $viewName)->viewName;
		} else {
			return NULL;
		}
	}

	/**
	 * Přidá presenter a jeho view do seznamu mobilních šablon. Také jim nastaví mobilní layout šablonu.
	 * @param string $presenterName jméno presenteru
	 * @param string $viewName jméno šablony
	 * @param string $templateName volitelné jméno souboru šablony
	 * @param string $layoutName volitelné jméno souboru šablony
	 */
	protected function addView($presenterName, $viewName, $templateName = NULL, $layoutName = NULL) {
		$this->addLayout($presenterName, $viewName, $layoutName);
		if (empty($templateName)) {
			$templateName = self::DEFAULT_VIEW_PREFIX . Strings::firstUpper($viewName);
		}
		$this->views->offsetSet($presenterName . "/" . $viewName, $this->getViewRepresentation($presenterName, $viewName, NULL, $templateName));
	}

	/**
	 *  Přidá presenter a jeho view do seznamu mobilních layoutů.
	 * @param string $presenterName jméno presenteru
	 * @param string $viewName jméno šablony
	 * @param string $layoutName volitelné jméno souboru layoutu
	 */
	protected function addLayout($presenterName, $viewName, $layoutName = NULL) {
		if (empty($layoutName)) {
			$layoutName = self::DEFAULT_LAYOUT_FILENAME;
		}
		$this->layouts->offsetSet($presenterName . "/" . $viewName, $this->getViewRepresentation($presenterName, $viewName, $layoutName, NULL));
	}

	/**
	 * Vrátí objekt reprezentující dvojici presenter a view. Také uchovává název jejich souboru se šablonou a layoutem.
	 * @param string $presenter jméno presenteru
	 * @param string $view jméno viewu
	 * @param string $layoutName jméno souboru pro layout
	 * @param string $templateName jméno souboru pro šablonu
	 * @return ArrayHash reprezentace této dvojice
	 */
	private function getViewRepresentation($presenter, $view, $layoutName, $templateName) {
		$representation = new ArrayHash();
		$representation->presenter = $presenter;
		$representation->view = $presenter;
		if (!empty($layoutName)) {
			$representation->layoutName = $layoutName;
		}
		if (!empty($templateName)) {
			$representation->viewName = $templateName;
		}
		return $representation;
	}

}
