<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Polly
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POSComponent\Galleries\UserGalleriesThumbnails;

use Nette\ComponentModel\IContainer;
use POS\Model\UserDao;
use POS\Model\UserGalleryDao;
use POS\Model\UserAllowedDao;
use POS\Model\FriendDao;
use POSComponent\BaseProjectControl;
use Nette\Http\SessionSection;

class BaseUserGalleriesThumbnails extends BaseProjectControl {
	/* proměnné pro css překlad */

	protected $cssVariables = array();

	/** @var \POS\Model\UserDao */
	public $userDao;

	/** @var \POS\Model\UserGalleryDao */
	public $userGalleryDao;

	/** @var \POS\Model\UserAllowedDao */
	public $userAllowedDao;

	/** @var \POS\Model\FriendDao */
	public $friendDao;

	/** @var \Nette\Http\SessionSection */
	private $section;

	public function __construct(UserDao $userDao, UserGalleryDao $userGalleryDao, UserAllowedDao $userAllowedDao, FriendDao $friendDao, SessionSection $section, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->userDao = $userDao;
		$this->userGalleryDao = $userGalleryDao;
		$this->userAllowedDao = $userAllowedDao;
		$this->friendDao = $friendDao;
		$this->section = $section;
	}

	public function renderBase($mode, $galleries, $ownerID, $templateName = "baseGallery.latte") {
		$this->setCssParams();
		$this->template->userData = $this->userDao->find($ownerID);
		$accessData = $this->getAccessData($ownerID, $galleries);
		$this->template->accessData = $accessData;

		/* vrati pouze posledni vsechny nahledy galerie daneho uzivatele */
		if ($mode == "listAll") {
			$this->renderListAllImages($galleries, $templateName);
			$this->template->listFew = FALSE;
		}

		/* vrati pouze posledni 4 nahledy galerie daneho uzivatele */
		if ($mode == "listFew") {
			$this->renderListFewImages($galleries, $templateName);
			$this->template->listFew = TRUE;
		}

		$this->template->render();
	}

	/* vrati pouze posledni vsechny nahledy galerie daneho uzivatele */

	private function renderListAllImages($galleries, $templateName) {
		/* galerie, které se mají zobrazit */
		$this->template->galleries = $galleries;

		$this->template->setFile(dirname(__FILE__) . '/' . $templateName);
	}

	/* vrati pouze posledni 4 nahledy galerie daneho uzivatele */

	private function renderListFewImages($galleries, $templateName) {
		/* galerie, které se mají zobrazit */
		$this->template->galleries = $galleries->limit(3);

		$this->template->setFile(dirname(__FILE__) . '/' . $templateName);
	}

	public function setCssParams() {
		$this->addToCssVariables(array(
			"img-height" => "200px",
			"img-width" => "200px",
			"text-padding-top" => "40%"
		));
	}

	protected function getCssVariables() {
		return $this->cssVariables;
	}

	protected function addToCssVariables(array $css) {
		$this->cssVariables = $this->cssVariables + $css;
	}

	protected function getUser() {
		return $this->getPresenter()->getUser();
	}

	public function createComponentCss() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/css');
		$compiler = \WebLoader\Compiler::createCssCompiler($files, WWW_DIR . '/cache/css');

		if (!empty($this->cssVariables)) {
			$varFilter = new \WebLoader\Filter\VariablesFilter($this->cssVariables);
			$compiler->addFileFilter($varFilter);
		}

		$compiler->addFileFilter(new \Webloader\Filter\LessFilter());
		$compiler->addFileFilter(function ($code, $compiler, $path) {
			return \cssmin::minify($code);
		});

// nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu
		return new \WebLoader\Nette\CssLoader($compiler, $this->template->basePath . '/cache/css');
	}

	/**
	 * získá informace o přistupech do galerií uživatele,
	 * přidá do session, pokud vní nejsou
	 * @param int $ownerID ID vlastníka galerie
	 * @param Nette\Database\Table\Selection $galleries
	 * @return array
	 */
	private function getAccessData($ownerID, $galleries) {
		$accessData = array();
		if (empty($this->section->galleriesAccess)) {
			return $accessData = $this->userGalleryDao->getGalleriesAccesInfo($this->presenter->user->id, $ownerID, $this->section);
		} else {
			foreach ($galleries as $item) {
				if (!array_key_exists($item->id, $this->section->galleriesAccess)) {
					$accessData = $this->userGalleryDao->getGalleriesAccesInfo($this->presenter->user->id, $ownerID, $this->section);
				} else {
					$accessData = $this->section->galleriesAccess;
				}
			}
			return $accessData;
		}
	}

}
