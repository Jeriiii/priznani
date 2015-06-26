<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;
use POS\Model\CityDao;
use POS\Model\EnumBraSizeDao;
use POS\Model\EnumDrinkDao;
use POS\Model\EnumGraduationDao;
use POS\Model\EnumHairColourDao;
use POS\Model\EnumMaritalStateDao;
use POS\Model\EnumOrientationDao;
use POS\Model\EnumPenisWidthDao;
use POS\Model\EnumPropertyDao;
use POS\Model\EnumShapeDao;
use POS\Model\EnumSmokeDao;
use POS\Model\EnumTallnessDao;

/**
 * Formulář pro podrobné vyhledávání
 */
class AdvancedForm extends BaseForm {

	/** @var \POS\Model\CityDao */
	public $cityDao;

	/** @var \POS\Model\EnumBraSizeDao */
	public $enumBraSizeDao;

	/** @var \POS\Model\EnumGraduationDao */
	public $enumGraduationDao;

	/** @var \POS\Model\EnumHairColourDao */
	public $enumHairColourDao;

	/** @var \POS\Model\EnumMaritalStateDao */
	public $enumMaritalStateDao;

	/** @var \POS\Model\EnumOrientationDao */
	public $enumOrientationDao;

	/** @var \POS\Model\EnumPenisWidthDao */
	public $enumPenisWidthDao;

	/** @var \POS\Model\EnumPropertyDao */
	public $enumPropertyDao;

	/** @var \POS\Model\EnumShapeDao */
	public $enumShapeDao;

	/** @var \POS\Model\EnumSmokeDao */
	public $enumSmokeDao;

	/** @var \POS\Model\EnumTallnessDao */
	public $enumTallnessDao;

	public function __construct(EnumBraSizeDao $enumBraSizeDao, EnumGraduationDao $enumGraduationDao, EnumHairColourDao $enumHairColourDao, EnumMaritalStateDao $enumMaritalStateDao, EnumOrientationDao $enumOrientationDao, EnumPenisWidthDao $enumPenisWidthDao, EnumPropertyDao $enumPropertyDao, EnumShapeDao $enumShapeDao, EnumSmokeDao $enumSmokeDao, EnumTallnessDao $enumTallnessDao, CityDao $cityDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->cityDao = $cityDao;
		$this->enumBraSizeDao = $enumBraSizeDao;
		$this->enumGraduationDao = $enumGraduationDao;
		$this->enumHairColourDao = $enumHairColourDao;
		$this->enumMaritalStateDao = $enumMaritalStateDao;
		$this->enumOrientationDao = $enumOrientationDao;
		$this->enumPenisWidthDao = $enumPenisWidthDao;
		$this->enumPropertyDao = $enumPropertyDao;
		$this->enumShapeDao = $enumShapeDao;
		$this->enumSmokeDao = $enumSmokeDao;
		$this->enumTallnessDao = $enumTallnessDao;

		/* políčka s věkem */
		$this->addAgeFields();

		/* políčka o pohlaví */
		$this->addSexFields();

		/* políčka o údajích o tělu */
		$this->addBodyFields();

		/* políčka s návyky */
		//$this->addHabitsFields(); //zakomentováno v první verzi přiznání

		/* obecná políčka */
		$this->addGeneralFields();

		/* políčka o zájmu */
		$this->addIntrestedInFields();

		/* políčka se sexuálníma praktikama */
		//$this->addPracticsFields(); //zakomentováno v první verzi přiznání

		$this->manageSubmittedFormValues();

		$this->addSubmit('search', 'Vyhledat');

		$this->setBootstrapRender();
		$this->onValidate[] = callback($this, 'validateForm');
		$this->onSuccess[] = callback($this, 'advancedFormSubmitted');

		return $this;
	}

	public function validateForm($form) {
		$values = $form->getValues();

		if (!$this->testNumeric($values->age_from)) {
			$form->addError('Věk musí být kladné číslo.');
		}
		if (!$this->testNumeric($values->age_to)) {
			$form->addError('Věk musí být kladné číslo.');
		}
		if (!$this->testNumeric($values->penis_length_from)) {
			$form->addError('Delka penisu musí být kladné číslo.');
		}
		if (!$this->testNumeric($values->penis_length_to)) {
			$form->addError('Delka penisu musí být kladné číslo.');
		}
	}

	public function advancedFormSubmitted(AdvancedForm $form) {
		$values = $form->getValues();
		$this->values = $values;
		$presenter = $this->getPresenter();

		$presenter->redirect('Search:advanced', (array) $values);
	}

	/**
	 * upraví města do pole pro select
	 * @param string $dataRaw
	 * @return array
	 */
	private function getCityData($dataRaw) {
		$cityData = array();
		$cityData[''] = 'město';
		foreach ($dataRaw as $item) {
			$cityData[$item->id] = $item->city . " (Okres: " . $item->district . ")";
		}
		return $cityData;
	}

