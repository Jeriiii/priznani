<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\Database\Table\ActiveRow;
use POS\Model\UserCategoryDao;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use NetteExt\Serialize\Serializer;

/**
 * Vrátí kategorie uživatelů, který daný uživatel hledá.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UserCategory {

	/**
	 * @var \POS\Model\UserCategoryDao
	 * @inject
	 */
	public $userCategoryDao;

	/** @var ActiveRow Vlastnosti uživatele podle kterého se vyhledává. */
	public $userProperty;

	/** @var SessionSection */
	public $section;

	/** Název sekce pro vrácení kategorií */
	const SECTION_NAME = "userCategories";

	public function __construct($logedUserProperty, UserCategoryDao $userCategoryDao, Session $session) {
		if (!($logedUserProperty instanceof ActiveRow) && !($logedUserProperty instanceof \Nette\ArrayHash)) {
			throw new \Exception('variable $logedUserProperty must be instance of ActiveRow or ArrayHash');
		}
		$this->userCategoryDao = $userCategoryDao;
		$this->userProperty = $logedUserProperty;
		$this->section = $session->getSection(self::SECTION_NAME);
		$this->section->setExpiration("2 days");
	}

	/**
	 * Vrátí seznam IDček kategorií, které uživatel hledá.
	 * @param boolean $recalculate Přepočítá kategorie.
	 * @return array Seznam IDček kategorií.
	 */
	public function getCategoryIDs($recalculate = FALSE) {
		if ($this->section->categories === NULL || $recalculate) {
			$categories = $this->userCategoryDao->getMine($this->userProperty);
			$ser = new Serializer($categories);
			$this->section->categories = $ser->getIDs();
		}

		return $this->section->categories;
	}

	/**
	 * Přepočítá výsledky.
	 */
	public function calculate() {
		$this->section->categories = null;
	}

}
