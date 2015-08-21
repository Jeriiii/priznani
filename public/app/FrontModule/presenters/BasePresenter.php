<?php

/**
 * Base Presenter
 *
 * Základní třída pro všechny presentery nastavující společné části.
 *
 * @author	Petr Kukrál
 */
use Nette\Application\UI\Form as Frm;
use \Navigation\Navigation;
use POS\Ajax\AjaxCrate;
use POS\Ajax\ChatConversationsHandle;
use POS\Ajax\ActivitesHandle;
use POSComponent\Payment;
use NetteExt\Session\SessionManager;
use NetteExt\Session\UserSession;
use POS\Ext\SimpleMenu\LeftMenu;
use POS\Ext\SimpleMenu\Menu;

abstract class BasePresenter extends BaseProjectPresenter {

	public $parameters;
	public $domain;
	public $partystyle;

	/** @var array Less soubory, co se mají zpracovat */
	private $lessFiles = array();

	/**
	 * Proměnná s uživatelskými daty (cachovaný řádek z tabulky users). Obsahuje relace na profilFoto, gallery, property
	 * @var ArrayHash|ActiveRow řádek z tabulky users
	 */
	protected $loggedUser = null;

	/** @var array proměnné pro css překlad */
	protected $cssVariables = array();

	/** @var array proměnné pro js překlad */
	protected $jsVariables = array();

	/** @var \POS\Model\ActivitiesDao @inject */
	public $activitiesDao;

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var \POS\Chat\ChatManager @inject */
	public $chatManager;

	/** @var \POS\Ajax\AjaxObserver @inject */
	public $ajaxObserver;

	/** @var \POS\Listeners\Services\ActivityReporter @inject */
	public $activityReporter;

	/** @var \POS\Model\PaymentDao @inject */
	public $paymentDao;

	/** @var \POS\Model\FriendRequestDao @inject */
	public $friendRequestDao;

	/** @var \POS\Model\YouAreSexyDao @inject */
	public $youAreSexyDao;

	/** @var \POS\Model\FriendDao @inject */
	public $friendDao;

	/** @var \NetteExt\Session\SessionManager Stará se o správu dat v session. */
	private $sessionManager = null;

	/** @var \POS\Model\UserBlockedDao @inject */
	public $userBlockedDao;

	/** @var \POS\Model\userCategoryDao @inject */
	public $userCategoryDao;

	/** @var \POS\Model\StreamDao @inject */
	public $streamDao;

	/** @var int 1 = má se automaticky spustit průvodce (funguje pouze na onepage), jinak 0 */
	protected $intro = 0;

	public function startup() {
		AntispamControl::register();
		parent::startup();
		if ($this->getUser()->isLoggedIn()) {
			$this->activityReporter->handleUsersActivity($this->getUser());
			$section = UserSession::getSectionLoggedUser($this->session);
			if (empty($section->loggedUser)) {
				$this->calculateLoggedUser();
			}
			$this->loggedUser = $section->loggedUser;
			$this->userDao->setActive($this->loggedUser->id);
		}

		$this->viewedActivity();
	}

	/**
	 * Vrátí session manager, který se stará o session.
	 * @return SessionManager
	 */
	public function getSessionManager() {
		if ($this->sessionManager == null) {
			if (empty($this->loggedUser)) {
				$this->loggedUser = $this->userDao->find($this->user->id);
			}

			$this->sessionManager = new SessionManager($this->session, $this->loggedUser);
		}

		return $this->sessionManager;
	}

	/**
	 * Pokud se nachází v adrese activityViewedId, označí aktivitu jako přečtenou
	 */
	public function viewedActivity() {
		$httpRequest = $this->context->getByType('Nette\Http\Request');
		$activityViewedID = $httpRequest->getQuery('activityViewedId');
		if (!empty($activityViewedID)) {
			$this->activitiesDao->markViewed($activityViewedID);
		}
	}

	/**
	 * Uloží do sečny aktuální data o přihlášeném uživateli.
	 */
	public function calculateLoggedUser() {
		$sm = $this->getSessionManager();
		$sm->calculateLoggedUser($this->userDao);
	}

