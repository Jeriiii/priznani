<?php

namespace Nette\Application\UI\Form;

use Nette\ComponentModel\IContainer;

/**
 * Upraví galerii
 */
class UserGalleryChangeForm extends UserGalleryBaseForm {

	/**
	 * @var \POS\Model\UserGalleryDao
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\ImageGalleryDao
	 */
	public $userImageDao;
	private $galleryID;

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, $galleryID, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		//form
		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->galleryID = $galleryID;

		$gallery = $this->userGalleryDao->find($galleryID);

		$this->addGroup('Kategorie');

		$this->genderCheckboxes();

		$this->setDefaults(array(
			"name" => $gallery->name,
			"descriptionGallery" => $gallery->description,
			"man" => $gallery->man,
			"women" => $gallery->women,
			"couple" => $gallery->couple,
			"more" => $gallery->more,
		));

		$this->addSubmit('send', 'Změnit')->setAttribute('class', 'btn-main medium');
		$this->onValidate[] = callback($this, 'checkboxValidation');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(UserGalleryChangeForm $form) {
		$values = $form->values;
		$presenter = $form->getPresenter();

		$this->userGalleryDao->updateNameDescGender($this->galleryID, $values->name, $values->descriptionGallery, $values->man, $values->women, $values->couple, $values->more);

		$presenter->flashMessage('Galerie byla úspěšně změněna');
		$presenter->redirect("Galleries:");
	}

}
