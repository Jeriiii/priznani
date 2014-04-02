<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Polly
 *
 * @author Mario
 */

namespace POSComponent\Galleries\UserGalleries;

class BaseUserGalleries extends \Nette\Application\UI\Control {

	/* proměnné pro css překlad */
	protected $cssVariables = array();

	public function renderBase($mode, $galleries, $templateName = "baseGallery.latte") {		
		$this->setCssParams();
		$this->template->userData = $this->getUserFromDB()
										->find($this->getUser()->id)
										->fetch();
		
		/* vrati pouze posledni vsechny nahledy galerie daneho uzivatele */
		if ($mode == "listAll") {
			$this->renderListAllImages($galleries, $templateName);
		}

		/* vrati pouze posledni 4 nahledy galerie daneho uzivatele */
		if ($mode == "listFew") {
			$this->renderListFewImages($galleries, $templateName);
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
	
	protected function getUserGalleries() {
		return $this->getPresenter()
					->context
					->createUsersGalleries();
	}
		
	protected function getUserFromDB() {
		return $this->getPresenter()
					->getContext()
					->createUsers();
	}
	
	protected function getUser() {
		return $this->getPresenter()
					->getUser();
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
 