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

	/** @var int 1 = má se automaticky spustit průvodce, jinak 0 */
	private $intro;

	/** @var int Na jakém presenteru se dá položka zobrazit. */
	private $showOn;

	public function __construct($loggedUser, DaoBox $daoBox, $sessionManager, $parent, $name, $intro = 0, $showOn = Menu::SHOW_ALL_PRES) {
		parent::__construct($parent, $name);

		$this->loggedUser = $loggedUser;
		$this->daoBox = $daoBox;
		$this->sessionManager = $sessionManager;
		$this->intro = $intro;
		$this->showOn = $showOn;
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render($templateName = 'default.latte') {
		$this->template->setFile(dirname(__FILE__) . '/' . $templateName);
		$this->template->menu = $this->createMenu();

		$userId = $this->presenter->getUser()->id;
		$this->template->countFriendRequests = $this->daoBox->friendRequestDao->getAllToUser($userId)->count();
		$this->template->countSexy = $this->daoBox->youAreSexyDao->countToUser($userId);
		$this->template->render();
	}

	public function renderMobile() {
		$this->render('mobile.latte');
	}

	/**
	 * Vrátí nové menu.
	 * @return \POS\Ext\SimpleMenu\Menu
	 */
	private function createMenu() {
		/* v mobilní verzi se pak zobrazuje v celém layoutu */
		$menu = new Menu($this->presenter->user->isLoggedIn(), $this->showOn);

		$menu->addItem(new Item('Přihlášení', ':Sign:in'), false);
		$menu->addItem(new Item('Registrace', ':Sign:registration'), false);

		$menu->addItem(new Item('Hledat přátele', ':Search:Search:', 'add-friend', array(
			'data-step' => '4', 'data-position' => 'left', 'data-intro' =>
			"Najdi nové přátele. Jednoduše jim pošli žádost o přátelství.", 'data-position' => "right")
			)
		);

		$menu->addItem(new Item('Spustit průvodce', null, null, array(
			'id' => 'startIntroBtn', 'data-auto-start' => $this->intro, 'onclick' =>
			"window.scrollTo(0, 0);javascript:introJs().setOption('showProgress', true).start();"
			)), true, Menu::SHOW_ONE_PAGE
		);



		$menu->addGroup(new Group('Profil'), true);
		$menu->addItem(new Item('Editovat profil', ':Profil:Edit:default', 'edit'), true);
		$menu->addItem(new Item('Můj profil', ':Profil:Show:default', 'profile'), true);
		/* skrytí žádostí o ověření pro první verzi přiznání */
		//$menu->addItem(new Item('<span>' . $countVerReqs . '</span>Žádostí o ověření', ':Profil:Show:verification', 'with-counter'), true);

		$menu->addGroup(new Group('Obrázky'), true);
		$menu->addItem(new Item('Nahrát fotky', null, 'add-gallery', array('id' => 'show-photo-form')), true, Menu::SHOW_ONE_PAGE);
		$menu->addItem(new Item('Moje galerie', ':Profil:Galleries:default'), true);

		if (isset($this->loggedUser) && $this->loggedUser->propertyID) { /* ochránit odkazy, které by spadly bez user properties */
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
