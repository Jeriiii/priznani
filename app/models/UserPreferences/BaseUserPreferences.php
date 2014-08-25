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

class BaseUserPreferences {

	/** @var \POS\Model\UserDao */
	protected $userDao;

	/** @var ActiveRow Vlastnosti přihlášeného uživatele. */
	protected $userProperty;

	/** @var array Nejlepší uživatelé pro tohoto uživatele */
	protected $bestUsers;

	/** @var Nette\Http\SessionSection Sečna do které se ukládá uživatelské vyhledávání */
	protected $section;

	const NAME_SESSION_BEST_U = "bestUsers";

	public function __construct(ActiveRow $userProperty, UserDao $userDao, Session $session) {
		$this->userProperty = $userProperty;
		$this->userDao = $userDao;
		$this->bestUsers = NULL;

		$this->section = $session->getSection(self::NAME_SESSION_BEST_U);
		$this->section->setExpiration("45 min");
	}

}
