<?php

namespace Nette\Application\UI\Form;

use POS\Model\UserGalleryDao;

/**
 * Formulář pro povolení přátel procházet danou galerii
 */
class AllowFriendsForm extends BaseForm {

	private $userGalleryDao;

	public function __construct(UserGalleryDao $userGalleryForm, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->userGalleryDao = $userGalleryForm;
		$this->ajax();

		$gallery = $this->userGalleryDao->find($this->getPresenter()->getParameter("galleryID"));

		$this->addCheckbox('allowFriends', 'Povolit galerii pro přátele');


		$this->setDefaults(array("allowFriends" => $gallery->allow_friends));

		$this->addSubmit('send', 'Změnit');
		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(AllowFriendsForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();
		$gallery = $this->userGalleryDao->find($this->getPresenter()->getParameter("galleryID"));
		$gallery->update(array("allow_friends" => $values->allowFriends));

		if ($presenter->isAjax()) {
			$presenter->redrawControl("friends");
		} else {
			$presenter->redirect("this");
		}
	}

}
