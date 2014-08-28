<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Uživatelské preference pro stream.
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */

namespace POS\UserPreferences;

use POS\Model\UserPropertyDao;
use Nette\Database\Table\ActiveRow;
use POS\Model\UserDao;
use POS\Model\StreamDao;
use POS\Model\StreamCategoriesDao;
use Nette\Http\Session;

/**
 * Vybere do streamu nejvhodnější data vzhledem k preferencím daného uživatele
 */
class StreamUserPreferences extends BaseUserPreferences implements IUserPreferences {

	/**
	 * Kolik dat vytvoří na začátku (počet řádků)
	 */
	const INIT_ITEMS_COUNT = 6;

	/**
	 * Název sekce v session, kterou používá pro ukládání
	 */
	const NAME_SESSION_BEST_STREAM_ITEMS = "bestStreamItems";

	/** @var array Nejlepší příspěvky pro tohoto uživatele */
	protected $bestStreamItems;

	/** @var Nette\Http\SessionSection Sečna do které se ukládá stav příspěvků na streamu */
	protected $streamSection;

	/** @var \POS\Model\StreamDao */
	protected $streamDao;

	/** @var \POS\Model\StreamCategoriesDao */
	protected $streamCategoriesDao;

	public function __construct(ActiveRow $userProperty, UserDao $userDao, StreamDao $streamDao, StreamCategoriesDao $streamCategoriesDao, Session $session) {
		parent::__construct($userProperty, $userDao, $session);
		$this->streamDao = $streamDao;
		$this->streamCategoriesDao = $streamCategoriesDao;

		$this->bestStreamItems = NULL;

		$this->streamSection = $session->getSection(self::NAME_SESSION_BEST_STREAM_ITEMS);
		$this->streamSection->setExpiration("45 min");
	}

	/**
	 * Přepočítá výsledky hledání uložené v session. Volá se i v případě,
	 * kdy je cache prázdná.
	 */
	public function calculate() {
		if (empty($this->streamSection->bestStreamItems)) {
			$this->initializeStreamItems();
		}
		$this->bestStreamItems = $this->streamSection->bestStreamItems;
	}

	/**
	 * Vrací nejvhodnější příspěvky na stream uživatele
	 * @return type
	 */
	public function getBestStreamItems() {
		$this->bestStreamItems = $this->streamSection->bestStreamItems;

		if ($this->bestStreamItems === NULL) {
			$this->calculate();
		}

		return $this->bestStreamItems;
	}

	private function initializeStreamItems() {
		$this->streamSection->bestStreamItems = $this->streamDao->getAllItemsWhatFits(array(
			$this->userProperty->offsetGet(UserPropertyDao::COLUMN_PREFERENCES_ID)
			), self::INIT_ITEMS_COUNT);
	}

}
