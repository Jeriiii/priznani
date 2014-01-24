<?php

/**
 * Form presenter.
 *
 * Obsluha administrační části systému.
 * Formuláře.
 *
 * @author     Petr Kukrál
 * @package    Safira
 */

namespace AdminModule;

use Nette\Application\UI\Form as Frm;

class GalleriesPresenter extends AdminSpacePresenter
{
	public $id_gallery;
	
	public function renderGalleries() 
	{
		$this->template->galleries = $this->context->createGalleries();
	}
	
	public function actionGallery($id_gallery)
	{
		$this->id_gallery = $id_gallery;
	}
	
	public function renderGallery($id_gallery) 
	{
		$this->template->gallery = $this->context->createGalleries()
				->where("id", $id_gallery)
				->fetch();
		$this->template->images = $this->context->createImages()
				->where("galleryID", $id_gallery)
				->order("order ASC");
	}
		
	protected function createComponentImageGalleryNewForm($name) {
		return new Frm\imageGalleryNewForm($this, $name);
	}
	
	protected function createComponentVideoGalleryNewForm($name) {
		return new Frm\videoGalleryNewForm($this, $name);
	}
	
	protected function createComponentGalleryNewForm($name) {
		return new Frm\galleryNewForm($this, $name);
	}
	
	protected function createComponentGalleryChangeForm($name) {
		return new Frm\galleryChangeForm($this, $name);
	}
	
	protected function createComponentGalleryReleaseForm($name) {
		return new Frm\galleryReleaseForm($this, $name);
	}
	
	public function handledeleteGallery($id_gallery)
	{
		$images = $this->context->createImages()
				->where("galleryID", $id_gallery);
		
		foreach($images as $image)
			$this->handledeleteImage($image->id, $id_gallery, FALSE);

		$way = WWW_DIR . "/images/galleries/" . $id_gallery;
		
		if( file_exists($way) )
		{
			rmdir($way);
		}
		
		$this->context->createGalleries()
				->where("id", $id_gallery)
				->delete();
		
		$this->flashMessage("Galerie byla smazána.");
		$this->redirect("this");
	}


	public function handledeleteImage($id_image, $id_gallery, $redirekt = TRUE)
	{		
		$image = $this->context->createImages()
				->where("id", $id_image)
				->fetch();
		
		$videoID = $image->videoID;
		
		$way = WWW_DIR . "/images/galleries/" . $id_gallery . "/" . $image->id . "." . $image->suffix;
		$wayMini = WWW_DIR . "/images/galleries/" . $id_gallery . "/mini" . $image->id . "." . $image->suffix;
		
		if( file_exists($way) )
		{
			unlink($way);
		}
		
		if( file_exists($wayMini) )
		{
			unlink($wayMini);
		}
		
		$this->context->createImages()
			->where("id", $id_image)
			->delete();
		
		/* jedna se o video */
		if($videoID != 0) {
			$this->context->createVideos()
				->find($image->videoID)
				->delete();
		}
		
		if($redirekt)
		{
			$this->flashMessage("Obrázek byl smazán.");
			$this->redirect("this");
		}
	}
	
	public function handleunsetGallery($id_gallery, $id_page)
	{
		$this->context->createPages_galleries()
				->where(array(
					"id_gallery" => $id_gallery,
					"id_page" => $id_page,
				))
				->delete();
		$this->flashMessage("Galerie byla odpojena.");
		$this->redirect("this");
	}
	
}
?>