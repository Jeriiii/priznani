<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 24.7.2015
 */

namespace POS\Ext\SimpleMenu;

use NetteExt\DaoBox;
use POSComponent\UsersList\FriendRequestList;
use POSComponent\UsersList\FriendsList;
use POSComponent\UsersList\SexyList\MarkedFromOther;
use POSComponent\UsersList\BlokedUsersList;
use NetteExt\Session\SessionManager;

/**
 * Levé menu navigace na hlavní stránce a v mobilní verzi pak v layoutu
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class LeftMenu extends \POSComponent\BaseProjectControl {

	/** @var ArrayHash|ActiveRow Přihlášený uživatel. */
	private $loggedUser;

	/** @var DaoBox */
	private $daoBox;

	/** @var \NetteExt\Session\SessionManager */
	private $sessionManager;

	public function __construct($loggedUser, DaoBox $daoBox, $sessionManager, $parent, $name) {
		parent::__construct($parent, $name);

		$this->loggedUser = $loggedUser;
		$this->daoBox = $daoBox;
		$this->sessionManager = $sessionManager;
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render() {
		$this->template->setFile(dirname(__FILE__) . '/default.latte');
		$this->template->menu = $this->createMenu();

		$userId = $this->presenter->getUser()->id;
		$this->template->countFriendRequests = $this->daoBox->friendRequestDao->getAllToUser($userId)->count();
		$this->template->countSexy = $this->daoBox->youAreSexyDao->countToUser($userId);
		$this->template->render();
	}

	/**
	 * Vrátí nové menu.
	 * @return \POS\Ext\SimpleMenu\Menu
	 */
	private function createMenu() {
		/* v mobilní verzi se pak zobrazuje v celém layoutu */
		$menu = new Menu($this->presenter->user->isLoggedIn());

		$menu->addItem(new Item('Přihlášení', ':Sign:in'), false);
		$menu->addItem(new Item('Registrace', ':Sign:registration'), false);

		$menu->addItem(new Item('Hledat přátele', ':Search:Search:', 'add-friend'));

		$menu->addGroup(new Group('Profil'));
		$menu->addItem(new Item('Editovat profil', ':Profil:Edit:default', 'edit'), true);
		$menu->addItem(new Item('Můj profil', ':Profil:Show:default', 'profile'), true);
		/* skrytí žádostí o ověření pro první verzi přiznání */
		//$menu->addItem(new Item('<span>' . $countVerReqs . '</span>Žádostí o ověření', ':Profil:Show:verification', 'with-counter'), true);

		$menu->addGroup(new Group('Obrázky'));
		$menu->addItem(new Item('Nahrát fotky', null, 'add-gallery', array('id' => 'show-photo-form')), true);
		$menu->addItem(new Item('Moje galerie', ':Profil:Galleries:default'), true);

		if ($this->loggedUser->propertyID) { /* ochránit odkazy, které by spadly bez user properties */
			$menu->addGroup(new Group('Uživatelé'));
		}

		$menu->addGroup(new Group('Zábava'));
		$menu->addItem(new Item('Přiznání o sexu', 'OnePage: priznani => 1'), null);
		$menu->addItem(new Item('Soutěže', ':Competition:list'), null);
		$menu->addItem(new Item('Hry', ':Eshop:game'), null);
		$menu->addItem(new Item('Bonusy', ':Page:metro'), null);

		return $menu;
	}

	protected function createComponentFriendRequest($name) {
		$sessionManager = $this->sessionManager;
		$smDaoBox = new DaoBox();
		$smDaoBox->userDao = $this->daoBox->userDao;
		$smDaoBox->streamDao = $this->daoBox->streamDao;
		$smDaoBox->userCategoryDao = $this->daoBox->userCategoryDao;

		return new FriendRequestList($this->daoBox->friendRequestDao, $this->presenter->getUser()->id, $sessionManager, $smDaoBox, $this, $name);
	}

	protected function createComponentFriends($name) {
		return new FriendsList($this->daoBox->friendDao, $this->presenter->getUser()->id, $this, $name);
	}

	protected function createComponentBlokedUsers($name) {
		return new BlokedUsersList($this->daoBox->userBlockedDao, $this->presenter->getUser()->id, $this, $name);
	}

	protected function createComponentMarkedFromOther($name) {
		return new MarkedFromOther($this->daoBox->paymentDao, $this->daoBox->youAreSexyDao, $this->presenter->getUser()->id, $this, $name);
	}

}
