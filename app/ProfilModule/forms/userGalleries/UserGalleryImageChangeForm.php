<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\Security as NS;
use Nette\ComponentModel\IContainer;
use Nette\Forms\Controls;
use Nette\Utils\Strings as Strings;
use Nette\Image;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;

class UserGalleryImageChangeForm extends UserGalleryImagesBaseForm {

	/**
	 * @var \POS\Model\UserGalleryDao
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\UserImageDao
	 */
	public $userImageDao;
	private $imageID;
	private $galleryID;

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, $imageID, $galleryID, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($userGalleryDao, $userImageDao, $parent, $name);

		//form

		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->imageID = $imageID;
		$this->galleryID = $galleryID;

		$image = $this->userImageDao->find($imageID);

		$this->addText('name', 'Jméno:')
			->addRule(Form::MAX_LENGTH, "Maximální délka jména fotky je %d znaků", 40)
			->addRule(Form::FILLED, 'Zadejte jméno fotky');

		$this->addTextArea('description', 'Popis fotky:', 100, 2)
			->addRule(Form::MAX_LENGTH, "Maximální délka popisu fotky je %d znaků", 500);

		$this->setDefaults(array(
			"name" => $image->name,
			"description" => $image->description
		));

		$this->addSubmit('send', 'Změnit')
			->setAttribute('class', 'btn-main medium');
		//$this->addProtection('Vypršel časový limit, odešlete formulář znovu');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(UserGalleryImageChangeForm $form) {
		$values = $form->values;
		$presenter = $form->getPresenter();

		$this->userImageDao->update($this->imageID, $values);

		$presenter->flashMessage('Fotka byla úspěšně změněna');
		$presenter->redirect("Galleries:listUserGalleryImages", $this->galleryID);
	}

}
