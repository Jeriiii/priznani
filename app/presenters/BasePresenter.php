<?php

/**
 * Base Presenter
 *
 * Základní třída pro všechny presentery nastavující společné části.
 *
 * @author	Petr Kukrál
 */
use Nette\Application\UI\Form as Frm,
	\Navigation\Navigation,
	\Nette\Utils\Strings,
	Nette\Http\Url,
	Nette\Http\Request;
use Nette\Security\User;
use POS\Ajax\ExampleHandle,
	POS\Ajax\AjaxCrate,
	POS\Ajax\ChatConversationsHandle;
use POS\Ajax\ActivitesHandle;
use POSComponent\Payment;
use NetteExt\Serialize\Serializer;
use NetteExt\Serialize\Relation;
use POS\Model\PaymentDao;
use Nette\Http\SessionSection;

abstract class BasePresenter extends BaseProjectPresenter {

	public $parameters;
	public $domain;
	public $partystyle;

	/**
	 * Proměnná s uživatelskými daty (cachovaný řádek z tabulky users). Obsahuje relace na profilFoto, gallery, property
	 * @var ArrayHash|ActiveRow řádek z tabulky users
	 */
	protected $loggedUser;

	/* modes */
	public $partymode = FALSE;
	public $sexmode = FALSE;
	public $advicemode = FALSE;
	public $datemode = FALSE;

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

	public function startup() {
		AntispamControl::register();
		parent::startup();
		if ($this->getUser()->isLoggedIn()) {
			$this->activityReporter->handleUsersActivity($this->getUser());
			$section = $this->getSectionLoggedUser();
			if (empty($section->loggedUser)) {
				$this->calculateLoggedUser();
			}
			$this->loggedUser = $section->loggedUser;
			$this->userDao->setActive($this->loggedUser->id);
		}

		$this->viewedActivity();
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
	 * @return Nette\Http\Session|Nette\Http\SessionSection
	 */
	protected function getSectionLoggedUser() {
		$sectionLoggedUser = $this->getSession('loggedUser');
		return $sectionLoggedUser;
	}

	/**
	 * Uloží do sečny aktuální data o přihlášeném uživateli.
	 */
	public function calculateLoggedUser() {
		$user = $this->userDao->getUser($this->getUser()->getId());
		$section = $this->getSectionLoggedUser();
		$section->setExpiration('20 minutes');

		$relProfilPhoto = new Relation("profilFoto");
		$relGallery = new Relation("gallery");
		$relProperty = new Relation("property");
		$relCouple = new Relation("couple");
		$relProfilPhoto->addRel($relGallery);

		$ser = new Serializer($user);
		$ser->addRel($relProfilPhoto);
		$ser->addRel($relProperty);
		$ser->addRel($relCouple);

		$sel = (array) $ser->toArrayHash();
		/* vytazeni jen jednoho radku */
		$userRow = array_shift($sel);
		$section->loggedUser = $userRow;
	}

	public function beforeRender() {
		$this->template->partymode = $this->partymode;
		if ($this->getUser()->isLoggedIn()) {
			$this->template->identity = $this->getUser()->getIdentity();
		}

		$this->template->domain = $this->domain;

		$this->template->facebook_html = "";
		$this->template->facebook_script = "";
		$this->template->ajaxObserverLink = $this->link('ajaxRefresh!'); //odkaz pro ajaxObserver na pravidelne pozadavky

		$this->fillJsVariablesWithLinks();
	}

	/**
	 * Zkontroluje, zda je uživatel přihlášen. Pokud ne, přesměruje ho na přihlášení.
	 */
	protected function checkLoggedIn() {
		$user = $this->getUser();
		if (!$user->isLoggedIn()) {
			if ($user->getLogoutReason() === User::INACTIVITY) {
				$this->flashMessage('Uplynula doba neaktivity! Systém vás z bezpečnostních důvodů odhlásil.', 'warning');
			} else {
				$this->flashMessage('Nejdříve se musíte přihlásit');
			}
			$backlink = $this->backlink();
			$httpRequest = $this->context->getByType('Nette\Http\Request');
			$backquery = $httpRequest->getQuery();
			$backlinkSession = $this->getSession('backlink');
			$backlinkSession->link = $backlink;
			$backlinkSession->query = $backquery;
			$this->redirect(':Sign:in', array('backlink' => TRUE));
		} else { //kontrola opravnění pro vztup do příslušné sekce
			if (!$user->isAllowed($this->name, $this->action)) {
				$this->flashMessage('Nejdříve se musíte přihlásit.', 'warning');
				$this->redirect(':Homepage:');
			}
		}
	}

	protected function createComponentOrders($name) {
		$nav = new Navigation($this, $name);
		$filters = array(
			"nejaktivnější" => "active",
			"nejnovější" => "news",
			"nejoblíbenější" => "likes",
			"nejkomentovanější" => "comments"
		);

		foreach ($filters as $name => $link) {
			$param = array("order" => $link);
			$article = $nav->add($name, $this->link("this", $param));
			if (array_key_exists("order", $_GET)) {
				if ($_GET["order"] == $link) {
					$nav->setCurrentNode($article);
				}
			} else {
				if ($link == "active") {
					$nav->setCurrentNode($article);
				}
			}
		}
	}

	protected function createComponentTopMenu($name) {
		$nav = new Navigation($this, $name);
		$filters = array(
			"nejaktivnější" => "active",
			"nejnovější" => "news",
			"nejoblíbenější" => "likes",
			"nejkomentovanější" => "comments"
		);

		foreach ($filters as $name => $link) {
			$param = array("order" => $link);
			$article = $nav->add($name, $this->link("this", $param));
			if (array_key_exists("order", $_GET)) {
				if ($_GET["order"] == $link) {
					$nav->setCurrentNode($article);
				}
			} else {
				if ($link == "active") {
					$nav->setCurrentNode($article);
				}
			}
		}
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
//prihlaseny uzivatel
			if ($user->isInRole('admin') || $user->isInRole('superadmin')) {
				$navigation["Administrace"] = $this->link(":Admin:Admin:default");
			}

			$navigation["Editovat profil"] = $this->link(":Profil:Edit:");
			$navigation["Hledat uživatele"] = $this->link(":Search:Search:");
			$navigation["Moje galerie"] = $this->link(":Profil:Galleries:");
			$navigation["Odhlásit se"] = $this->link(":Sign:out");
		} else {
//neprihlaseny uzivatel
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

	/**
	 * nastaví mód dle url
	 */
	protected function setMode($url = NULL) {
		if (empty($url))
			$url = $this->url;

		if ($this->url == "priznanizparby") {
			$this->partymode = TRUE;
		} elseif ($this->url == "poradna-o-sexu") {
			$this->advicemode = TRUE;
		} elseif ($this->url == "seznamka") {
			$this->datemode = TRUE;
		} else {
			$this->sexmode = TRUE;
		}

		$this->setTemplateMode($this->template);
	}

	/**
	 * nastaví mód template
	 */
	protected function setTemplateMode($template) {
		$template->partymode = $this->partymode;
		$template->sexmode = $this->sexmode;
		$template->advicemode = $this->advicemode;
		$template->datemode = $this->datemode;
	}

	protected function setPartyMode() {
		$this->partymode = TRUE;
		$this->setTemplateMode($this->template);
	}

	protected function setSexMode() {
		$this->sexmode = TRUE;
		$this->setTemplateMode($this->template);
	}

	protected function setAdviceMode() {
		$this->advicemode = TRUE;
		$this->setTemplateMode($this->template);
	}

	protected function setDateMode() {
		$this->datemode = TRUE;
		$this->setTemplateMode($this->template);
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

// nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu
		return new \WebLoader\Nette\CssLoader($compiler, $this->template->basePath . '/cache/css');
	}

	public function createComponentCssLayout() {
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
			'layout/layout.less',
			'mobile/responsive-menu.less',
			'chat/jquery.ui.chatbox.css',
			'chat/default.less',
			'chat/jquery-ui.less',
			'form.css',
			'variables.less'
		));

// nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu
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

// nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu
		return new \WebLoader\Nette\CssLoader($compiler, $this->template->basePath . '/cache/css');
	}