	public function beforeRender() {
		if ($this->getUser()->isLoggedIn()) {
			$this->template->identity = $this->getUser()->getIdentity();
		}

		$this->template->domain = $this->domain;
		$this->template->isNotComponentCssEmpty = !empty($this->lessFiles);

		$this->template->loggedUser = $this->loggedUser;
		$this->template->production = $this->productionMode;

		$this->template->facebook_html = "";
		$this->template->facebook_script = "";
		$this->template->ajaxObserverLink = $this->link('ajaxRefresh!'); //odkaz pro ajaxObserver na pravidelne pozadavky
	}

	/**
	 * Vytvoření komponenty pro chat
	 * @param String $name
	 * @return \POSComponent\Chat\PosChat
	 */
	protected function createComponentChat($name) {
		return new POSComponent\Chat\PosChat($this->chatManager, $this->loggedUser, $this, $name);
	}

	/**
	 * Hlavní navigace v pravé části lišty v layoutu
	 * @param type $name
	 */
	protected function createComponentUserMenu($name) {
		$nav = new Navigation($this, $name);
		$user = $this->getUser();
		$nav->setMenuTemplate(APP_DIR . '/components/Navigation/usermenu.phtml');
		$navigation = array();

		if ($this->getUser()->isLoggedIn()) {
			/* prihlaseny uzivatel */
			if ($user->isInRole('admin') || $user->isInRole('superadmin')) {
				$navigation["Administrace"] = $this->link(":Admin:Admin:default");
			}

			if (!$this->deviceDetector->isMobile()) {
				$navigation["Editovat profil"] = $this->link(":Profil:Edit:");
				$navigation["Hledat uživatele"] = $this->link(":Search:Search:");
				$navigation["Moje galerie"] = $this->link(":Profil:Galleries:");
			}
			$navigation["Odhlásit se"] = $this->link(":Sign:out");
		} else {
			/* neprihlaseny uzivatel */

			$navigation["Přihlášení"] = $this->link(":Sign:in");
			$navigation["Registrace"] = $this->link(":Sign:registration");
		}

		$backlink = $this->link($this->backlink());
		foreach ($navigation as $name => $link) {
			$article = $nav->add($name, $link);
			if ($backlink == $link) {
				$nav->setCurrentNode($article);
			}
		}
	}

	/**
	 * Vytovří komponentu pro aktivity
	 * @return \Activities Komponenta aktivit
	 */
	protected function createComponentActivities() {
		$activities = new Activities($this->activitiesDao, $this->loggedUser, $this->paymentDao);
		return $activities;
	}

	public function createComponentCss() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/css');
		$compiler = \WebLoader\Compiler::createCssCompiler($files, WWW_DIR . '/cache/css');

		if (!empty($this->cssVariables)) {
			$varFilter = new WebLoader\Filter\VariablesFilter($this->cssVariables);
			$compiler->addFileFilter($varFilter);
		}

		$compiler->addFileFilter(new \Webloader\Filter\LessFilter());
		$compiler->addFileFilter(function ($code, $compiler, $path) {
			return cssmin::minify($code);
		});

