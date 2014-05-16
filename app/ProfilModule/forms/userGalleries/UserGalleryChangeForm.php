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

		$this->addGroup('Kategorie');

		$this->addCheckbox('man', 'jen muži');

		$this->addCheckbox('women', 'jen ženy');

		$this->addCheckbox('couple', 'pár');

		$this->addCheckbox('more', '3 a více');

		$this->setDefaults(array(
			"name" => $filledForm->name,
			"descriptionGallery" => $filledForm->description,
			"man" => $filledForm->man,
			"women" => $filledForm->women,
			"couple" => $filledForm->couple,
			"more" => $filledForm->more,
		));

		$this->addSubmit('send', 'Změnit')->setAttribute('class', 'btn-main medium');
		$this->onValidate[] = callback($this, 'checkboxValidation');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(UserGalleryChangeForm $form) {
		$values = $form->values;
		$presenter = $form->getPresenter();

		$values2['name'] = $values->name;
		$values2['description'] = $values->descriptionGallery;
		$values2['man'] = $values->man;
		$values2['women'] = $values->women;
		$values2['couple'] = $values->couple;
		$values2['more'] = $values->more;

		$presenter->context->createUsersGalleries()
			->where("id", $this->galleryID)
			->update($values2);

		$presenter->flashMessage('Galerie byla úspěšně změněna');
		$presenter->redirect("Galleries:");
	}

}
