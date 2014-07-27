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

		$countData = $this->getCachedData();
		$nav = new Navigation($this, $name);
		$user = $this->getUser();
		$nav->setMenuTemplate(APP_DIR . '/components/Navigation/adminmenu.phtml');
		if ($user->isAllowed('galleries') && $user->isInRole('superadmin'))
			$navigation["GALERIE"] = $this->link("Galleries:galleries");
		if ($user->isAllowed('forms'))
			$navigation["FORMULÁŘ " . '<span class="badge badge-warning">' . $countData["confessionCount"] . '</span>'] = $this->link("Forms:forms");
		//if ($user->isAllowed('facebook')) $navigation["FACEBOOK"] = $this->link("Admin:facebook");
		//if ($user->isAllowed('files')) $navigation["SOUBORY"] = $this->link("Admin:files");
		//if ($user->isAllowed('map')) $navigation["MAPY"] = $this->link("Admin:map");
		if ($user->isAllowed('accounts'))
			$navigation["ÚČTY"] = $this->link("Admin:accounts");
		if ($user->isAllowed('accept_images'))
			$navigation["SCHVAL. FOTEK " . '<span class="badge badge-warning">' . $countData["imageCount"] . '</span>'] = $this->link("AcceptImages:");
		$navigation["MUJ ÚČET"] = $this->link("Admin:myAccounts");
		$navigation["PLATBY"] = $this->link("Payments:");
		$navigation["OBJEDNÁVKY"] = $this->link("GameOrders:");
		$navigation["UŽIV. ZPRÁVY " . '<span class="badge badge-warning">' . $countData["messageCount"] . '</span>'] = $this->link("Contacts:");
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
	 * Zkontroluje, jestli data v cachi exitují, pokudn e, uloží jea nastaví expiraci na 1 minutu,
	 * v pobou případech požadovaná data vrací
	 * @return array
	 */
	private function getCachedData() {
		if ($this->cache->load("menuCountData") === NULL) {
			$data = $this->cache->save("menuCountData", $this->getImageConfessionMessageCount(), array(
				Cache::EXPIRE => '1 minutes',
			));
		} else {
			$data = $this->cache->load("menuCountData");
		}
		return $data;
	}

}
