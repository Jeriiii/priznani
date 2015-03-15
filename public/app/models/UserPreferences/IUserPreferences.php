<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Rozhraní které musí každá Preference implementovat
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POS\UserPreferences;

interface IUserPreferences {

	/**
	 * Přepočítá výsledky hledání uložené v cache. Volá se i v případě,
	 * kdy je cache prázdná.
	 */
	public function calculate();
}
