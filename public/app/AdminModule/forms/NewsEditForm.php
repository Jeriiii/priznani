<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use POS\Model\NewsDao;
use POS\Model\UsersNewsDao;
use POS\Model\UserDao;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

/**
 * Editace novinky
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class NewsEditForm extends NewsNewForm {

	/** @var Active */
	private $new;

	public function __construct(ActiveRow $new, NewsDao $newsDao, UsersNewsDao $usersNewsDao, UserDao $userDao, $parent = NULL, $name = NULL) {
		parent::__construct($newsDao, $usersNewsDao, $userDao, $parent, $name);

		$this->new = $new;

		$this->setDefaults(array(
			"name" => $new->name,
			"text" => $new->text,
			"release" => $new->release
		));

		if ($new->release) {
			unset($this["release"]);
		}

		$this["send"]->caption = "Uložit";

		return $this;
	}

	public function submitted(NewsNewForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$this->newsDao->update($this->new->id, array(
			NewsDao::COLUMN_NAME => $values->name,
			NewsDao::COLUMN_TEXT => $values->text,
			NewsDao::COLUMN_RELEASE => isset($values->release) ? 1 : 0
		));

		if (isset($values->release)) {
			$this->releaseNew($this->new->id);
		}

		$presenter->flashMessage('Novinka byla upravena.');
		$presenter->redirect('News:');
	}

}
