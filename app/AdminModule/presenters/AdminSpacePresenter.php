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
use Nette\Caching\Cache;

class AdminSpacePresenter extends \BaseProjectPresenter {

	public $id_page;
	public $id_gallery;
	public $id_file;
	public $text;

	/**
	 * @var \POS\Model\ConfessionDao
	 * @inject
	 */
	public $confessionDao;

	/**
	 * @var \POS\Model\UserImageDao
	 * @inject
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\ContactDao
	 * @inject
	 */
	public $contactDao;

	/**
	 * Storage pro cache
	 */
	private $storage;

	/**
	 * Cache
	 */
	private $cache;

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
		//umístění cache
		$this->storage = new \Nette\Caching\Storages\FileStorage('../temp');
		// vytvoření cache
		$this->cache = new Cache($this->storage);
		$this->setLayout('adminLayout');
	}

	protected function createComponentAdminMenu($name) {
		$nav = new Navigation($this, $name);
		$nav->setMenuTemplate(APP_DIR . '/components/Navigation/adminmenu.phtml');

		$items = $this->getAdminMenuItems();
		$this->setMenuItems($nav, $items);
	}

	/**
	 * Vrátí položky v navigaci.
	 * @return array Pole položek
	 */
	public function getAdminMenuItems() {
		$user = $this->getUser();
		$countData = $this->getCachedData();

		if ($user->isAllowed('galleries')) {
			$navigation["GALERIE"] = $this->link("Galleries:galleries");
		}
		if ($user->isAllowed('forms')) {
			$navigation["FORMULÁŘ " . $this->getCountLable($countData["confessionCount"])] = $this->link("Forms:forms");
		}
		//if ($user->isAllowed('files')) $navigation["SOUBORY"] = $this->link("Admin:files");
		if ($user->isAllowed('accounts')) {
			$navigation["ÚČTY"] = $this->link("Admin:accounts");
		}
		if ($user->isAllowed('accept_images')) {
			$navigation["SCHVAL. FOTEK " . $this->getCountLable($countData["imageCount"])] = $this->link("AcceptImages:");
		}
		$navigation["MUJ ÚČET"] = $this->link("Admin:myAccounts");
		$navigation["PLATBY"] = $this->link("Payments:");
		$navigation["OBJEDNÁVKY"] = $this->link("GameOrders:");
		$navigation["UŽIV. ZPRÁVY " . $this->getCountLable($countData["messageCount"])] = $this->link("Contacts:");

		return $navigation;
	}

	/**
	 * Nastaví položky v menu.
	 * @param \Navigation\Navigation $nav Navigace.
	 * @param array $items Položky v menu.
	 */
	public function setMenuItems($nav, $items) {
		$backlink = $this->link($this->backlink());
		foreach ($items as $name => $link) {
			$article = $nav->add($name, $link);
			if ($backlink == $link) {
				$nav->setCurrentNode($article);
			}
		}
	}

	/**
	 * Vrátí nové součty prvků v menu.
	 * @return mixed
	 */
	public function invalidateMenuData() {
		$data = $this->cache->save("menuCountData", $this->getImageConfessionMessageCount(), array(
			Cache::EXPIRE => '1 minutes',
		));

		return $data;
	}

	/**
	 * Vrátí HTML s počtem prvků
	 * @param int $count
	 * @return string
	 */
	private function getCountLable($count) {
		return '<span class="badge badge-warning">' . $count . '</span>';
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

	/**
	 * Získá počty nevyřízených fotek, přiznání a zpráv od uživatelů a vrátí pole
	 * @return array
	 */
	private function getImageConfessionMessageCount() {
		$data = array();
		$data["imageCount"] = $this->userImageDao->getUnapprovedCount();
		$data["confessionCount"] = $this->confessionDao->countUnprocessed();
		$data["messageCount"] = $this->contactDao->getUnviewedCount();

		// Prochází pole a upravuje v případě potřeby data na 99+
		foreach ($data as $key => $value) {
			if ($value > 99) {
				$data[$key] = "99+";
			}
		}
		return $data;
	}

	/**
	 * Zkontroluje, jestli data v cachi exitují, pokud ne, uloží je a nastaví expiraci na 1 minutu,
	 * v obou případech požadovaná data vrací
	 * @return array
	 */
	private function getCachedData() {
		if ($this->cache->load("menuCountData") === NULL) {
			$data = $this->invalidateMenuData();
		} else {
			$data = $this->cache->load("menuCountData");
		}
		return $data;
	}

}
