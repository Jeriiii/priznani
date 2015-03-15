<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt\TemplateManager;

use Nette\Application\UI\Presenter;
use Nette\Templating\FileTemplate;

/**
 * Třída nastavující mobilní šablony ve specifických případech daných konfigurační třídou.
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class TemplateManager extends \Nette\Object {

	/**
	 * Nastaví presenteru šablony, pokud jsou v seznamu šablon
	 * @param \Nette\Application\UI\Presenter $presenter aktuální presenter
	 * @param IViewList $viewList objekt s nastavením, které šablony se mají použít
	 */
	public function setTemplates(Presenter $presenter, IViewList $viewList) {
		if ($viewList->hasView($presenter->name, $presenter->view)) {
			$this->setView($presenter, $viewList->getViewName($presenter->name, $presenter->view));
		}
		if ($viewList->hasLayout($presenter->name, $presenter->view)) {
			$this->setLayout($presenter, $viewList->getLayoutName($presenter->name, $presenter->view));
		}
	}

	/**
	 * Nastaví danému presenteru mobilní šablonu.
	 * @param \Nette\Application\UI\Presenter $presenter
	 * @param string název souboru šablony (bez přípony)
	 */
	private function setView($presenter, $viewFilename) {
		$presenter->setView($viewFilename);
	}

	/**
	 * Nastaví danému presenteru mobilní layout.
	 * @param \Nette\Application\UI\Presenter $presenter
	 * @param string název souboru layoutu (bez přípony)
	 */
	private function setLayout($presenter, $layoutFilename) {
		$presenter->setLayout($layoutFilename);
	}

}
