<?php

use Nette\Application\UI\Form as Frm;

/**
 * TempPresenter Description
 */
class ContactPresenter extends BasePresenter {

	/**
	 * @var \POS\Model\ContactDao
	 * @inject
	 */
	public $contactDao;

	/*
	 * Vytvoří komponentu pro formulář
	 */

	protected function createComponentContactForm($name) {
		return new Frm\contactForm($this->contactDao, $this, $name);
	}

}