	public function createComponentJs() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js');
		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');

		$compiler->addFilter(function ($code) {
			$packer = new JavaScriptPacker($code, "None");
			return $packer->pack();
		});

// nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	public function createComponentJsLayout() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js/layout');
		$files->addFiles(array(
			'../cookies.js',
			'iedebug.js',
			'baseAjax.js',
			/* 'order.js', */
			'fbBase.js',
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

	public function createComponentJsLayoutLoggedIn() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js');
		$files->addFiles(array(
			'chat/core.js',
			'chat/init.js',
			'chat/jquery.ui.chatbox/jquery.ui.chatbox.js',
			'chat/jquery.ui.chatbox/chatboxManager.js',
			'chat/toogleContacts.js',
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

	public function createComponentFbLikeAndCommentToDatabase() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js/layout');
		$files->addFiles(array('fbLikeAndCommentToDatabase.js'));
		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');

		if (!empty($this->jsVariables)) {
			$varFilter = new WebLoader\Filter\VariablesFilter($this->jsVariables);
			$compiler->addFileFilter($varFilter);
		}
		$compiler->addFilter(function ($code) {
			$packer = new JavaScriptPacker($code, "None");
			return $packer->pack();
		});

		// nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	/**
	 * Funkce naplni potrebne odkazy do jsVariables, kterou nasledne pouziva WebLoader
	 */
	private function fillJsVariablesWithLinks() {
		$linkIncLike = $this->link('incLike!');
		$linkDecLike = $this->link('decLike!');
		$linkIncComment = $this->link('incComment!');
		$linkDecComment = $this->link('decComment!');

		$this->addToJsVariables(array(
			"inc-like" => $linkIncLike,
			"dec-like" => $linkDecLike,
			"inc-comment" => $linkIncComment,
			"dec-comment" => $linkDecComment
		));
	}

	protected function getCssVariables() {
		return $this->cssVariables;
	}

	protected function addToCssVariables(array $css) {
		$this->cssVariables = $this->cssVariables + $css;
	}

	protected function getJsVariables() {
		return $this->jsVariables;
	}

	protected function addToJsVariables(array $js) {
		$this->jsVariables = $this->jsVariables + $js;
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
		return new Frm\signInForm($this->backlink, $this, $name);
	}

	protected function createComponentPayment($name) {
		return new Payment($this, $name);
	}

}
