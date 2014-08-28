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
use POS\Model\StreamDao;
use POS\Model\StreamCategoriesDao;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Caching\Cache;
use Nette\Http\Session;
use Nette\Http\SessionSection;

class BaseUserPreferences {

	/** @var \POS\Model\UserDao */
	protected $userDao;

	/** @var \POS\Model\StreamDao */
	protected $streamDao;

	/** @var \POS\Model\StreamCategoriesDao */
	protected $streamCategoriesDao;

	/** @var ActiveRow Vlastnosti přihlášeného uživatele. */
	protected $userProperty;

	/** @var array Nejlepší uživatelé pro tohoto uživatele */
	protected $bestUsers;

	/** @var array Nejlepší příspěvky pro tohoto uživatele */
	protected $bestStreamItems;

	/** @var Nette\Http\SessionSection Sečna do které se ukládá uživatelské vyhledávání */
	protected $section;

	/** @var Nette\Http\SessionSection Sečna do které se ukládá stav příspěvků na streamu */
	protected $streamSection;

	const NAME_SESSION_BEST_U = "bestUsers";
	const NAME_SESSION_BEST_STREAM_ITEMS = "bestStreamItems";

	public function __construct(ActiveRow $userProperty, UserDao $userDao, StreamDao $streamDao, StreamCategoriesDao $streamCategoriesDao, Session $session) {
		$this->userProperty = $userProperty;
		$this->userDao = $userDao;
		$this->streamDao = $streamDao;
		$this->streamCategoriesDao = $streamCategoriesDao;
		$this->bestUsers = NULL;
		$this->bestStreamItems = NULL;

		$this->section = $session->getSection(self::NAME_SESSION_BEST_U);
		$this->section->setExpiration("45 min");

		$this->streamSection = $session->getSection(self::NAME_SESSION_BEST_STREAM_ITEMS);
		$this->streamSection->setExpiration("45 min");
	}

}
