<?php

namespace Nette\Application\UI\Form;

use Nette\ComponentModel\IContainer;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\StreamDao;

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

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, StreamDao $streamDao, $galleryID, $isPaying, $userID, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($userGalleryDao, $userImageDao, $streamDao, $isPaying, $userID, NULL, $parent, $name);
		//form
		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->galleryID = $galleryID;

		$gallery = $this->userGalleryDao->find($galleryID);

		$this->setDefaults(array(
			"name" => $gallery->name,
			"description" => $gallery->description,
			"private" => $gallery->private
		));

		$this->addSubmit('send', 'Změnit')->setAttribute('class', 'btn-main medium');

		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(UserGalleryChangeForm $form) {
		$values = $form->values;
		$presenter = $form->getPresenter();

		$this->userGalleryDao->update($this->galleryID, $values);

		$presenter->flashMessage('Galerie byla úspěšně změněna');
		$presenter->redirect("Galleries:");
	}

}
