<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use POS\Model\NewsDao;
use POS\Model\UsersNewsDao;
use POS\Model\UserDao;
use Nette\Application\UI\Form;

/**
 * Popis formuláře
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class NewsNewForm extends BaseForm {

	/** @var \POS\Model\NewsDao */
	public $newsDao;

	/** @var \POS\Model\UsersNewsDao @inject */
	public $usersNewsDao;

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	public function __construct(NewsDao $newsDao, UsersNewsDao $usersNewsDao, UserDao $userDao, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->newsDao = $newsDao;
		$this->usersNewsDao = $usersNewsDao;
		$this->userDao = $userDao;

		$name = $this->addText("name", "Název");
		$name->addRule(Form::FILLED, "Vyplňte název");
		$text = $this->addTextArea("text", "Text novinky", 250, 3);
		$text->addRule(Form::FILLED, "Vyplňte text");

		$this->addCheckbox("release", "Rovnou vydat")
			->setDefaultValue(TRUE);

		$this->addSubmit('send', 'Vytvořit');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(NewsNewForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$newID = $this->newsDao->insert(array(
			NewsDao::COLUMN_NAME => $values->name,
			NewsDao::COLUMN_TEXT => $values->text
		));

		if ($values->release) {
			$this->releaseNew($newID);
		}

		$presenter->flashMessage('Byla vytvořena nová aktualita');
		$presenter->redirect('News:');
	}

	/**
	 * Vydat novinku
	 * @param int $newID ID novinky
	 */
	protected function releaseNew($newID) {
		$users = $this->userDao->getAll();

		$this->newsDao->update($newID, array(
			NewsDao::COLUMN_RELEASE => 1
		));

		$this->usersNewsDao->begginTransaction();
		foreach ($users as $user) {
			$this->usersNewsDao->insert(array(
				UsersNewsDao::COLUMN_NEW_ID => $newID,
				UsersNewsDao::COLUMN_USER_ID => $user->id
			));
		}
		$this->usersNewsDao->endTransaction();
	}

}