	/**
	 * upraví okresy do pole pro select
	 * @param string $dataRaw
	 * @return array
	 */
	private function getDistrictData($dataRaw) {
		$districtData = array();
		$districtData[''] = 'okres';
		foreach ($dataRaw as $item) {
			$districtData[$item->districtID] = $item->district . " (Kraj: " . $item->region . ")";
		}
		return $districtData;
	}

	/**
	 * upraví kraje do pole pro select
	 * @param string $dataRaw
	 * @return array
	 */
	private function getRegiontData($dataRaw) {
		$regionData = array();
		$regionData[''] = 'kraj';
		foreach ($dataRaw as $item) {
			$regionData[$item->regionID] = $item->region;
		}
		return $regionData;
	}

	/**
	 * Otestuje, zda zadaný řetězec je kladné číslo
	 * @param string $text řetězec, které testujeme
	 * @return boolean
	 */
	private function testNumeric($text) {
//regulární výraz, pro hledání mínusu v číselném řetězci
		$pattern = "/-/";

		if (!is_numeric($text) && $text != "") {
			return FALSE;
		}
		if (preg_match($pattern, $text)) {
			return FALSE;
		}
		return TRUE;
	}

	private function addAgeFields() {
		/* skupina pro políčka s věkem */
		$this->addGroup('Věk');

		$this->addText('age_from', 'od:')
			->setAttribute('class', 'smallColumn')
			->setAttribute('placeholder', 18);
		$this->addText('age_to', 'do:')
			->setAttribute('class', 'smallColumn');
	}

	/**
	 * funkce přidá sekci s údaji o pohlaví
	 */
	private function addSexFields() {
//skupina pro políčka s pohlavím a orientací
		$this->addGroup('Pohlaví a orientace');

		$sexType = $this->getSexTypeChoices();

		$penisWidthType = $this->getPenisWidthChoices();

		$braSize = $this->getBraSizeChoices();

		$orientationType = $this->getOrientationChoices();

		$this->addSelect('sex', 'pohlaví:', $sexType)
			->setAttribute('class', 'columnSelectWidth');

		$this->addText('penis_length_from', 'délka penisu:')
			->setAttribute('placeholder', 'od(cm)')
			->setAttribute('class', 'middleColumn');

		$this->addText('penis_length_to', '')
			->setAttribute('placeholder', 'do(cm)')
			->setAttribute('class', 'middleColumn');

		$this->addSelect('penis_width', 'obvod penisu: ', $penisWidthType)
			->setAttribute('class', 'columnSelectWidth');

		$this->addSelect('bra_size', 'velikost prsou:', $braSize)
			->setAttribute('class', 'columnSelectWidth');

		$this->addSelect('orientation', 'Sexuální orientace:', $orientationType)
			->setAttribute('class', 'columnSelectWidth');

		$state = $this->getStateChoices();

		$this->addSelect('marital_state', 'stav:', $state)
			->setAttribute('class', 'columnSelectWidth');
	}

	/**
	 * funkce přidá sekci s údaji o těle
	 */
	private function addBodyFields() {
		/* skupina pro políčka s tělem */
		$this->addGroup('Tělo');

		$shapeTypes = $this->getShapeChoices();

		$hairColor = $this->getHairColorChoices();

		$tallness_to = $this->getTallnessChoices("to");

		$tallness_from = $this->getTallnessChoices("from");

		$this->addSelect('shape', 'postava:', $shapeTypes)
			->setAttribute('class', 'columnSelectWidth');
		$this->addSelect('hair_color', 'barva vlasů:', $hairColor)
			->setAttribute('class', 'columnWidth');
		$this->addSelect('tallness_from', 'výška:', $tallness_from)
			->setAttribute('class', 'columnTallness');
		$this->addSelect('tallness_to', '', $tallness_to)
			->setAttribute('class', 'columnTallness');
	}

	/**
	 * funkce přidá sekci s údaji o návycích
	 */
	private function addHabitsFields() {
		/* skupina pro políčka s návyky */
		$this->addGroup('Návyky');

		$smokes = $this->getSmokes();
		$drinks = $this->getDrinks();

		$this->addSelect('drink', 'pití:', $drinks)
			->setAttribute('class', 'columnSelectWidth');
		$this->addSelect('smoke', 'kouření:', $smokes)
			->setAttribute('class', 'columnSelectWidth');
	}

	/**
	 * funkce přidá sekci s obecnými údaji
	 */
	private function addGeneralFields() {
//skupina pro obecná políčka(vzdělání a bydliště)
		$this->addGroup('Obecné');

//		$gra = $this->getGraduationChoices();
//
//		$this->addSelect('graduation', 'vzdělání:', $gra) //zakomentováno v první verzi přiznání
//			->setAttribute('class', 'columnSelectWidth');

		$dataRaw = $this->cityDao->getCitiesData();
		$cityData = $this->getCityData($dataRaw);
		$districtData = $this->getDistrictData($dataRaw);
		$regionData = $this->getRegiontData($dataRaw);

		$this->addGroup('Bydliště');
		$this->addSelect('city', 'bydliště:', $cityData)
			->setAttribute('class', 'columnSelectWidth');

		$this->addSelect('district', '', $districtData)
			->setAttribute('class', 'columnSelectWidth');

		$this->addSelect('region', '', $regionData)
			->setAttribute('class', 'columnSelectWidth');
	}

