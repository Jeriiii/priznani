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

	/**
	 * @var string Text tlačítka, když má být jiný než $tittle
	 */
	private $btnText = "";

	/**
	 * @var string Třída tlačítka, pokud ho chcete třeba nastylovat
	 */
	private $btnClass = "";

	/**
	 * @var string Třída potvryovac9ho tlačítka, pokud ho chcete třeba nastylovat
	 */
	private $confirmBtnClass = "";

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
		$this->template->btnText = $this->btnText;
		$this->template->btnClass = $this->btnClass;
		$this->template->confirmBtnClass = $this->confirmBtnClass;
		$this->template->btnSetText = $this->btnSetText;
		if ($this->getDeviceDetector()->isMobile()) {
			$this->renderTemplate(dirname(__FILE__) . '/confirmMobile.latte');
		} else {
			$this->renderTemplate(dirname(__FILE__) . '/confirm.latte');
		}
	}

	public function setTittle($tittle) {
		$this->tittle = $tittle;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	/**
	 * Pokud chcete, aby se text tlačítka lišil od tittle
	 */
	public function setBtnText($btnText) {
		$this->btnText = $btnText;
	}

	public function setConfirmBtnClass($confirmBtnClass) {
		$this->confirmBtnClass = $confirmBtnClass;
	}

	public function setBtnClass($btnClass) {
		$this->btnClass = $btnClass;
	}

}
