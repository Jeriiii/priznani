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


	public function beforeRender() {
		parent::beforeRender();
		$this->setSexMode();
	}

	/* bacha, id je url - tedy nazev stranky */
	
	public function actionDefault($imageID)
	{
		$this->gallery = $this->context->createGalleries()
							->order("id DESC")
							->fetch();
		
		if(empty($imageID)) 
		{
			/* naplnime promenou z gallerie */
			$httpRequest = $this->context->httpRequest;
			$imageID = $httpRequest->getQuery('gallery-imageID');
		}
		if(empty($imageID)) 
		{
			/* nachazime se na hlavni strance */
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
			if(!empty($imageID))
				$imageID = $imageID->id;
		}

		if(empty($imageID))
		{
			/* zatím neexistuje obrázek */
			$this->redirect("Competition:uploadImage");
		} else {
			$this->imageID = $imageID;

			$this->image = $this->context->createImages()
							->find($this->imageID)
							->fetch();
			$this->domain = $this->partymode ? "http://priznanizparby.cz" : "http://priznaniosexu.cz";
		}
	}
	
	public function renderDefault($imageID)
	{
		if(!empty($this->imageID))
			$this->template->imageLink = "$this->domain/images/galleries/" . $this->gallery->id . "/" . $this->image->id . "." . $this->image->suffix;
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
		
		$this->template->images = array(1,2,3);
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
		
		return new Gallery($images, $this->image, $this->gallery, $this->domain);
	}

	protected function createComponentImageNew($name) {
		return new Frm\ImageNewForm($this, $name);
	}
	
	public function handleincLike($id_confession){}
	
	public function handledecLike($id_confession){}
	
	public function handleincComment($id_confession){}
	
	public function handledecComment($id_confession){}


}
