<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer;

class UserGalleryBaseForm extends UserGalleryImagesBaseForm {

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addGroup("Info o galerii");
		
		$this->addText("name", "Jméno galerie", 30, 150)
			->addRule(Form::MAX_LENGTH, "Maximální délka jména galerie je %d znaků", 150);
		$this->addTextArea('descriptionGallery', 'Popis galerie:', 100, 15)
			->addRule(Form::MAX_LENGTH, "Maximální délka popisu galerie je %d znaků", 500);
		return $this;
	}

}