<?php

namespace ProfilModule;

/**
 * Base presenter for all profile application presenters.
 */

use \Nette\Security\User,
	\UserGalleries\UserGalleries,
        \Nette\Application\UI\Form as Frm;

class GalleriesPresenter extends \BasePresenter
{

        private $userModel;
        private $user;
        private $fotos;
        public $galleryID;

        public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);		

	}
        
                
	public function startup() {
		parent::startup();
             $this->userModel = $this->context->userModel;
	}
        
        public function actionUserGalleryChange($galleryID)
	{
            //\Nette\Diagnostics\Debugger::Dump($galleryID);die();
		$this->galleryID = $galleryID;
	}
        
        public function actionImageNew($galleryID)
	{

            if($galleryID){
		$this->galleryID = $galleryID;
            }
	}
        public function renderDefault($id)
	{
         if(empty($id)){ 
                    $this->template->mode = "listAll";

		} else {

                    $this->template->mode = "listFew";
		}
        }

	public function handledeleteGallery($galleryID)
	{
            
		$images = $this->context->createUsersFoto()
				->where("galleryID", $galleryID);
                        
		foreach($images as $image){   

                            $this->handledeleteImage($image->id, $galleryID, FALSE);
                }
                
		$way = WWW_DIR . "/images/userGalleries/" .$this->getUserInfo()->getId()."/". $galleryID;
		
		if( file_exists($way) )
		{
			rmdir($way);
		}
		
		$this->context->createUsersGallery()
				->where("id", $galleryID)
				->delete();
		
		$this->flashMessage("Galerie byla smazána.");
		$this->redirect("this");
	}
        
        public function handledeleteImage($id_image, $id_gallery, $redirekt = TRUE)
	{		
		$image = $this->context->createUsersFoto()
				->where("id", $id_image)
				->fetch();
 
                $way = WWW_DIR . "/images/userGalleries/" . $image->userId . "/" . $id_gallery . "/" . $image->id . "." . $image->suffix;
		$wayMini = WWW_DIR . "/images/userGalleries/" . $image->userId . "/" . $id_gallery . "/min" . $image->id . "." . $image->suffix;
                $wayScrn = WWW_DIR . "/images/userGalleries/" . $image->userId . "/" . $id_gallery . "/galScrn" . $image->id . "." . $image->suffix;
                $waySqr = WWW_DIR . "/images/userGalleries/" . $image->userId . "/" . $id_gallery . "/minSqr" . $image->id . "." . $image->suffix;
		
		if( file_exists($way) )
		{
			unlink($way);
		}
		
		if( file_exists($wayMini) )
		{
			unlink($wayMini);
		}
		
                if( file_exists($wayScrn) )
		{
			unlink($wayScrn);
		}
                
                if( file_exists($waySqr) )
		{
			unlink($waySqr);
		}
                
		$this->context->createUsersFoto()
			->where("id", $id_image)
			->delete();
 
		
		if($redirekt)
		{
			$this->flashMessage("Obrázek byl smazán.");
			$this->redirect("this");
		}
	}
 
        protected function getUserDataFromDB(){
            return $this->context->createUsersFoto();
        }
        protected function getUserInfo(){
            return $this->getUser();
        }
        
        
        public function renderListUserGalleryImages($galleryID) {
    //        \Nette\Diagnostics\Debugger::Dump($galleryID);die();
                $this->template->images = $this->getUserDataFromDB()
                                               ->where('userID',$this->getUserInfo()->getId())
                                               ->where("galleryID", $galleryID)
                                               ->order("id DESC");
                
		$this->template->galleryID = $galleryID;
                 
		$this->template->userData = $this->userModel->findUser(array("id" => $this->getUserInfo()->getId()));
              //               \Nette\Diagnostics\Debugger::Dump($this->template->userData);die();
      	}
        
        public function createComponentUserGalleries(){ 
            return new \UserGalleries($this->userModel);
        }
        
        protected function createComponentUserGalleryNew($name) {
		return new Frm\UserGalleryNewForm($this, $name);
	}
        protected function createComponentUserGalleryChange($name) {
		return new Frm\UserGalleryChangeForm($this, $name);
	}
        
        protected function createComponentNewImage($name) {  
		return new Frm\NewImageForm($this,$name);
	}
}
