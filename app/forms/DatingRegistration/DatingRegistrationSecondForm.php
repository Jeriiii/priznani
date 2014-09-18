<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;
use POS\Model\UserDao;
use POS\Model\CityDao;
use POS\Model\DistrictDao;
use POS\Model\RegionDao;
use Nette\Http\SessionSection;

class DatingRegistrationSecondForm extends DatingRegistrationBaseForm {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/**
	 * @var \POS\Model\CityDao
	 */
	public $cityDao;

	/**
	 * @var \POS\Model\DistrictDao
	 */
	public $districtDao;

	/**
	 * @var \POS\Model\RegionDao
	 */
	public $regionDao;

	/** @var \Nette\Http\SessionSection */
	private $regSession;
	private $cityID;
	private $districtID;
	private $regionID;

	public function __construct(RegionDao $regionDao, DistrictDao $districtDao, CityDao $cityDao, UserDao $userDao, IContainer $parent = NULL, $name = NULL, SessionSection $regSession = NULL) {
		parent::__construct($parent, $name);
		$this->userDao = $userDao;
		$this->regSession = $regSession;
		$this->cityDao = $cityDao;
		$this->districtDao = $districtDao;
		$this->regionDao = $regionDao;

		$this->addText('email', 'Email')
			->addRule(Form::FILLED, 'Email není vyplněn.')
			->addRule(Form::EMAIL, 'Vyplněný email není platného formátu.')
			->addRule(Form::MAX_LENGTH, 'Email je příliž dlouhý.', 50);
		$this->addText('user_name', 'Uživatelské jméno')
			->addRule(Form::FILLED, 'Uživatelské jméno není vyplněno')
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"Uživatelské jméno\" je 100 znaků.', 20);
		$this->addPassword('password', 'Heslo')
			->addRule(Form::FILLED, 'Heslo není vyplněno.')
			->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 4)
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"Heslo\" je 100 znaků.', 100);
		$this->addPassword('passwordVerify', 'Heslo pro kontrolu:')
			->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
			->addRule(Form::EQUAL, 'Hesla se neshodují', $this['password']);
		$this->addText('first_sentence', 'Úvodní věta (max 100 znaků)')
			->addRule(Form::FILLED, 'Úvodní věta není vyplněna.')
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"Úvodní věta\" je 100 znaků.', 100);
		$this->addTextArea('about_me', 'O mě (max 300 znaků)', 40, 10)
			->addRule(Form::FILLED, 'O mě není vyplněno.')
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"O mě\" je 300 znaků.', 300);
		$this->addText('city', 'Bydlím v:')
			->addRule(Form::FILLED, 'Město není vyplněno.');

		if (isset($regSession)) {
			$this->setDefaults(array(
				'email' => $regSession->email,
				'user_name' => $regSession->user_name,
				'first_sentence' => $regSession->first_sentence,
				'about_me' => $regSession->about_me
			));
		}

		$this->onSuccess[] = callback($this, 'submitted');
		$this->onValidate[] = callback($this, "uniqueUserName");
		$this->onValidate[] = callback($this, "uniqueEmail");
		$this->onValidate[] = callback($this, "existingCity");
		$this->addSubmit('send', 'Do třetí části registrace')
			->setAttribute("class", "btn btn-success");

		return $this;
	}

	public function submitted($form) {
		$values = $form->values;
		$presenter = $this->getPresenter();

		$authenticator = $presenter->context->authenticator;
		$pass = $authenticator->calculateHash($values->password);

		$this->regSession->email = $values->email;
		$this->regSession->user_name = $values->user_name;
		$this->regSession->password = $values->password;
		$this->regSession->passwordHash = $pass;
		$this->regSession->first_sentence = $values->first_sentence;
		$this->regSession->about_me = $values->about_me;
		$this->regSession->cityID = $this->cityID;
		$this->regSession->districtID = $this->districtID;
		$this->regSession->regionID = $this->regionID;

		$presenter->redirect('Datingregistration:PreThirdRegForm');
	}

	/**
	 * Zkontroluje, zda je user_name unikátní
	 * @param Nette\Application\UI\Form $form
	 */
	public function uniqueUserName($form) {
		$values = $form->values;

		$user_name = $this->userDao->findByUserName($values->user_name);
		if ($user_name) {
			$form->addError('Toto jméno je již obsazeno.');
		}
	}

	/**
	 * Zkontroluje, zda je email unikátní
	 * @param Nette\Application\UI\Form $form
	 */
	public function uniqueEmail($form) {
		$values = $form->values;

		$email = $this->userDao->findByEmail($values->email);
		if ($email) {
			$form->addError('Tento mail již někdo používá.');
		}
	}

	/**
	 * Zkontorluje, zda dané město je v databázi, pokud ano uloží do glob.proměnných
	 * @param type Nette\Application\UI\Form $form
	 * @return
	 */
	public function existingCity($form) {
		$values = $form->getValues();

		$data = explode(", ", $values->city);


		if (sizeof($data) < 3) {
			$form->addError('Neúplná data o městu(město, okres, kraj)');
			return;
		}

		$region = $this->regionDao->findByName($data[2]);
		if (!$region) {
			$form->addError('Omlouváme se, toto město není v naší databázi. Prosím, vyberte ze seznamu větší město ve vašem okolí.');
			return;
		}

		$district = $this->districtDao->findByNameAndRegionID($data[1], $region->id);
		if (!$district) {
			$form->addError('Omlouváme se, toto město není v naší databázi. Prosím, vyberte ze seznamu větší město ve vašem okolí.');
			return;
		}

		$city = $this->cityDao->findByNameAndDistrictID($data[0], $district->id);
		if (!$city) {
			$form->addError('Omlouváme se, toto město není v naší databázi. Prosím, vyberte ze seznamu větší město ve vašem okolí.');
			return;
		}

		$this->regionID = $region->id;
		$this->districtID = $district->id;
		$this->cityID = $city->id;
	}

}