		/* nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu */
		return new \WebLoader\Nette\CssLoader($compiler, $this->template->basePath . '/cache/css');
	}

	public function createComponentCssComponents() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/css');
		$compiler = \WebLoader\Compiler::createCssCompiler($files, WWW_DIR . '/cache/css');
		$compiler->addFileFilter(new \Webloader\Filter\LessFilter());
		$compiler->addFileFilter(function ($code, $compiler, $path) {
			return cssmin::minify($code);
		});

		$files->addFiles($this->lessFiles);

		/* nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu */
		return new \WebLoader\Nette\CssLoader($compiler, $this->template->basePath . '/cache/css');
	}

	/**
	 * Přidá další less file k zpracování. Tyto soubory se vždy vykreslí.
	 * @param string $lessFile Relatvní cesta k less souboru ze složky css
	 */
	public function addLessFile($lessFile) {
		$this->lessFiles[] = $lessFile;
	}

	public function createComponentCssLayout() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/css');
		$compiler = \WebLoader\Compiler::createCssCompiler($files, WWW_DIR . '/cache/css');
		$compiler->addFileFilter(new \Webloader\Filter\LessFilter());
		$compiler->addFileFilter(function ($code, $compiler, $path) {
			return cssmin::minify($code);
		});

		$files->addFiles(array(
			'default.css',
			'layout/layout.less',
			'mobile/responsive-menu.less',
			'chat/jquery.ui.chatbox.css',
			'chat/default.less',
			'chat/jquery-ui.less',
			'form.css',
			'variables.less'
		));

		/* nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu */
		return new \WebLoader\Nette\CssLoader($compiler, $this->template->basePath . '/cache/css');
	}

	/**
	 * Vytvoření komponenty k mimifikaci stylů k mobilní verzi v jQueryMobile
	 * @return \WebLoader\Nette\CssLoader
	 */
	public function createComponentCssMobileLayout() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/css');
		$compiler = \WebLoader\Compiler::createCssCompiler($files, WWW_DIR . '/cache/css');

		if (!empty($this->cssVariables)) {
			$varFilter = new WebLoader\Filter\VariablesFilter($this->cssVariables);
			$compiler->addFileFilter($varFilter);
		}
		$compiler->addFileFilter(new \Webloader\Filter\LessFilter());
		$compiler->addFileFilter(function ($code, $compiler, $path) {
			return cssmin::minify($code);
		});

		$files->addFiles(array(
			'default.css',
			'mobile/layout.less',
			'jqueryMobile/posRedTheme/pos-mobile-theme.min.css',
			'jqueryMobile/posRedTheme/jquery.mobile.icons.min.css',
			'jqueryMobile/jquery.mobile.structure-1.4.5.min.css'
		));
		return new \WebLoader\Nette\CssLoader($compiler, $this->template->basePath . '/cache/css');
	}

	public function createComponentCssBoostrapModal() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/css');
		$compiler = \WebLoader\Compiler::createCssCompiler($files, WWW_DIR . '/cache/css');

		$compiler->addFileFilter(new \Webloader\Filter\LessFilter());
		$compiler->addFileFilter(function ($code, $compiler, $path) {
			return cssmin::minify($code);
		});

		$files->addFiles(array('bootstrap/helpNew/bootstrap.less'));

		/* nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu */
		return new \WebLoader\Nette\CssLoader($compiler, $this->template->basePath . '/cache/css');
	}

	public function createComponentJs() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js');
		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');

		$compiler->addFilter(function ($code) {
			$packer = new JavaScriptPacker($code, "None");
			return $packer->pack();
		});

		/* nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu */
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	public function createComponentJsLayout() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js/layout');
		$files->addFiles(array(
			'../cookies.js',
			'iedebug.js',
			'baseAjax.js',
			/* 'order.js', */
			/* 'fbBase.js', */
			/* 'leftMenu.js', */
			'../nette.ajax.js',
			'initAjax.js',
			'../mobile/responsive-menu.js',
			'../forms/netteForms.js',
			'../ajaxObserver/core.js',
			'../ajaxBox/ajaxBox.js',
			'../ajaxBox/ajaxbox-standard-init.js',
			'../ajaxBox/ajaxBox.otherFnc.js',
			'../features/jquery.slimscroll.js',
			'../ajaxBox/confirm/confirm.js',
			'../ajaxBox/popUp/init-simple-popUp.js'
		));

		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new JavaScriptPacker($code, "None");
			return $packer->pack();
		});

		/* nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu */
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	/**
	 * Vytvoření komponenty k mimifikaci skriptů k mobilní verzi v jQueryMobile
	 * @return \WebLoader\Nette\CssLoader
	 */
	public function createComponentJsMobileLayout() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js/layout');
		$files->addFiles(array(
			'baseAjax.js',
			'mobile.js',
			/* 'fbBase.js', */
			'../nette.ajax.js',
			'../jqueryMobile/mobile-init.js',
			'initAjax.js',
			'../ajaxObserver/core.js',
			'../jqueryMobile/jquery.mobile-1.4.5.min.js'
		));

		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new JavaScriptPacker($code, "None");
			return $packer->pack();
		});

		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	protected function createComponentLeftMenu($name, $showOn = Menu::SHOW_ALL_PRES) {
		$daoBox = new \NetteExt\DaoBox();

		$daoBox->friendRequestDao = $this->friendRequestDao;
		$daoBox->userDao = $this->userDao;
		$daoBox->streamDao = $this->streamDao;
		$daoBox->userCategoryDao = $this->userCategoryDao;
		$daoBox->friendDao = $this->friendDao;
		$daoBox->userBlockedDao = $this->userBlockedDao;
		$daoBox->youAreSexyDao = $this->youAreSexyDao;
		$daoBox->paymentDao = $this->paymentDao;

		return new LeftMenu($this->loggedUser, $daoBox, $this->getSessionManager(), $this, $name, $this->intro, $showOn);
	}

	/**
	 * Vytvoření komponenty k mimifikaci skriptů k mobilní verzi v jQueryMobile, když je uživatel přihlášený
	 * @return \WebLoader\Nette\CssLoader
	 */
	public function createComponentJsMobileLayoutLoggedIn() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js/layout');
		$files->addFiles(array(
			'mobileObserver.js'
		));

		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new JavaScriptPacker($code, "None");
			return $packer->pack();
		});

		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	public function createComponentJsLayoutLoggedIn() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js');
		$files->addFiles(array(
			'Autolinker.js',
			'chat/core.js',
			'chat/init.js',
			'chat/jquery.ui.chatbox/jquery.ui.chatbox.js',
			'chat/jquery.ui.chatbox/chatboxManager.js',
			'chat/toogleContacts.js',
			'chat/search/standard.js',
			'ajaxBox/ajaxbox-signed-in-init.js',
			'ajaxBox/activities/activities.js',
		));

		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new JavaScriptPacker($code, "None");
			return $packer->pack();
		});

// nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	protected function getCssVariables() {
		return $this->cssVariables;
	}

	protected function addToCssVariables(array $css) {
		$this->cssVariables = $this->cssVariables + $css;
	}

	protected function createComponentLoggedInMenu($name) {
		$nav = new Navigation($this, $name);
		if ($this->getUser()->isInRole("admin") || $this->getUser()->isInRole("superadmin"))
			$nav->add("ADMINISTRACE", $this->link(":Admin:Admin:"));
		$nav->add("ODHLÁŠENÍ", $this->link("signOut!"));
	}

	protected function createComponentLoggedOutMenu($name) {
		$nav = new Navigation($this, $name);
		$nav->add("REGISTRACE", $this->link("Sign:registration"));
		$nav->add("PŘIHLÁŠENÍ", $this->link("Sign:in"));
	}

	public function handleSignOut() {
		$this->getSession('allow')->remove();
		$this->getUser()->logout();
		$this->flashMessage("Byl jste úspěšně odhlášen");
		$this->redirect('this');
	}

	public function handlePanic() {
		$this->getSession('allow')->remove();
		$this->getUser()->logout();

		$this->redirectUrl("http://www.gmail.com");
	}

	/**
	 * Zpracování požadavku ajaxObserveru viz dokumentace "ajaxObserver"
	 */
	public function handleAjaxRefresh() {
		if ($this->isAjax()) {
			$handles = new AjaxCrate();

//$handles->addHandle('chat', new ExampleHandle()); //příklad
			$handles->addHandle('chatConversationWindow', new ChatConversationsHandle($this->chatManager, $this->getUser()->getId()));
			$handles->addHandle('activities-observer', new ActivitesHandle($this->activitiesDao, $this->getUser()->getId()));
			$this->ajaxObserver->sendRefreshRequests($this, $handles);
		}
	}

	/**
	 * Sign in form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm($name) {
		return new Frm\signInForm($this->backlink, $this->userDao, $this, $name);
	}

	protected function createComponentPayment($name) {
		return new Payment($this, $name);
	}

}
