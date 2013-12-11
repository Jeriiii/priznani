<?php

/**
 * Admin presenter.
 *
 * Obsluha administrační části systému.
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */

namespace AdminModule;

use Nette\Application\UI\Form as Frm;

class PagesPresenter extends AdminSpacePresenter
{

	public $id_page;
	
	public function renderPagesSort()
	{
		$this->template->pages = $this->context->createPages()
									->order("order ASC");
	}
	
	public function actionChangePage($id_page) 
	{
		$this->id_page = $id_page;
		$this->template->id_page = $id_page;
		$this->template->galleries = $this->context->createPages_galleries()
				->getGalleries($id_page);
	}
	
	public function actionChangeSpecialPage($id_page) 
	{
		$this->id_page = $id_page;
	}
		
	protected function createComponentPageChangeForm($name) {
		return new Frm\pageChangeForm($this, $name);
	}
	
	protected function createComponentPageSpecialChangeForm($name) {
		return new Frm\pageSpecialChangeForm($this, $name);
	}
	
	protected function createComponentPageNewForm($name) {
		return new Frm\pageNewForm($this, $name);
	}
	
	protected function createComponentAddFormForm($name) {
		return new Frm\addFormForm($this, $name);
	}
	
	protected function createComponentAddGalleryForm($name) {
		return new Frm\addGalleryForm($this, $name);
	}
	
	public function handledeletePage($id)
	{
		$this->context->createPages_galleries()
			->where("id_page", $id)
			->delete();
		
		$this->context->createPages_forms()
			->where("id_page", $id)
			->delete();
		
		$this->context->createFiles()
			->where("id_page", $id)
			->update(array(
				"id_page" => NULL
			));
		
		$this->context->createTexts()
			->where("id",$id)
			->delete();
		
		$this->context->createPages()
			->where("id_view", $id)
			->delete();
		
		$this->flashMessage("Stránka byla smazána.");
		$this->redirect("this");
	}
	
	public function handlepagesGoUp($id)
	{
		$pages = new \PagesSort($this);
		$pages->setDataPages($this->context->createPages()->order("order ASC"));
		
		$pages->goUp($id);
	}
	
	public function handlepagesGoDown($id)
	{
		$pages = new \PagesSort($this);
		$pages->setDataPages($this->context->createPages()->order("order ASC"));
		
		$pages->goDown($id);
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