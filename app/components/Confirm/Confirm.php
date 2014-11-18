<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */
/**
 * Vykreslí potvrzující okénko
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POSComponent;

class Confirm extends BaseProjectControl {

	public function __construct($parent, $name) {
		parent::__construct($parent, $name);
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render($id, $name, $link, $tittle, $message) {
		$this->template->id = $id;
		$this->template->name = $name;
		$this->template->link = $link;
		$this->template->tittle = $tittle;
		$this->template->message = $message;
		$this->renderTemplate(dirname(__FILE__) . '/confirm.latte');
	}

}
