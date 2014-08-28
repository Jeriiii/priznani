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

class StreamUserPreferences extends BaseUserPreferences implements IUserPreferences {

	const INIT_ITEMS_COUNT = 6;

	/**
	 * Přepočítá výsledky hledání uložené v session. Volá se i v případě,
	 * kdy je cache prázdná.
	 */
	public function calculate() {
		if (empty($this->streamSection->myStreamItems)) {
			$this->initializeStreamItems();
		}
		$this->streamSection->myStreamItems;
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
		$this->streamSection->myStreamItems = $this->streamDao->getAllItemsWhatFits(array(
			$this->userProperty->offsetGet(UserPropertyDao::COLUMN_PREFERENCES_ID)
			), self::INIT_ITEMS_COUNT);
	}

}
