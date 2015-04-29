<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Objekt který vybere výsledky podle preferencí uživatele
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POS\UserPreferences;

use POS\Model\UserDao;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Caching\Cache;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use POS\Model\UserCategoryDao;
use POS\Model\UserCategory;

class BaseUserPreferences {

	/** @var \POS\Model\UserDao */
	protected $userDao;

	/** @var \POS\Model\UserCategoryDao */
	protected $userCategoryDao;

	/** @var ActiveRow Vlastnosti přihlášeného uživatele. */
	protected $userProperty;

	/** @var ActiveRow Přihlášenéhý uživatel. */
	protected $user;

	/** @var array Nejlepší uživatelé pro tohoto uživatele */
	protected $bestUsers;

	/** @var Nette\Http\SessionSection Sečna do které se ukládá uživatelské vyhledávání */
	protected $section;

	/** @var Nette\Http\Session Obecná sečna. Pro práci se sečnou využijte připravenou section */
	protected $session;

	/** @var UserCategory Je opravdu private použijte getter */
	private $userCategory = NULL;

	const NAME_SESSION_BEST_U = "bestUsers";

	public function __construct($user, UserDao $userDao, UserCategoryDao $userCategoryDao, Session $session, $expirationTime = '45 min') {
		if (!($user instanceof ActiveRow) && !($user instanceof \Nette\ArrayHash)) {
			throw new Exception("variable user must be instance of ActiveRow or ArrayHash");
		}

		$this->userProperty = $user->property;
		$this->user = $user;
		$this->userDao = $userDao;
		$this->userCategoryDao = $userCategoryDao;
		$this->bestUsers = NULL;
		$this->session = $session;

		$this->section = $session->getSection(self::NAME_SESSION_BEST_U);
		$this->section->setExpiration($expirationTime);
	}

	/**
	 * Přepočítá uživ. kategorie.
	 */
	protected function calculate() {
		if (empty($this->userCategory)) {
			$this->userCategory = new UserCategory($this->userProperty, $this->userCategoryDao, $this->session);
		}

		$this->userCategory->calculate();
	}

	/**
	 * Vrátí kategorie, o které se přihlášený uživatel zajímá.
	 * @param boolean $recalculate Přepočítá kategorie.
	 * @return array IDs kategorií.
	 */
	protected function getUserCategories($recalculate = FALSE) {
		if ($this->userCategory === NULL) {
			$this->userCategory = new UserCategory($this->userProperty, $this->userCategoryDao, $this->session);
		}
		$categoryIDs = $this->userCategory->getCategoryIDs($recalculate);
		return $categoryIDs;
	}

}
