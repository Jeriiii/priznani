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

	/**
	 * @var boolean TRUE = odkaz se přegeneruje plinkem, FALSE = odkaz se nechá tak jak je.
	 */
	private $plink;

	/**
	 * @var boolean TRUE = do tlačítka bude přidán text z prom. $tittle, FALSE = tlačítko bude bez textu - např. pro obrázky.
	 */
	private $btnSetText;

	/**
	 * @var string Titulek tlačítka
	 */
	private $tittle;

	/**
	 * @var string Zpráva, která má uživatele vyzvat k akci např. "Opravdu chcete smazat obrázek?"
	 */
	private $message;

	/**
	 * @var string Zpráva, která má uživatele vyzvat k akci např. "Opravdu chcete smazat obrázek?"
	 */
	private $link;

	public function __construct($parent, $name, $btnSetText = TRUE, $plink = TRUE) {
		parent::__construct($parent, $name);
		$this->plink = $plink;
		$this->btnSetText = $btnSetText;
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render($id, $name, $link, $tittle = '', $message = '') {
		if (!empty($tittle)) {
			$this->tittle = $tittle;
		}
		if (!empty($message)) {
			$this->message = $message;
		}
		$this->template->id = $id;
		$this->template->name = $name;
		$this->template->link = $link;
		$this->template->tittle = $this->tittle;
		$this->template->message = $this->message;
		$this->template->plink = $this->plink;
		$this->template->btnSetText = $this->btnSetText;
		$this->renderTemplate(dirname(__FILE__) . '/confirm.latte');
	}

	public function setTittle($tittle) {
		$this->tittle = $tittle;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

}
