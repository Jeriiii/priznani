<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use POS\Model\UserDao;
use Nette\ArrayHash;
use Nette\Http\SessionSection;

/*
 * Zaregistruje muže, ženu či celý pár.
 */

class DatingRegistrationThirdForm extends DatingRegistrationBaseForm {

	/** @var \POS\Model\UserDao */
	protected $userDao;

	/** @var int Typ uživatele */
	protected $type;

	/** @var \Nette\Http\SessionSection */
	private $regSession;

	/** @var \Nette\Http\SessionSection */
	private $regCoupleSession;

	/** prefix k roznání, že jde o druhého uživatele z páru */
	const SECOND_MAN_SUFFIX = "Second";

	public function __construct(UserDao $userDao, $regSession = NULL, $regCoupleSession = NULL, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->userDao = $userDao;
		$type = $regSession->type;
		$this->type = $type;
		$this->regSession = $regSession;
		$this->regCoupleSession = $regCoupleSession;

		/* first person */
		$this->addFirstPerson($type);

		/* second person */
		if (self::isCouple($type)) {
			$this->addSecondPerson($type);
		}

		$this->onSuccess[] = callback($this, 'submitted');
		$submitBtn = $this->addSubmit('send', 'Dokončit registraci');
		$submitBtn->setAttribute("class", "btn btn-main");
		$this->setBootstrapRender();
	}

	public function submitted($form) {
		$values = $form->values;
		$presenter = $this->getPresenter();

		$this->setFirstPersonData($this->type, $this->regSession, $values);

		if (self::isCouple($this->type)) {
			$this->setSecondPersonData($this->type, $this->regCoupleSession, $values);
		}

		$presenter->redirect('Datingregistration:register');
	}

	/**
	 * Uloží data do sečny
	 * @param \Nette\ArrayHash $values
	 * @param type $suffix
	 */
	protected function setBaseData($section, ArrayHash $values, $suffix = "") {
		$section->marital_state = $values->offsetGet("marital_state" . $suffix);
		$section->orientation = $values->offsetGet("orientation" . $suffix);
		$section->tallness = $values->offsetGet("tallness" . $suffix);
		$section->shape = $values->offsetGet("shape" . $suffix);
	}

	/**
	 * Uloží data o ženě do sečny
	 * @param \Nette\ArrayHash $values
	 * @param type $suffix
	 */
	protected function setWomanData($section, ArrayHash $values, $suffix = "") {
		$section->bra_size = $values->offsetGet("bra_size" . $suffix);
		$section->hair_colour = $values->offsetGet("hair_colour" . $suffix);
		$section->penis_length = "";
		$section->penis_width = "";
	}

	/**
	 * Uloží data o muži do sečny
	 * @param \Nette\ArrayHash $values
	 * @param type $suffix
	 */
	protected function setManData($section, ArrayHash $values, $suffix = "") {
		$section->bra_size = "";
		$section->hair_colour = "";
		$section->penis_length = $values->offsetGet("penis_length" . $suffix);
		$section->penis_width = $values->offsetGet("penis_width" . $suffix);
	}

	/**
	 * Uloží data o prvním uživateli.
	 * @param int $type Typ uživatele.
	 */
	protected function setFirstPersonData($type, $section, ArrayHash $values) {
		if (self::isFirstWoman($type)) {
			$this->setWomanData($section, $values);
		}
		if (self::isFirstMan($type)) {
			$this->setManData($section, $values);
		}
		$this->setBaseData($section, $values);
	}

	/**
	 * Uloží data o druhém uživateli z páru.
	 * @param int $type Typ uživatele.
	 */
	protected function setSecondPersonData($type, $section, ArrayHash $values) {
		$section->vigor = $this->getVigor($section->age);

		if (self::isSecondWoman($type)) {
			$this->setWomanData($section, $values, self::SECOND_MAN_SUFFIX);
		}
		if (self::isSecondMan($type)) {
			$this->setManData($section, $values, self::SECOND_MAN_SUFFIX);
		}
		$this->setBaseData($section, $values, self::SECOND_MAN_SUFFIX);
	}

	/**
	 * Přidá inputy pro prvního uživatele.
	 * @param int $type Typ uživatele.
	 */
	public function addFirstPerson($type) {
		$this->addGroup(self::getFirstManName($type));
		if (self::isFirstWoman($type)) {
			$this->addWoman();
		}
		if (self::isFirstMan($type)) {
			$this->addMan();
		}
		$this->addBaseSelect();
	}

	/**
	 * Přidá inputy pro prvního uživatele.
	 * @param int $type Typ uživatele.
	 */
	public function addSecondPerson($type) {
		$this->addGroup(self::getSecondManName($type));
		if (self::isSecondWoman($type)) {
			$this->addWoman(self::SECOND_MAN_SUFFIX);
		}
		if (self::isSecondMan($type)) {
			$this->addMan(self::SECOND_MAN_SUFFIX);
		}
		$this->addBaseSelect(self::SECOND_MAN_SUFFIX);
	}

