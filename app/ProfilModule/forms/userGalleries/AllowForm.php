<?php

namespace Nette\Application\UI\Form;

use POS\Model\UserAllowedDao,
	POS\Model\UserDao;

/**
 * Formulář přidává povolení lidem pro procházení galerie pomocí ajaxu
 */
class AllowForm extends BaseForm {

	/**
	 * @var \POS\Model\UserAllowedDao
	 */
	private $userAllowedDao;

	/**
	 * @var \POS\Model\UserDao
	 */
	private $userDao;

	public function __construct(UserAllowedDao $userAllowedDao, UserDao $userDao, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->userAllowedDao = $userAllowedDao;
		$this->userDao = $userDao;
		$this->ajax();

		$this->addText('user_name', '');

		$this->addSubmit('send', 'Přidat');
		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(AllowForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();
		$user = $this->userDao->findByUserName($values->user_name);
		$allowData["userID"] = $user->id;
		$allowData["galleryID"] = $presenter->getParameter("galleryID");

		$this->userAllowedDao->insert($allowData);

		if ($presenter->isAjax()) {
			$form->clearFields();
			$presenter->redrawControl('allowedUsers');
		} else {
			$presenter->redirect("this");
		}
	}

}
