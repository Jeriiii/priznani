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
	Nette\Utils\Finder,
	Nette\DateTime;

class AdminPresenter extends AdminSpacePresenter
{

	public $id_gallery;
	public $id_file;
	
	public function actionDefault()
	{
		if($this->getUser()->isInRole("estateAgent") || $this->getUser()->isInRole("realEstate")) $this->redirect("EstateAgent:");
	}
	
	public function renderDefault()
	{
		$this->redirect("Forms:forms");
//		$this->template->forms_new_send = $this->context->createForm_new_send()
//												->order("date DESC");
//		$this->template->forms = $this->context->createForms();
//		
//		$date = new DateTime();
//		$date->modify('-2 month');
//		
//		$this->context->createForm_new_send()
//				->where("mark", 1)
//				->where("date < ?", $date)
//				->delete();
	}
	
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
				->where("id_gallery", $id_gallery)
				->order("order ASC");
	}
	
	public function renderAccounts()
	{
		$this->template->unconfirmed_users = $this->context->createUsers()
							->where("role", "unconfirmed_user");
		$this->template->users = $this->context->createUsers()
							->where("role", "user");
		$this->template->admins = $this->context->createUsers()
							->where("role", "admin");
		$this->template->superadmins = $this->context->createUsers()
							->where("role", "superadmin");
	}
	
	public function renderFiles()
	{
		$this->template->files = $this->context->createFiles();
	}
	
	public function actionChangeFile($id_file)
	{
		$this->id_file = $id_file;
	}
	
	protected function createComponentGalleryNewForm($name) {
		return new Frm\galleryNewForm($this, $name);
	}
	
	protected function createComponentGalleryChangeForm($name) {
		return new Frm\galleryChangeForm($this, $name);
	}
	
	protected function createComponentImageNewForm($name) {
		return new Frm\imageNewForm($this, $name);
	}
	
	protected function createComponentGalleryReleaseForm($name) {
		return new Frm\galleryReleaseForm($this, $name);
	}
	
	protected function createComponentAuthorizatorForm($name) {
		return new Frm\authorizatorForm($this, $name);
	}
	
	protected function createComponentFacebookForm($name) {
		return new Frm\facebookForm($this, $name);
	}
	
	protected function createComponentPasswordForm($name) {
		return new Frm\passwordForm($this, $name);
	}
	
	protected function createComponentFileNewForm($name) {
		return new Frm\fileNewForm($this, $name);
	}
	
	protected function createComponentFileChangeForm($name) {
		return new Frm\fileChangeForm($this, $name);
	}
	
	protected function createComponentMapForm($name) {
		return new Frm\mapForm($this, $name);
	}
	
	protected function createComponentGoogleAnalyticsForm($name) {
		return new Frm\googleAnalyticsForm($this, $name);
	}
    
	protected function createComponentDetailInzerat() {
		$dialog = new \Cherry\JDialogs\BaseDialog;
		$dialog->template_file = APP_DIR."/dialogs/templates/basedialog.latte";

		$text = "muj text";
		
		$dialog->addData(array("text" => $text));	

		$dialog->addOption(array(
			"autoOpen" => "false", 
			"title" => "testDialogTwo",
		));

		return $dialog;
	}
	
	public function handledeleteGallery($id_gallery)
	{
		$images = $this->context->createImages()
				->where("id_gallery", $id_gallery);
		
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
		
		if($redirekt)
		{
			$this->flashMessage("Obrázek byl smazán.");
			$this->redirect("this");
		}
	}
	
	public function handledeleteUser($id)
	{
		$this->context->createUsers()
				->where("id",$id)
				->delete();
		
		$this->flashMessage("Uživatel byl smazán.");
		$this->redirect("this");
	}
	
	public function handlechangeRole($id, $type)
	{
		$user = $this->context->createUsers()
				->where("id",$id);
		$role = $this->context->createUsers()
				->where("id",$id)
				->fetch()
				->role;
		if(! $type)
		{
			if($role == "user")
			{
				$user->update(array(
					"role" => "admin"
				));
			}elseif($role == "admin"){
				$user->update(array(
					"role" => "user"
				));
			}
		}else{
			if($role == "admin")
			{
				$user->update(array(
					"role" => "superadmin"
				));
			}elseif($role == "superadmin"){
				$user->update(array(
					"role" => "admin"
				));
			}
		}
		$this->flashMessage("Práva byla změněna.");
		$this->redirect("this");
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
	
	public function handledeleteFile($id)
	{
		$file = $this->context->createFiles()
			->where("id",$id);
		
		$path = WWW_DIR."/files/page_files/".$id.'.'.$file->fetch()->suffix;
		
		if(file_exists($path))
			unlink($path);
		
		$file->delete();
		$this->flashMessage("Soubor byl smazán.");
		$this->redirect("this");
	}
	
	public function handleMarkNewSendForm($id, $path)
	{
		$this->context->createForm_new_send()
				->find($id)
				->update(array(
					"mark" => 1
				));
		
		$this->redirectUrl($path);
	}
}
?>