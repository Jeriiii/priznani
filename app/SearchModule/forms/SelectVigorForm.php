<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use Nette\Forms\Form;
use POS\Model\EnumVigorDao;

/**
 * Popis formuláře
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class SelectVigorForm extends BaseForm {

	public function __construct(EnumVigorDao $enumVigorDao, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$vigors = $enumVigorDao->getAll()->fetchPairs(EnumVigorDao::COLUMN_ID, EnumVigorDao::COLUMN_NAME);
		$this->addSelect("vigor", "Znamení:", $vigors)
			->setPrompt("Vyberte znamení");

		$this->addSubmit('send', 'Hledat');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(SelectVigorForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$presenter->redirect('this', $values->vigor);
	}

}
