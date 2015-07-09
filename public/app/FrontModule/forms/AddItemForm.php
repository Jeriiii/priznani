<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use POS\Model\AdviceDao;
use POS\Model\ConfessionDao;
use POS\Model\PartyDao;

/**
 * Základní formulář, který se nevyužívá sám, ale vždy jako rodič.
 * Připravuje metody pro vložení přiznání a poradny.
 */
class AddItemForm extends BaseForm {

	private $tableName = "confession";

	/**
	 * @var \POS\Model\AdviceDao
	 */
	public $adviceDao;

	/**
	 * @var \POS\Model\ConfessionDao
	 */
	public $confessionDao;

	/**
	 * @var \POS\Model\PartyDao
	 */
	public $partyDao;

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		if ($this->testMode == FALSE) {
			$this->addAntispam();
		}

		$this->addTextarea("note", "Přiznání", 30, 30, 1)
			->addRule(Form::FILLED, "Vyplňte prosím text přiznání.")
			->addRule(Form::MAX_LENGTH, "Status je příliš dlouhý.", 65500);
		/* ochrana proti spamu */
		$this->addText('nick', 'Toto pole prosím nevyplňujte.');
		$this->addSubmit("submit", "Odeslat");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function setAdviceDao(AdviceDao $adviceDao) {
		$this->adviceDao = $adviceDao;
	}

	public function setPartyDao(PartyDao $partyDao) {
		$this->partyDao = $partyDao;
	}

	public function setConfession(ConfessionDao $confDao) {
		$this->confessionDao = $confDao;
	}

	/*
	 * ochrana proti spamu a proti odeslani priznani znovu
	 */

	public function protection($values, $presenter, $template) {
		if ($this->tableName == "date")
			$exist_confession = FALSE;
		else
			$exist_confession = $this->getDao()
				->existConnectionLikeText($values->note);

		if (!empty($values->nick)) {
			$presenter->flashMessage('Toto je ochrana proti robotovi. Zkuste to prosím znovu. Jestli problem pretrvava obratte se prosim na spravce.');
			$presenter->redirect("this");
		}
		if ($exist_confession) {
			$confession = $this->getDao()
				->getConnectionLikeText($values->note);
			$presenter->flashMessage('Přiznání s tímto textem již existuje. Můžete ho sledovat na tomto odkaze.');
			$presenter->redirect('Page:' . $template, $confession->id);
		}

		unset($values["nick"]);
		unset($values['spam']);

		return $values;
	}

	public function submitted($form) {
		$template = "confession";
		$presenter = $this->getPresenter();
		$values = $this->protection($form->values, $presenter, $template);

		$values["create"] = new \Nette\DateTime;

		/* u inzerátů seznamky se ještě ukládá uživatel, který inzerát vložil */
		if ($this->tableName == "date") {
			$insertData = array(
				"create" => $values["create"],
				"note" => $values->note,
				"userID" => $presenter->getUser()->id
			);
		} else {
			$insertData = array(
				"create" => $values["create"],
				"note" => $values->note,
			);
		}

		$id = $this->getDao()
				->insert($insertData)
			->id;

		$presenter->flashMessage('Přiznání bylo vytvořeno, na této adrese můžete sledovat STAV svého přiznání.');
		if ($this->tableName == "date")
			$presenter->redirect("this");
		$presenter->redirect('Page:' . $template, $id);
	}

	/**
	 * Vrátí nastavenné dao
	 * @return Právě nastavené dao
	 * @throws Exception V potomkovi není nastavenné dao
	 */
	public function getDao() {
		if (isset($this->confessionDao)) {
			return $this->confessionDao;
		} elseif ($this->adviceDao) {
			return $this->adviceDao;
		} elseif ($this->partyDao) {
			return $this->partyDao;
		} else {
			throw new Exception("Dao must be set.");
		}
	}

}
