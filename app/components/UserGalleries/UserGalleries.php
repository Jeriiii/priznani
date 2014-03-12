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
use \Nette\Security\User, Nette\Utils\Strings;

class UserGalleries extends Nette\Application\UI\Control {

        
        private $user;
        /* proměnné pro css překlad */
	protected $cssVariables = array();

	public function __construct($user){	
            $this->user = $user;
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
	
	protected function getCssVariables() {
		return $this->cssVariables;
	}
	
	protected function addToCssVariables(array $css) {
		$this->cssVariables = $this->cssVariables + $css;
	}
        
        /* vrati pouze posledni 4 nahledy galerie daneho uzivatele */
	public function render($mode) {
    //        \Nette\Diagnostics\Debugger::Dump($ahoj);die();
 
                if($mode == "listAll"){
                                           //             ->where("id = ANY (SELECT MAX( id ) FROM `user_images` WHERE userID =87 GROUP BY galleryID)"                                                 /*$this->getPresenter()->getContext()->createUsersFoto()->max("id")->where("userId",$this->userId)->group("galleryID")*/

                    $this->template->galleries = $this->getUserDataFromDB()
                                                         ->where('id',$this->getUserInfo()->getId())
                                                          ->group('galleryID')
                                                           ->order('galleryID DESC');

                    $this->template->userData = $this->user->findUser(array("id" => $this->getUserInfo()->getId()));
                   
                    $this->template->setFile(dirname(__FILE__) . '/default.latte');
                    $this->template->render();
                }
                
                if($mode == "listFew"){
                    
                    $this->template->galleries = $this->getUserDataFromDB()
                                                        ->where("id = ANY (SELECT MAX( id ) FROM `user_images` WHERE userID =87 GROUP BY galleryID)"                                                 /*$this->getPresenter()->getContext()->createUsersFoto()->max("id")->where("userId",$this->userId)->group("galleryID")*/
                                                           )->order('galleryID DESC')->limit(3);

                    $this->template->userData = $this->user->findUser(array("id" => $this->getUserInfo()->getId()));

                    $this->template->setFile(dirname(__FILE__) . '/default.latte');
                    $this->template->render(); 
                    
                }
                
                
                $this->addToCssVariables(array(
			"img-height" => "200px",
			"img-width" => "200px",
			"text-padding-top" => "40%"
		));
	}
   
        protected function getUserDataFromDB(){
            return $this->getPresenter()->getContext()->createUsersFoto();
        }
        protected function getUserInfo(){
            return $this->getPresenter()->getUser();
        }
 

}

?>
 