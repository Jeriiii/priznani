<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\StreamDao;
use Nette\Utils\Html;

/**
 * Základní formulář pro galerii
 */
class UserGalleryBaseForm extends UserGalleryImagesBaseForm {

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, StreamDao $streamDao, $isPaying, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($userGalleryDao, $userImageDao, $streamDao, $parent, $name);

		$this->addGroup("");

		$this->addText("name", "Jméno galerie", 30, 150)
			->addRule(Form::FILLED, "Vyplňte prosím jméno galerie")
			->addRule(Form::MAX_LENGTH, "Maximální délka jména galerie je %d znaků", 150);
		$this->addTextArea('description', 'Popis galerie:', 100, 2)
			->addRule(Form::MAX_LENGTH, "Maximální délka popisu galerie je %d znaků", 500);

		if ($isPaying) {
			$this->addCheckbox('private', Html::el()->setHtml('Soukromá <span class="tooltip-sign">?
			<div class="tooltip-element">
			Pokud nastavíte galerii jako soukromou, <br />budou si ji moci prohlížet pouze vaši přátelé a lidé,<br />kterým to dovolíte.
			</div>
			</span>'));
		}

		return $this;
	}

}