	/**
	 * Vrátí název prvního z registrovaných
	 * @param int $type Typ uživatele.
	 * @return string Název prvního z registrovaných
	 */
	public static function getFirstManName($type) {
		if ($type == UserDao::PROPERTY_COUPLE || $type == UserDao::PROPERTY_COUPLE_WOMAN) {
			return "Partnerka";
		}
		if ($type == UserDao::PROPERTY_COUPLE_MAN) {
			return "Partner";
		}
		return "";
	}

	/**
	 * Vrátí název druhého z registrovaných
	 * @param int $type Typ uživatele.
	 * @return string Název druhého z registrovaných
	 */
	public static function getSecondManName($type) {
		if ($type == UserDao::PROPERTY_COUPLE_WOMAN) {
			return "Partnerka";
		}
		if ($type == UserDao::PROPERTY_COUPLE || $type == UserDao::PROPERTY_COUPLE_MAN) {
			return "Partner";
		}
		return "";
	}

	protected function addBaseSelect($suffix = "") {
		$this->addSelect('marital_state' . $suffix, 'Stav:', $this->userDao->getUserStateOption())
			->setPrompt("- vyberte -")
			->addRule(Form::FILLED, "Vyberte Váš stav");
		$this->addSelect('orientation' . $suffix, 'Orientace:', $this->userDao->getUserOrientationOption())
			->setPrompt("- vyberte -")
			->addRule(Form::FILLED, "Vyberte váší orientaci");
		$this->addSelect('tallness' . $suffix, 'Výška:', $this->userDao->getUserTallnessOption())
			->setPrompt("- vyberte -")
			->addRule(Form::FILLED, "Vyberte váši výšku");
		$this->addSelect('shape' . $suffix, 'Postava:', $this->userDao->getUserShapeOption())
			->setPrompt("- vyberte -")
			->addRule(Form::FILLED, "Vyberte váší postavu");
	}

	/**
	 * Přidá specifické inputy pro ženu.
	 * @param string $suffix Rozlišuje jestli jde o prvního nebo o druhého (druhého z páru) uživatele
	 */
	protected function addWoman($suffix = "") {
		$this->addSelect('bra_size' . $suffix, 'Velikost košíčků:', $this->userDao->getUserBraSizeOption());

		$this->addSelect('hair_colour' . $suffix, 'Barva vlasů:', $this->userDao->getUserHairs())
			->setPrompt("- vyberte -")
			->addRule(Form::FILLED, "Vyberte prosím barvu vlasů");
	}

	/**
	 * Přidá specifické inputy pro muže.
	 * @param string $suffix Rozlišuje jestli jde o prvního nebo o druhého (druhého z páru) uživatele
	 */
	protected function addMan($suffix = "") {
		$this->addText('penis_length' . $suffix, 'Délka penisu:')
			->setType('number')
			->addRule(Form::INTEGER, 'Délka musí být číslo.')
			->addRule(Form::RANGE, 'Délka je mezi 2 - 40 cm', array(2, 40));

		$this->addSelect('penis_width' . $suffix, 'Obvod penisu:', $this->userDao->getUserPenisWidthOption());
	}

	/**
	 * Pokud je uživatel nějaký druh páru, vrátí TRUE
	 * @param int $type Typ uživatele.
	 * @return boolean TRUE pokud jde o pár, jinak FALSE
	 */
	public static function isCouple($type) {
		if ($type == UserDao::PROPERTY_COUPLE || $type == UserDao::PROPERTY_COUPLE_MAN || $type == UserDao::PROPERTY_COUPLE_WOMAN) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Pokud je první z páru žena | nebo jde o ženu, vrátí TRUE
	 * @param int $type Typ uživatele.
	 * @return boolean TRUE pokud je první z páru žena | nebo jde o ženu
	 */
	public static function isFirstWoman($type) {
		if ($type == UserDao::PROPERTY_COUPLE || $type == UserDao::PROPERTY_WOMAN || $type == UserDao::PROPERTY_COUPLE_WOMAN) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Pokud je první z páru muž | nebo jde o muže, vrátí TRUE
	 * @param int $type Typ uživatele.
	 * @return boolean TRUE pokud je první z páru muž | nebo jde o muže
	 */
	public static function isFirstMan($type) {
		if ($type == UserDao::PROPERTY_MAN || $type == UserDao::PROPERTY_COUPLE_MAN) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Pokud je druhý z páru žena | nebo jde o ženu, vrátí TRUE
	 * @param int $type Typ uživatele.
	 * @return boolean TRUE pokud je druhý z páru žena | nebo jde o ženu
	 */
	public static function isSecondWoman($type) {
		if ($type == UserDao::PROPERTY_COUPLE_WOMAN) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Pokud je druhý z páru muž | nebo jde o muže, vrátí TRUE
	 * @param int $type Typ uživatele.
	 * @return boolean TRUE pokud je druhý z páru muž | nebo jde o muže
	 */
	public static function isSecondMan($type) {
		if ($type == UserDao::PROPERTY_COUPLE || $type == UserDao::PROPERTY_COUPLE_MAN) {
			return TRUE;
		}
		return FALSE;
	}

}
