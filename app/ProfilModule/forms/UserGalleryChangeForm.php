<?php

namespace Nette\Application\UI\Form;

use Nette\ComponentModel\IContainer;

class UserGalleryChangeForm extends UserGalleryBaseForm {

	private $galleryID;

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		//form
		$presenter = $this->getPresenter();
		$this->galleryID = $presenter->galleryID;

		$filledForm = $presenter->context->createUsersGalleries()
			->where('id', $this->galleryID)
			->fetch();

		$this->setDefaults(array(
			"name" => $filledForm->name,
			"description" => $filledForm->description
		));
		
		$this->addSubmit('send', 'Změnit')->setAttribute('class', 'btn-main medium');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(UserGalleryChangeForm $form) {
		$values = $form->values;
		$presenter = $form->getPresenter();

		$values2['name'] = $values->name;
		$values2['description'] = $values->descriptionGallery;

		$presenter->context->createUsersGalleries()
			->where("id", $this->galleryID)
			->update($values2);

		$presenter->flashMessage('Galerie byla úspěšně změněna');
		$presenter->redirect("Galleries:");
	}

}
