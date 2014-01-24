<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	Nette\Image;


class ItemGalleryNewForm extends Form
{
	public $id_gallery;

	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
		
		$presenter = $this->getPresenter();
		$this->id_gallery = $presenter->id_gallery;
		
		$this->addText("name", "Jméno obrázku/videa:", 30, 35)
			->addRule(Form::FILLED, "Musíte zadat jméno obrázku.");
		$this->addTextArea("comment", "Komentář obrázku", 30, 6)
			->addRule(Form::MAX_LENGTH, "Maximální délka komentáře je %d znaků", 500);
	}
}