	/**
	 * funkce přidá sekci s údaji o zájmech
	 */
	private function addIntrestedInFields() {
		/* skupina pro políčka se zájmy koho potkat */
		$this->addGroup('Chce potkat');

		$this->addCheckbox('men', 'muži');
		$this->addCheckbox('women', 'ženy');
		$this->addCheckbox('couple', 'pár');
		$this->addCheckbox('men_couple', 'pár mužů');
		$this->addCheckbox('women_couple', 'pár žen');
		$this->addCheckbox('more', 'skupina');
	}

	/**
	 * funkce přidá sekci s údaji o praktikách
	 */
	private function addPracticsFields() {
		/* skupina pro políčka se sexuálními praktikami */
		$this->addGroup('Sexuální praktiky');

		$this->addCheckbox('threesome', 'trojka');
		$this->addCheckbox('anal', 'anál');
		$this->addCheckbox('group', 'skupinový sex');
		$this->addCheckbox('bdsm', 'BDSM');
		$this->addCheckbox('swallow', 'polykání');
		$this->addCheckbox('cum', 'ejakulace');
		$this->addCheckbox('oral', 'orál');
		$this->addCheckbox('piss', 'pissing');
		$this->addCheckbox('sex_massage', 'sexuální masáže');
		$this->addCheckbox('petting', 'petting');
		$this->addCheckbox('fisting', 'fisting');
		$this->addCheckbox('deepthroat', 'deepthroat');
	}

	/**
	 * Obstará vyplnění hodnot do formuláře po odeslání
	 */
	private function manageSubmittedFormValues() {
		$parameters = $this->getPresenter()->getParameters();

		$this->setDefaults($parameters);
	}

	/**
	 * připraví data z databáze pro možnosti pohlaví
	 * @return array
	 */
	private function getSexTypeChoices() {
		$sex = array("" => "--------");
		$sex = $sex + $this->enumPropertyDao->getList();

		return $sex;
	}

	/**
	 * připraví data z databáze pro možnosti šířky penisu
	 * @return array
	 */
	private function getPenisWidthChoices() {
		$widths = array("" => "--------");
		$widths = $widths + $this->enumPenisWidthDao->getList();

		return $widths;
	}

	/**
	 * připraví data z databáze pro možnosti velikosti prsou
	 * @return array
	 */
	private function getBraSizeChoices() {
		$sizes = array("" => "--------");
		$sizes = $sizes + $this->enumBraSizeDao->getList();

		return $sizes;
	}

	/**
	 * připraví data z databáze pro možnosti sexuální orientace
	 * @return array
	 */
	private function getOrientationChoices() {
		$orientations = array("" => "--------");
		$orientations = $orientations + $this->enumOrientationDao->getList();

		return $orientations;
	}

	/**
	 * připraví data z databáze pro možnosti tvaru těla
	 * @return array
	 */
	private function getShapeChoices() {
		$shapes = array("" => "--------");
		$shapes = $shapes + $this->enumShapeDao->getList();

		return $shapes;
	}

	/**
	 * připraví data z databáze pro možnosti barvy vlasů
	 * @return array
	 */
	private function getHairColorChoices() {
		$hairColors = array("" => "--------");
		$hairColors = $hairColors + $this->enumHairColourDao->getList();

		return $hairColors;
	}

	/**
	 * připraví data z databáze pro možnosti výsky
	 * @return array
	 */
	private function getTallnessChoices($type) {
		if ($type == "from") {
			$tallness = array("" => "od(cm)");
		} else {
			$tallness = array("" => "do(cm)");
		}

		$tallness = $tallness + $this->enumTallnessDao->getList();
		return $tallness;
	}

	/**
	 * připraví data z databáze pro možnosti zvyků
	 * @return array
	 */
	private function getSmokes() {
		$smokes = array("" => "--------");
		$smokes = $smokes + $this->enumSmokeDao->getList();

		return $smokes;
	}

	public function getDrinks() {
		$drinks = array("" => "--------");
		$drinks = $drinks + $this->enumSmokeDao->getList();

		return $drinks;
	}

	/**
	 * připraví data z databáze pro možnosti vzdělání
	 * @return array
	 */
	private function getGraduationChoices() {
		$graduations = array("" => "--------");
		$graduations = $graduations + $this->enumGraduationDao->getList();

		return $graduations;
	}

	/**
	 * připraví data z databáze pro možnosti stavu
	 * @return array
	 */
	private function getStateChoices() {
		$states = array("" => "--------");
		$states = $states + $this->enumMaritalStateDao->getList();

		return $states;
	}

}
