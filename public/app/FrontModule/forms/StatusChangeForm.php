<?php

namespace Nette\Application\UI\Form;

/**
 * Změna statusu uživatele.
 */
use Nette\Database\Table\ActiveRow;
use POS\Model\EnumStatusDao;

class StatusChangeForm extends BaseForm {

	/** @var int ActiveRow Aktivní řádek co je potřeba změnit */
	private $userProperty;

	public function __construct(ActiveRow $userProperty, EnumStatusDao $enumStatusDao, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->userProperty = $userProperty;
		$enumStatus = $enumStatusDao->getAll()->fetchPairs("id", "name");
		$status = $this->addSelect("statusID", "Chci", $enumStatus)
			->setPrompt("Vyberte si status");

		if ($userProperty->statusID !== NULL) {
			$status->setDefaultValue($userProperty->statusID);
		}

		$this->addSubmit('send', 'Změnit');
		$this->onSuccess[] = callback($this, 'submitted');
		$this->setBootstrapRender();
		return $this;
	}

	public function submitted(StatusChangeForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$this->userProperty->update($values);

		$presenter->flashMessage('Status byl změněn');
		$presenter->redirect('this');
	}

}
