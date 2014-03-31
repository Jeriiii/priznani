<?php

/**
 * Base Presenter
 *
 * Základní třída pro všechny presentery nastavující společné části.
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */
use Nette\Application\UI\Form as Frm,
	\Navigation\Navigation,
        \Nette\Utils\Strings,
	Nette\Http\Url,
	Nette\Http\Request;

abstract class BasePresenter extends Nette\Application\UI\Presenter {
	
	public $parameters;
	public $domain;
	public $partystyle;

	/* modes */
	public $partymode = FALSE;
	public $sexmode = FALSE;
	public $advicemode = FALSE;
	public $datemode = FALSE;
	
	/* proměnné pro css překlad */
	protected $cssVariables = array();

	public function startup() {
		if ($this->name == "Homepage") {
			$page = $this->context->createPages()
					->order("order ASC")
					->fetch();
			if ($page->id_view)
				$this->redirect($page->presenter . ":" . $page->view, $page->url);
			else
				$this->redirect($page->presenter . ":" . $page->view);
		}
		AntispamControl::register();
		parent::startup();
	}

	public function beforeRender() {
		if ($this->name == "Competition") {
			$this->template->fbToDatabase = FALSE;
			if ($this->action == "default") {
				$this->template->existForm = FALSE;
			} else {
				$this->template->existForm = TRUE;
			}
		} else {
			$this->template->existForm = TRUE;
			$this->template->fbToDatabase = TRUE;
			if ($this->name != "Sign") {
				//die("Stránky přiznáníosexu jsou dočasně mimo provoz.");
			}
		}
		$this->template->partymode = $this->partymode;
		if ($this->getUser()->isLoggedIn()) {
			$this->template->identity = $this->getUser()->getIdentity();
		}
//		$user = $this->getUser();
//		$authorizator = new MyAuthorizator;
		$parameters = $this->context->createAuthorizator_table()
				->fetch();
		$this->parameters = $parameters;
//		$httpRequest = $this->context->httpRequest;
//		$this->domain = $httpRequest
//							->getUrl()
//							->host;
//
//		if($this->domain == "priznanizparby" || $this->domain == "priznanizparby.cz" || $httpRequest->getQuery('url') == "priznanizparby")
//		{
//			$this->partystyle = TRUE;die();
//		}
//		else
//		{
//			$this->partystyle = FALSE;
//		}
//		$this->template->partystyle = $this->partystyle;

		$this->template->domain = $this->domain;

		$this->template->facebook_html = "";
		$this->template->facebook_script = "";

		$google = $this->context->createGoogle_analytics()
				->fetch();

		$name = "";

		if ($google)
			$name = $google->name;

		$this->template->google_analytics = "
				<script type='text/javascript'>
					var _gaq = _gaq || [];
					  _gaq.push(['_setAccount', '" . $name . "']);
					  _gaq.push(['_trackPageview']);

					  (function() {
						var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
						ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
						var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
					  })();
				</script>	
			";

		if ($parameters->map == 1) {
			$map = $this->context->createMap()
					->fetch();

			if (!empty($map)) {
				$this->template->gps = $map->gps;
				$this->template->name = $map->name;
				$this->template->text_map = $map->text;

				$this->template->map_head = '
					<script type="text/javascript" src="http://api4.mapy.cz/loader.js"></script>
					<script type="text/javascript">Loader.load();</script>
				';
			} else {
				$this->template->map_head = '';
			}
		} else {
			$this->template->map_script = "";
			$this->template->map_head = '';
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
         * User menu komponenta
         * @param type $name
         */
        protected function createComponentUserMenu($name) 
	{
		$nav = new Navigation($this, $name);
		$user = $this->getUser();
		$nav->setMenuTemplate(APP_DIR . '/components/Navigation/usermenu.phtml');
                $navigation = array();
                $currentUrl = $this->getName();
                $isModul = $this->isProfilModuleCurrent($currentUrl);
                
                //prihlaseny uzivatel
                if ($this->getUser()->isLoggedIn()) {                    
                    
                    // Je-li modul, poupravime odkazy
                    if($isModul){                                        
                        if ($user->isInRole('admin') || $user->isInRole('superadmin')){
                                     $navigation["Administrace"] = $this->link(":Admin:Admin:default");
                                     $navigation["Moje galerie"] = $this->link("Galleries:");
                                     $navigation["Přiznání"] = $this->link(":Page:");
                                     $navigation["Nastavení"] = $this->link("#");                                 
                                     $navigation["Odhlásit se"] = $this->link(":Sign:out");
                        } else {
                                if ($user->isInRole('user')){
                                     $navigation["Moje galerie"] = $this->link("Galleries:");
                                     $navigation["Přiznání"] = $this->link(":Page:");
                                     $navigation["Nastavení"] = $this->link("#");
                                     $navigation["Odhlásit se"] = $this->link(":Sign:out");
                                }
                        }
                        
                     //Neni-li modul, pak jsou odkazy klasicky   
                    } else {                                        
                        if ($user->isInRole('admin') || $user->isInRole('superadmin')){
                                     $navigation["Administrace"] = $this->link("Admin:Admin:default");
                                     $navigation["Moje galerie"] = $this->link("Profil:Galleries:");
                                     $navigation["Přiznání"] = $this->link("Page:");
                                     $navigation["Nastavení"] = $this->link("#");                                 
                                     $navigation["Odhlásit se"] = $this->link("Sign:out");
                        } else {
                                if ($user->isInRole('user')){
                                     $navigation["Moje galerie"] = $this->link("Profil:Galleries:");
                                     $navigation["Přiznání"] = $this->link("Page:");
                                     $navigation["Nastavení"] = $this->link("#");
                                     $navigation["Odhlásit se"] = $this->link("Sign:out");
                                }
                        }
                    }
                    
                //neprihlaseny uzivatel
                } else {
                    if($isModul){                    
                               $navigation["Přihlášení"] = $this->link(":Sign:in");
                               $navigation["Registrace"] = $this->link(":Sign:registration");
                    } else {
                               $navigation["Přihlášení"] = $this->link("Sign:in");
                               $navigation["Registrace"] = $this->link("Sign:registration");
                    }
                      
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
         * Metoda kontroluje, zda jsme v Profil modulu. Pouziva se cheme-li zmenit odkazy v navigaci
         * @param type $module
         * @return type
         */
        public function isProfilModuleCurrent($module)
        {
            $module = trim((string) $module);
            return Strings::startsWith($module, 'Profil');
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
		
		if(!empty($this->cssVariables)) {
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
	
	protected function getCssVariables() {
		return $this->cssVariables;
	}
	
	protected function addToCssVariables(array $css) {
		$this->cssVariables = $this->cssVariables + $css;
	}

//	protected function createComponentMenu($name) 
//	{
//		$nav = new Navigation($this, $name);
//		$pages = $this->context->createPages()
//				//->where("visibility_menu", 1)
//				->order("order ASC")
//				->where("visibility_menu", 1);
//		
//		$navigation = array();
//		
//		$presenter = $this->getPresenter();
//
//		if($presenter->name == "Page")
//		{
//			$url = new Url($this->context->httpRequest->getUrl());
//			$backlink = ($url->path);
//		}else{
//			$backlink = $this->link($this->backlink() );
//		}
//		
////		if ($this->parameters->news) $navigation["Aktuality"] = $this->link("Page:aktuality");
//		
//		foreach($pages as $page)
//		{
//			if($page->id_view)
//				$navigation[$page->name] = $this->link(
//					$page->presenter . ":" . $page->view , $page->url//"Page:default",$page->id
//				);
//			else
//				$navigation[$page->name] = $this->link(
//					$page->presenter . ":" . $page->view
//				);
//		}
//		foreach ($navigation as $name => $link) {
//			$article = $nav->add($name, $link);
//			if ($backlink == $link) {
//				$nav->setCurrentNode($article);
//			}
//			//die("back: " . $backlink . " link " . $link);
//		}
//		
//   	}
//	protected function createComponentMenu($name) 
//	{
//		$nav = new Navigation($this, $name);
//		$pages = $this->context->createTexts()
//				->where("visibility_menu", 1)
//				->order("order ASC");
//		
//		$navigation = array();
//		
//		$presenter = $this->getPresenter();
//		
//		if($presenter->name == "Page")
//		{
//			$id = $presenter->id_page;
//			$backlink = $this->link($this->backlink(), $id );
//		}else{
//			$backlink = $this->link($this->backlink() );
//		}
//		
//		if ($this->parameters->news) $navigation["Aktuality"] = $this->link("Page:aktuality");
//		
//		foreach($pages as $page)
//		{
//			$navigation[$page->name] = $this->link("Page:default",$page->id);
//		}
//		foreach ($navigation as $name => $link) {
//			$article = $nav->add($name, $link);
//			if ($backlink == $link) {
//				$nav->setCurrentNode($article);
//			}
//		}
//		
//   	}

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
		$this->redirect('this');
	}

}
