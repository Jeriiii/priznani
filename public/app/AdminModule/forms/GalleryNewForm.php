<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;
use POS\Model\GalleryDao;

class GalleryNewForm extends Form {

	/**
	 * @var \POS\Model\GalleryDao
	 */
	private $galleryDao;

	public function __construct(GalleryDao $galleryDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		//graphics
		$renderer = $this->getRenderer();
		$renderer->wrappers['controls']['container'] = 'div';
		$renderer->wrappers['pair']['container'] = 'div';
		$renderer->wrappers['label']['container'] = NULL;
		$renderer->wrappers['control']['container'] = NULL;
		//form

		$mode = array(
			"sex" => "Přiznání o sexu",
			"party" => "Přiznání z pařby"
		);

		$this->galleryDao = $galleryDao;
		$this->addText("name", "Jméno", 30, 150)
			->addRule(Form::FILLED, "Musíte zadat jméno galerie.");
		$this->addText("description", "Popis", 50, 300)
			->addRule(Form::FILLED, "Musíte zadat popis galerie.");
		$this->addSelect("mode", "Mód:", $mode)
			->setPrompt("- vyberte mod -")
			->addRule(Form::FILLED, "Prosím vyberte mód.");

		$this->addSubmit("submit", "Vytvořit");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(GalleryNewForm $form) {
		$values = $form->values;

		$mode = $values["mode"];
		unset($values["mode"]);
		if ($mode == "sex") {
			$values["sexmode"] = 1;
		} else {
			$values["partymode"] = 1;
		}

		$this->galleryDao->insert($values);
		$this->getPresenter()->flashMessage('Galerie byla vytvořena, nezapomeňte pak v databázi vyplnit zastupující obrázek');
		$this->getPresenter()->redirect('Galleries:galleries');
	}

}