<?php

namespace ProfilModule;

use Nette\Application\UI\Form as Frm,
	Nette\ComponentModel\IContainer;

class EditProfilPresenter extends ProfilBasePresenter {

	private $userModel;
	private $record;
	private $record_couple_partner;
	public $fotos;
	private $id;
	private $user;

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
	}

	protected function createComponentFirstEditForm($name) {
		return new Frm\DatingEditFirstForm($this, $name);
	}

	protected function createComponentSecondEditForm($name) {
		return new Frm\DatingEditSecondForm($this, $name);
	}

	protected function createComponentThirdEditManForm($name) {
		return new Frm\DatingEditManThirdForm($this, $name);
	}

	protected function createComponentThirdEditWomanForm($name) {
		return new Frm\DatingEditWomanThirdForm($this, $name);
	}

	protected function createComponentFourthEditWomanForm($name) {
		return new Frm\DatingEditWomanFourthForm($this, $name);
	}

	protected function createComponentFourthEditManForm($name) {
		return new Frm\DatingEditManFourthForm($this, $name);
	}

	protected function createComponentGroupEditForm($name) {
		return new Frm\groupEditForm($this, $name);
	}

	protected function createComponentUploadPhotosForm($name) {
		return new Frm\UploadPhotosForm($this, $name);
	}

	protected function createComponentInterestedInForm($name) {
		return new Frm\InterestedInForm($this, $name);
	}

	protected function createComponentGalleryProfilEditForm($name) {
		return new Frm\galleryProfilEditForm($this, $name);
	}

	public function startup() {
		parent::startup();
		$this->userModel = $this->context->userModel;
	}

	public function renderDefault($id) {
		if (empty($id)) {
			$this->user = $this->userModel->findUser(array("id" => $this->getUser()->getId()));
		} else {
			$this->user = $this->userModel->findUser(array("id" => $id /* $this->getUser()->getId() */));
		}
		//	$this->record = $this->userModel->findUser(array('id' => $this->getUser()->getId()) );
		$this->template->userData = $this->user; //$this->userModel->findUser(array("id" => $this->getUser()->getId()));
//		/** single man */
//			if($this->record->user_property == "man"){
//				$this->setView("singleEditManForm");
//				
//		/** single woman */		
//			}else if($this->record->user_property == "woman"){
//					$this->setView("singleEditWomanForm");
//					
//			/** couple man + woman */
//			}else if($this->record->user_property == "couple"){
//					$this->setView("coupleEditWomanManForm");								
//
//			/** couple woman + woman */
//			}else if($this->record->user_property == "coupleWoman"){
//					$this->setView("coupleEditWomanWomanForm");
//					
//			/** couple man + man */		
//			}else if($this->record->user_property == "coupleMan"){
//					$this->setView("coupleEditManManForm");
//			
//			/** group */
//			}else if($this->record->user_property == "group"){
//					$this->setView("groupEditForm");
//			}

		/** mini galerie */
		$this->fotos = $this->context->createUsersFoto()->findUserFoto((array('userId' => $this->getUser()->getId())));

		$this->template->fotos = $this->fotos;
		if ($this->fotos != null) {
			$this->template->hasFoto = true;
		} else {
			$this->template->hasFoto = false;
		}
	}

	public function handledeleteImage($id_image, $id_user, $redirekt = TRUE) {
		$image = $this->context->createUsersFoto()
			->where("id", $id_image)
			->fetch();

		$way = WWW_DIR . "/images/users/profils/" . $id_user . "/" . $image->id . "." . $image->suffix;
		$wayMini = WWW_DIR . "/images/users/profils/" . $id_user . "/mini" . $image->id . "." . $image->suffix;

		if (file_exists($way)) {
			unlink($way);
		}

		if (file_exists($wayMini)) {
			unlink($wayMini);
		}

		$this->context->createUsersFoto()
			->where("id", $id_image)
			->delete();

		if ($redirekt) {
			$this->flashMessage("Obrázek byl smazán.");
			$this->redirect('this');
		}
	}

}
