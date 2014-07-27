<?php

/**
 * Admin presenter.
 *
 * Obsluha administrační části systému.
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */

namespace AdminModule;

use \Nette\Security\User,
	\Navigation\Navigation;

class AdminSpacePresenter extends \BaseProjectPresenter {

	public $id_page;
	public $id_gallery;
	public $id_file;
	public $text;

	public function startup() {
		parent::startup();
		//přesměrování s backlinkem pro případ, že uživatel není přihlášen
		if (!$this->user->isLoggedIn()) {
			if ($this->user->getLogoutReason() === User::INACTIVITY) {
				$this->flashMessage('Uplynula doba neaktivity! Systém vás z bezpečnostních důvodů odhlásil.', 'warning');
			}

			$this->redirect(':Sign:in', array('backlink' => $this->backlink()));
		} else { //kontrola opravnění pro vztup do příslušné sekce
			if (!$this->user->isAllowed($this->name, $this->action)) {
				$this->flashMessage('Na vstup do této sekce nemáte dostatečná oprávnění!', 'warning');
				$this->redirect(':Homepage:');
			}
		}
		$this->setLayout('adminLayout');
	}

	protected function createComponentAdminMenu($name) {
		$nav = new Navigation($this, $name);
		$user = $this->getUser();
		$nav->setMenuTemplate(APP_DIR . '/components/Navigation/adminmenu.phtml');
		if ($user->isAllowed('galleries') && $user->isInRole('superadmin'))
			$navigation["GALERIE"] = $this->link("Galleries:galleries");
		if ($user->isAllowed('forms'))
			$navigation["FORMULÁŘ"] = $this->link("Forms:forms");
		//if ($user->isAllowed('facebook')) $navigation["FACEBOOK"] = $this->link("Admin:facebook");
		//if ($user->isAllowed('files')) $navigation["SOUBORY"] = $this->link("Admin:files");
		//if ($user->isAllowed('map')) $navigation["MAPY"] = $this->link("Admin:map");
		if ($user->isAllowed('accounts'))
			$navigation["ÚČTY"] = $this->link("Admin:accounts");
		if ($user->isAllowed('accept_images'))
			$navigation["SCHVAL. FOTEK"] = $this->link("AcceptImages:");
		$navigation["MUJ ÚČET"] = $this->link("Admin:myAccounts");
		$navigation["PLATBY"] = $this->link("Payments:");
		$navigation["OBJEDNÁVKY"] = $this->link("GameOrders:");
		$navigation["UŽIV. ZPRÁVY"] = $this->link("Contacts:");
		//if ($user->isAllowed('news')) $navigation["AKTUALITY"] = $this->link("AdminNews:");

		$backlink = $this->link($this->backlink());
		foreach ($navigation as $name => $link) {
			$article = $nav->add($name, $link);
			if ($backlink == $link) {
				$nav->setCurrentNode($article);
			}
		}
	}

	public function beforeRender() {
		$authorizator = new \Authorizator;
		$user = $this->getUser();
		$user->setAuthorizator($authorizator);
	}

	public function handleSignOut() {
		$this->getUser()->logout();
		$this->flashMessage("Byl jste úspěšně odhlášen");
		$this->redirect(':Sign:in');
	}

}
