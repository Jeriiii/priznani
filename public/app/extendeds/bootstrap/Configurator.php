<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 9.7.2015
 */

namespace POS\Ext;

use Kdyby\Events\DI\EventsExtension;

/**
 * Konfigurátor specifický pro datenode
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Configurator extends \Nette\Configurator {

	/**
	 * Přidá kdyby events, pokud se nenacházíte na instalační stránce.
	 * Pokud jste na produkci, zapne je vždycky.
	 */
	public function addEvents($productionMode) {
		if (strpos($_SERVER["REQUEST_URI"], 'install') !== false) {/* zjištění, jestli url obsahuje řetězec install */
			$installUrl = TRUE;
		} else {
			$installUrl = FALSE;
		}

		if ($productionMode || !$installUrl) {/* zapnutí eventů v závislosti na prostředí */
			EventsExtension::register($this);
		}
	}

	/**
	 * Přidá do kontajnetu testovací config, pokud je spuštěný testovací mód.
	 * @param string $bootstrapPath Cesta k souboru bootstrap.php
	 * @return TRUE = Je spušťen testovací mód, jinak FALSE.
	 */
	public function addTestConfig($bootstrapPath) {
		//pokud se automaticky testuje
		$testing = (isset($_SERVER['TESTING']) && $_SERVER['TESTING']) ||
			(isset($_SERVER['HTTP_X_TESTING']) && $_SERVER['HTTP_X_TESTING']);

		if ($testing) {
			$this->addConfig($bootstrapPath . '/config/test.config.neon', FALSE);
		}

		return $testing;
	}

}
