<?php

/**
 * Základní komponenta pro vykreslení obrázků v galerii
 *
 * @author Mario
 */

namespace POSComponent\Galleries\UserImagesInGallery;

use POS\Model\UserDao;
use POSComponent\BaseProjectControl;

class BaseUserImagesInGallery extends BaseProjectControl {
	/* právě procházená galerie */

	protected $galleryID;
	/* proměnné pro css překlad */
	protected $cssVariables = array();
	/* obrázky, co se mají zobrazit */
	protected $images;

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	public function __construct($galleryID, $images, UserDao $userDao) {
		$this->galleryID = $galleryID;
		$this->images = $images;
		$this->userDao = $userDao;
	}

	public function renderBase($mode, $owner, $templateName = "baseUserImagesInGallery.latte") {
		$this->setCssParams();

		$this->template->galleryID = $this->galleryID;
		$this->template->userData = $this->userDao->find($owner);

		/* vrati pouze posledni vsechny nahledy galerie daneho uzivatele */
		if ($mode == "listAll") {
			$this->renderListAllImages($this->images, $templateName);
		}

		/* vrati pouze posledni 4 nahledy galerie daneho uzivatele */
		if ($mode == "listFew") {
			$this->renderListFewImages($this->images, $templateName);
		}

		$this->template->render();
	}

	/* vrati pouze posledni vsechny nahledy galerie daneho uzivatele */

	private function renderListAllImages($images, $templateName) {
		/* galerie, které se mají zobrazit */
		$this->template->images = $images;

		$this->template->setFile(dirname(__FILE__) . '/' . $templateName);
	}

	/* vrati pouze posledni 4 nahledy galerie daneho uzivatele */

	private function renderListFewImages($images, $templateName) {
		/* galerie, které se mají zobrazit */
		$this->template->images = $images->limit(3);

		$this->template->setFile(dirname(__FILE__) . '/' . $templateName);
	}

	public function setCssParams() {
		$this->addToCssVariables(array(
			"img-height" => "200px",
			"img-width" => "200px",
			"text-padding-top" => "10px"
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

}

?>
