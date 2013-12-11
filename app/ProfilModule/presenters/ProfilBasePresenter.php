<?php

namespace ProfilModule;

/**
 * Base presenter for all profile application presenters.
 */

use Nette\Security\User;

class ProfilBasePresenter extends \BasePresenter//\Nette\Application\UI\Presenter
{

	public function startup() {
		parent::startup();
//		$user = $this->getUser();
//		
//		if (!$user->isLoggedIn()) {
//			if ($user->getLogoutReason() === User::INACTIVITY) {
//				$this->flashMessage('Uplynula doba neaktivity! Systém vás z bezpečnostních důvodů odhlásil.', 'warning');
//			}
//			$backlink = $this->getApplication()->storeRequest();
//			$this->redirect(':Sign:in', array('backlink' => $backlink));
//		} else { //kontrola opravnění pro vztup do příslušné sekce
//			if (!$user->isAllowed($this->name, $this->action)) {
//				$this->flashMessage('Nejdříve se musíte přihlásit.', 'warning');
//				$this->redirect(':Homepage:');
//			}
//		}
	}
	/*
	public function handleSignOut()
	{
		$this->getUser()->logout();
		$this->redirect('Sign:in');
	}*/
}
