<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POS\Listeners\Services;

/**
 * ActivityReporter slouží jako služba pro DI container, která může informovat listenery o aktivitě uživatele
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ActivityReporter extends \Nette\Object {
	/*
	 * Pro listenery při jakékoli aktivitě přihlášeného uživatele
	 */

	public $onUserActivity = array();

	/**
	 * Zavolá se při aktivitě uživatele
	 * @param type $user uživatel z presenteru
	 */
	public function handleUsersActivity($user) {
		$this->onUserActivity($user->id);
	}

}
