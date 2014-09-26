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

class BaseUserPreferences {

	/** @var \POS\Model\UserDao */
	protected $userDao;

	/** @var \POS\Model\UserCategoryDao */
	protected $userCategoryDao;

	/** @var ActiveRow Vlastnosti přihlášeného uživatele. */
	protected $userProperty;

	/** @var array Nejlepší uživatelé pro tohoto uživatele */
	protected $bestUsers;

	/** @var Nette\Http\SessionSection Sečna do které se ukládá uživatelské vyhledávání */
	protected $section;

	/** @var Nette\Http\Session Obecná sečna. Pro práci se sečnou využijte připravenou section */
	protected $session;

	const NAME_SESSION_BEST_U = "bestUsers";

	public function __construct(ActiveRow $userProperty, UserDao $userDao, UserCategoryDao $userCategoryDao, Session $session) {
		$this->userProperty = $userProperty;
		$this->userDao = $userDao;
		$this->userCategoryDao = $userCategoryDao;
		$this->bestUsers = NULL;
		$this->session = $session;

		$this->section = $session->getSection(self::NAME_SESSION_BEST_U);
		$this->section->setExpiration("45 min");
	}

}
