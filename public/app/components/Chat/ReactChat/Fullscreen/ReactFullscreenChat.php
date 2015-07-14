<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat\React;

/**
 * Komponenta chatu napsaného v Reactu.
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ReactFullscreenChat extends ReactChat {

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/reactFullscreen.latte');
		$user = $this->getPresenter()->getUser();
		$template->logged = $user->isLoggedIn() && $user->getIdentity();
		$template->loggedUser = $this->loggedUser;
		$template->render();
	}

}
