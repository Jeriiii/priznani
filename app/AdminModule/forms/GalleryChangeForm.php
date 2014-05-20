<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;
use POS\Model\GalleryDao;

class GalleryChangeForm extends Form {

	/**
	 * @var \POS\Model\GalleryDao
	 */
	private $galleryDao;

	/**
	 * @var int
	 */
	private $galleryID;

	public function __construct(GalleryDao $galleryDao, $galleryID, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		//graphics
		$renderer = $this->getRenderer();
		$renderer->wrappers['controls']['container'] = 'div';
		$renderer->wrappers['pair']['container'] = 'div';
		$renderer->wrappers['label']['container'] = NULL;
		$renderer->wrappers['control']['container'] = NULL;
		//form
		$this->galleryDao = $galleryDao;
		$this->galleryID = $galleryID;
		$gallery = $this->galleryDao->find($galleryID);

		$this->addText("name", "Jméno", 30, 150)
			->setDefaultValue($gallery->name)
			->addRule(Form::FILLED, "Musíte zadat jméno galerie.");
		$this->addText("description", "Popis", 50, 300)
			->setDefaultValue($gallery->description)
			->addRule(Form::FILLED, "Musíte zadat popis galerie.");
		$this->addSubmit("submit", "Změnit");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(GalleryChangeForm $form) {
		$values = $form->values;
		$presenter = $this->getPresenter();
		$this->galleryDao->updateNameDecrip($this->galleryID, $values->name, $values->description);

		$presenter->flashMessage('Galerie byla změněna');
		$presenter->redirect('Galleries:gallery', $this->galleryID);
	}

}
