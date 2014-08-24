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

use POS\Model\UserPropertyDao;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Caching\Cache;

class BaseUserPreferences {

	/** @var \POS\Model\UserPropertyDao */
	protected $userPropertyDao;

	/** @var ActiveRow Přihlášený uživatel, podle kterého se vybírá */
	protected $user;

	/** @var ActiveRow Vlastnosti přihlášeného uživatele. */
	protected $userProperty;

	/** @var array Nejlepší uživatelé pro tohoto uživatele */
	protected $bestUsers;

	/** @var Nette\Caching\Cache Cache pro uložení výsledků. */
	protected $cache;

	const NAME_CACHE_USERS = "bestUsers";

	public function __construct(ActiveRow $user, UserPropertyDao $userPropertyDao) {
		$this->user = $user;
		$this->userPref = $user->property;
		$this->userPropertyDao = $userPropertyDao;
		$this->bestUsers = NULL;

		//umístění cache
		$storage = new \Nette\Caching\Storages\FileStorage('../temp');
		// vytvoření cache
		$this->cache = new Cache($storage);
	}

}
