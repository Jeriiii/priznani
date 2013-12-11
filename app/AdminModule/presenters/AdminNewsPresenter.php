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

use Nette\Application\UI\Form as Frm,
	Nette\Security\User;

class AdminNewsPresenter extends AdminSpacePresenter
{
	public $id_new;
	
	public function renderDefault()
	{
		$this->template->news = $this->context->createNews();
	}
	
	public function actionChangeNew($id) 
	{
		$this->id_new = $id;
		$this->template->id_new = $id;
		$this->template->galleries = $this->context->createNews_galleries()
			->getGalleries($id);
	}
	
	public function renderChangeNew($id_new)
	{
	}


	protected function createComponentNewNewForm($name) {
		return new Frm\newNewForm($this, $name);
	}
	
	protected function createComponentNewChangeForm($name) {
		return new Frm\newChangeForm($this, $name);
	}
	
	protected function createComponentAddGalleryToNewsForm($name) {
		return new Frm\addGalleryToNewsForm($this, $name);
	}
	
	public function handleunsetGallery($id_gallery, $id_new)
	{
		$this->context->createNews_galleries()
				->where(array(
					"id_gallery" => $id_gallery,
					"id_new" => $id_new,
				))
				->delete();
		$this->flashMessage("Galerie byla odpojena.");
		$this->redirect("this");
	}
	
	public function handledeleteNew($id_new){
		$this->context->createNews()
			->find($id_new)
			->delete();
		$this->flashMessage("Aktualita byla smazána.");
		$this->redirect("this");
	}
}
?>