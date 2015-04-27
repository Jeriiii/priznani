<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt;

use NetteExt\TemplateManager\IViewList;

/**
 * Třída detekující prostředí, v němž aplikace běží, včetně toho, zda je na to aplikace připravená
 * Jde o abstrakci nad TemplateManagerem a mobile detectorem
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class EnvironmentDetector extends \Nette\Object {

	/** @var DeviceDetector */
	private $detector;

	/** @var IViewList
	 *  objekt s nastavením, které šablony se mají použít
	 */
	private $mvl;

	/**
	 * Aktuální presenter - název.
	 * @var String
	 */
	private $presenterName;

	/**
	 * Aktuální presenter - šablona. - pozor, do této třídy se musí dostat před změnou
	 * @var String
	 */
	private $view;

	public function __construct($name, $view, DeviceDetector $detector, IViewList $mvl) {
		$this->presenterName = $name;
		$this->view = $view;
		$this->detector = $detector;
		$this->mvl = $mvl;
	}

	/**
	 * Zjistí, zda pro aktuální zobrazení existuje speciální (např. mobilní) šablona
	 * @return type
	 */
	public function hasSpecialTemplate() {
		return $this->mvl->hasLayout($this->presenterName, $this->view) || $this->mvl->hasView($this->presenterName, $this->view);
	}

	/**
	 * Rozhodne, zda je zařízení mobilní telefon A zda pro něj aktuálně existuje mobilní verze
	 * @return boolean
	 */
	public function isMobile() {
		return $this->detector->isMobile() && $this->hasSpecialTemplate();
	}

}
