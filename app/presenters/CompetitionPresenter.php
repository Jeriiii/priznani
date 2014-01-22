<?php

/**
 * Homepage presenter.
 *
 * Zobrayení úvodní stránky systému
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */
use Nette\Application\UI\Form as Frm,
	Nette\Http\Request;

class CompetitionPresenter extends BasePresenter
{
	public $imageID;
	public $image;
	public $gallery;
	public $domain;


	public function startup() {
		parent::startup();
		
		$httpRequest = $this->context->httpRequest;
		$this->domain = $httpRequest
							->getUrl()
							->host;

		if(strpos($this->domain, "priznanizparby") !== false)
		{
			$this->setPartyMode();
		} else {
			$this->setSexMode();
		}
	}

	public function beforeRender() {
		parent::beforeRender();
	}
	
	public function renderList() {
		$galleries = $this->context->createGalleries()
							->where("sexmode", 1);
		
		if($this->partymode) {
			$galleries->where("partymode", 1);					
		} else {
			$galleries->where("sexmode", 1);	
		}
		
		$this->template->competitions = $galleries;
	}

	/* bacha, id je url - tedy nazev stranky */
	
	public function actionDefault($imageID, $galleryID)
	{
		//určitá galerie
		if(! empty($galleryID)) {
			$this->gallery = $this->context->createGalleries()
								->where("id", $galleryID)
								->fetch();
		}
		
		/* id obrázku je uloženo v odkaze od galerie */
		if(empty($imageID)) 
		{
			/* pokus o naplneni promenne z gallerie */
			$httpRequest = $this->context->httpRequest;
			$imageID = $httpRequest->getQuery('gallery-imageID');
		}
		if(empty($imageID)) 
		{
			// je na hlavní stránce soutěží ?
			if(empty($galleryID)) {
				$this->gallery = $this->getLastGallery();
			}
			
			// vrátí obrázek z galerie, pokud existuje alespoň jeden
			$imageID = $this->getImageIDFromGallery($this->gallery);
		}

		if(empty($imageID))
		{
			/* zatím žádný obrázek v galerii není */
			$this->redirect("Competition:uploadImage");
		} else {
			// obrázek nalezen, nastavení obrázku do presenteru
			$this->imageID = $imageID;

			$this->image = $this->context->createImages()
							->find($this->imageID)
							->fetch();
			$this->gallery = $this->context->createGalleries()
									->where("id", $this->image->galleryID)
									->fetch();
                        
			$this->domain = $this->partymode ? "http://priznanizparby.cz" : "http://priznaniosexu.cz";
		}
	}
	
	/**
	 * vrátí ID obrázku když je v této galerii alespoň jeden.
	 * jinak null
	 */
	
	public function getImageIDFromGallery($gallery) {
		$image = $this->context->createImages()
						->where("galleryID", $gallery->id)
						->where("approved", 1)
						->order("id DESC")
						->fetch();

		if(!empty($image)) {
			return $image->id;
		}
		
		return null;
	}
	
	/**
	 * vrátí poslední galerii podle modu sex/pařba
	 */
	
	public function getLastGallery() {
		$gallery = $this->context->createGalleries();

		if($this->partymode) {
			$gallery->where("partymode", 1);							
		} else {
			$gallery->where("sexmode", 1);	
		}

		$gallery = $gallery
					->order("id DESC")
					->fetch();
		
		return $gallery;
	}
	
	public function renderDefault($imageID, $galleryID)
	{
		if(!empty($this->imageID))
			$this->template->imageLink = $this->domain . "/images/galleries/" . $this->gallery->id . "/" . $this->image->id . "." . $this->image->suffix;
		else
			$this->template->imageLink = null;
	}

	public function renderUploadImage()
	{
		$photos = $this->context->createImages()
										->order("id DESC");
		$this->template->photo1 = $photos->fetch();
		$this->template->photo2 = $photos->fetch();
		
		$this->template->galleryID = $this->context->createGalleries()
										->order("id DESC")
										->fetch()
										->id;
		if($this->partymode) {
			$this->template->images = array(1,2,3);
		} else {
			$this->template->images = array(1,2,3);
		}
	}

	protected function createComponentGallery()
	{
		//			if($this->getUser()->isInRole("admin") || $this->getUser()->isInRole("superadmin"))
//			{
//			$imageID = $this->context->createImages()
//						->where("galleryID", $this->gallery->id)
//						->order("id DESC")
//						->fetch();
//			
//			}else{
			$imageID = $this->context->createImages()
						->where("galleryID", $this->gallery->id)
						->where("approved", 1)
						->order("id DESC")
						->fetch();
//			}
//			
		if($this->getUser()->isInRole("admin") || $this->getUser()->isInRole("superadmin"))
		{
			$images = $this->context->createImages()
					->where("galleryID", $this->gallery->id);
		}else{
			$images = $this->context->createImages()
					->where("galleryID", $this->gallery->id)
					->where("approved", 1);
		}
		
		return new Gallery($images, $this->image, $this->gallery, $this->domain, $this->partymode);
	}

	protected function createComponentImageNew($name) {
		return new Frm\ImageNewForm($this, $name);
	}
	
	public function createComponentCss()
	{
			$files = new \WebLoader\FileCollection(WWW_DIR . '/css');
			//$files->addFiles(array('form.css', 'ex.less'));
			$compiler = \WebLoader\Compiler::createCssCompiler($files, WWW_DIR . '/cache/css');
			$compiler->addFileFilter(new \Webloader\Filter\LessFilter());
			$compiler->addFileFilter(function ($code, $compiler, $path) {
					return cssmin::minify($code);
			});

	// nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu
	 return new \WebLoader\Nette\CssLoader($compiler, $this->template->basePath . '/cache/css');
	}
	
	public function createComponentJs()
	{
			$files = new \WebLoader\FileCollection(WWW_DIR . '/js');                                       
							 //$files->addRemoteFile('http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js');
			$files->addFiles(array(
				'netteForms.js'
			));

			$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
			$compiler->addFilter(function ($code) {
				$packer = new JavaScriptPacker($code, "None");
				return $packer->pack();
			});
	return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	
	public function handleincLike($id_confession){}
	
	public function handledecLike($id_confession){}
	
	public function handleincComment($id_confession){}
	
	public function handledecComment($id_confession){}


}
