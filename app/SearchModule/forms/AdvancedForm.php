<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;
use POS\Model\CityDao;

/**
 * Formulář pro podrobné vyhledávání
 */
class AdvancedForm extends BaseForm {

	/**
	 * @var \POS\Model\CityDao
	 */
	public $cityDao;
	private $values;

	public function __construct(CityDao $cityDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->cityDao = $cityDao;

		//políčka s věkem
		$this->addAgeFields();

		//políčka o pohlaví
		$this->addSexFields();

		//políčka o údajích o tělu
		$this->addBodyFields();

		//políčka s návyky
		$this->addHabitsFields();

		//obecná políčka
		$this->addGeneralFields();

		//políčka o zájmu
		$this->addIntrestedInFields();

		//políčka se sexuálníma praktikama
		$this->addPracticsFields();

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
		//skupina pro políčka s věkem
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

		$sexType = array(
			'' => '--------',
			'1' => 'muž',
			'2' => 'žena',
			'3' => 'pár',
			'4' => 'pár mužů',
			'5' => 'pár žen',
			'6' => 'skupina'
		);

		$penisWidthType = array(
			'' => '--------',
			'1' => 'hubený',
			'2' => 'střední',
			'3' => 'tlustý',
		);

		$braSize = array(
			'' => '--------',
			'1' => 'A',
			'2' => 'B',
			'3' => 'C',
			'4' => 'D',
			'5' => 'E',
			'6' => 'F',
		);

		$orientationType = array(
			'' => '--------',
			'1' => 'hetero',
			'2' => 'homo',
			'3' => 'bi',
			'4' => 'bi - chtěl bych zkusit',
		);

		$this->addSelect('sex', 'pohlaví:', $sexType)
			->setAttribute('class', 'columnSelectWidth');

		$this->addText('penis_length_from', 'délka penisu:')
			->setAttribute('placeholder', 'od(cm)')
			->setAttribute('class', 'middleColumn');

		$this->addText('penis_length_to', '')
			->setAttribute('placeholder', 'do(cm)')
			->setAttribute('class', 'middleColumn');

		$this->addSelect('penis_width', 'šířka penisu: ', $penisWidthType)
			->setAttribute('class', 'columnSelectWidth');

		$this->addSelect('bra_size', 'velikost prsou:', $braSize)
			->setAttribute('class', 'columnSelectWidth');

		$this->addSelect('orientation', 'Sexuální orientace:', $orientationType)
			->setAttribute('class', 'columnSelectWidth');
	}

	/**
	 * funkce přidá sekci s údaji o těle
	 */
	private function addBodyFields() {
		//skupina pro políčka s tělem
		$this->addGroup('Tělo');

		$shapeTypes = array(
			'' => '--------',
			'1' => 'hubená',
			'2' => 'štíhlá',
			'3' => 'normální',
			'4' => 'atletická',
			'5' => 'plnoštíhlá',
			'6' => 'při těle',
		);

		$hairColor = array(
			'' => '--------',
			'1' => 'blond',
			'2' => 'hnědá',
			'3' => 'zrzavá',
			'4' => 'černá',
			'5' => 'jiná',
		);

		$tallness_from = array(
			'' => 'od(cm)',
			'1' => 160,
			'2' => 170,
			'3' => 180,
			'4' => 190,
			'5' => 200
		);

		$tallness_to = array(
			'' => 'do(cm)',
			'1' => 160,
			'2' => 170,
			'3' => 180,
			'4' => 190,
			'5' => 200
		);

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
		//skupina pro políčka s návyky
		$this->addGroup('Návyky');

		$drink = array(
			'' => '--------',
			'1' => 'často',
			'2' => 'ne',
			'3' => 'příležitostně',
		);

		$this->addSelect('drink', 'pití:', $drink)
			->setAttribute('class', 'columnSelectWidth');
		$this->addSelect('smoke', 'kouření:', $drink)
			->setAttribute('class', 'columnSelectWidth');
	}

	/**
	 * funkce přidá sekci s obecnými údaji
	 */
	private function addGeneralFields() {
		//skupina pro obecná políčka(vzdělání a bydliště)
		$this->addGroup('Obecné');

		$gra = array(
			'' => '--------',
			'1' => 'základní',
			'2' => 'vyučen/a',
			'3' => 'střední',
			'4' => 'vyšší odborné',
			'5' => 'vysoké',
		);

		$state = array(
			'' => '--------',
			'1' => 'volný',
			'2' => 'ženatý/vdaná',
			'3' => 'rozvedený/á',
			'4' => 'oddělený/á',
			'5' => 'vdovec/vdova',
			'6' => 'zadaný/á',
		);

		$this->addSelect('marital_state', 'stav:', $state)
			->setAttribute('class', 'columnSelectWidth');

		$this->addSelect('graduation', 'vzdělání:', $gra)
			->setAttribute('class', 'columnSelectWidth');

		$dataRaw = $this->cityDao->getCitiesData();
		$cityData = $this->getCityData($dataRaw);
		$districtData = $this->getDistrictData($dataRaw);
		$regionData = $this->getRegiontData($dataRaw);

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
		//skupina pro políčka se zájmy koho potkat
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
		//skupina pro políčka se sexuálními praktikami
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

}
