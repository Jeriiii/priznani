<?php

<#assign licenseFirst = "/* ">
<#assign licensePrefix = " * ">
<#assign licenseLast = " */">
<#include "${project.licensePath}">

/**
 * Popis komponenty
 *
 * @author ${user}
 */
namespace POSComponent;

class ${name} extends BaseProjectControl {

	public function __construct($parent, $name) {
		parent::__construct($parent, $name);
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render() {
		$this->template->setFile(dirname(__FILE__) . '/default.latte');
		$this->template->render();
	}

}
