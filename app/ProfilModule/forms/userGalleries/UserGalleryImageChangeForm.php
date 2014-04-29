<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Forms\Controls,
	Nette\Utils\Strings as Strings;
use Nette\Image;

class UserGalleryImageChangeForm extends UserGalleryImagesBaseForm {

	private $galleryID;
	private $imageID;

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		//form
		$presenter = $this->getPresenter();

		$this->galleryID = $presenter->galleryID;
		$this->imageID = $presenter->imageID;

		$filledForm = $presenter->context->createUsersImages()
			->where('galleryID', $this->galleryID)
			->where('id', $this->imageID)
			->fetch();

		$this->addText('name', 'Jméno:')
			->setDefaultValue($filledForm->name)
			->addRule(Form::MAX_LENGTH, "Maximální délka jména fotky je %d znaků", 40)
			->addRule(Form::FILLED, 'Zadejte jméno fotky');

		$this->addTextArea('description', 'Popis fotky:', 100, 2)
			->setDefaultValue($filledForm->description)
			->addRule(Form::MAX_LENGTH, "Maximální délka popisu fotky je %d znaků", 500);
		$this->addSubmit('send', 'Změnit')
			->setAttribute('class', 'btn-main medium');
		//$this->addProtection('Vypršel časový limit, odešlete formulář znovu');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(UserGalleryImageChangeForm $form) {
		$values = $form->values;
		$presenter = $form->getPresenter();

		$presenter->context->createUsersImages()
			->where("id", $this->imageID)
			->update($values);

		$presenter->flashMessage('Fotka byla úspěšně změněna');
		$presenter->redirect("Galleries:listUserGalleryImages", $this->galleryID);
	}

}