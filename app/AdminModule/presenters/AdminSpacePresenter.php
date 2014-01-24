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

class AdminSpacePresenter extends  \Nette\Application\UI\Presenter
{

	public $id_page;
	public $id_gallery;
	public $id_file;
	public $text;

	public function startup()
	{
		parent::startup();
		//přesměrování s backlinkem pro případ, že uživatel není přihlášen
		if (!$this->user->isLoggedIn()) {
			if ($this->user->getLogoutReason() === User::INACTIVITY) {
				$this->flashMessage('Uplynula doba neaktivity! Systém vás z bezpečnostních důvodů odhlásil.', 'warning');
			}
			$backlink = $this->getApplication()->storeRequest();
			$this->redirect(':Sign:in', array('backlink' => $backlink));
		} else { //kontrola opravnění pro vztup do příslušné sekce
			if (!$this->user->isAllowed($this->name, $this->action)) {
				$this->flashMessage('Na vstup do této sekce nemáte dostatečná oprávnění!', 'warning');
				$this->redirect(':Homepage:');
			}
		}
		$this->setLayout('adminLayout');
	}
	
	protected function createComponentAdminMenu($name) 
	{
		$nav = new Navigation($this, $name);
		$user = $this->getUser();
		$nav->setMenuTemplate(APP_DIR . '/components/Navigation/adminmenu.phtml');
		if($user->isInRole("estateAgent") || $user->isInRole("realEstate"))
			$navigation["PLÁN"] = $this->link("EstateAgent:");
		else 
		{
			// $navigation["STRÁNKY"] = $this->link("Pages:pagesSort");
			if ($user->isAllowed('galleries') && $user->isInRole('superadmin')) $navigation["GALERIE"] = $this->link("Galleries:galleries");
			if ($user->isAllowed('forms')) $navigation["FORMULÁŘ"] = $this->link("Forms:forms");
			//if ($user->isAllowed('facebook')) $navigation["FACEBOOK"] = $this->link("Admin:facebook");
			//if ($user->isAllowed('files')) $navigation["SOUBORY"] = $this->link("Admin:files");
			if ($user->isAllowed('google_analytics')) $navigation["NÁVŠTĚVNOST"] = $this->link("Admin:analytics");
			//if ($user->isAllowed('map')) $navigation["MAPY"] = $this->link("Admin:map");
			if ($user->isAllowed('accounts')) $navigation["ÚČTY"] = $this->link("Admin:accounts");
			$navigation["MUJ ÚČET"] = $this->link("Admin:myAccounts");
			//if ($user->isAllowed('news')) $navigation["AKTUALITY"] = $this->link("AdminNews:");
			if ($user->isAllowed('authorizator')) $navigation["SUPER NASTAVENÍ"] = $this->link("Admin:authorizator");
		}

		$backlink = $this->link($this->backlink());
		foreach ($navigation as $name => $link) {
			$article = $nav->add($name, $link);
			if ($backlink == $link) {
				$nav->setCurrentNode($article);
			}
		}
   	}
	
	public function beforeRender()
	{
		$user = $this->getUser();
		$allow = $this->getSession('allow');
		
		if(!isset($allow->galleries)){
			$allow->setExpiration(0);
			
			$parametrs = $this->context->createAuthorizator_table()
				->fetch();
			
			$allow->galleries = $parametrs->galleries == 1 ? TRUE : FALSE;
			$allow->forms = $parametrs->forms == 1 ? TRUE : FALSE;
			$allow->accounts = $parametrs->accounts == 1 ? TRUE : FALSE;
			$allow->facebook = $parametrs->facebook == 1 ? TRUE : FALSE;
			$allow->files = $parametrs->files == 1 ? TRUE : FALSE;
			$allow->map = $parametrs->map == 1 ? TRUE : FALSE;
			$allow->google_analytics = $parametrs->google_analytics == 1 ? TRUE : FALSE;
			$allow->news = $parametrs->news == 1 ? TRUE : FALSE;
		}
		
		$authorizator = new \MyAuthorizator;
		$authorizator->setParametrs(
			$allow->galleries,
			$allow->forms,
			$allow->accounts,
			$allow->facebook,
			$allow->files,
			$allow->map,
			$allow->google_analytics,
			$allow->news
		);
		
		$user->setAuthorizator($authorizator);
	}
	
	public function increaseAdminScore($value)
	{
		$id_admin = $this->getUser()->id;
		$old_score = $this->context->createUsers()
						->find($id_admin)
						->fetch()
						->admin_score;
		$this->context->createUsers()
						->find($id_admin)
						->update(array(
							"admin_score" => $old_score + $value
						));
	}
	
	public function handleSignOut()
	{
		$this->getSession('allow')->remove();
		$this->getUser()->logout();
		$this->redirect('this');
	}
}
