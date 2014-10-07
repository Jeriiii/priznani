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

	public function __construct(CityDao $cityDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->cityDao = $cityDao;

		//skupina pro políčka s věkem
		$age = $this->addGroup('Věk');

		$ageFrom = $this->addText('age_from', 'od:')
			->setAttribute('class', 'smallColumn')
			->setAttribute('placeholder', 18);
		$ageTo = $this->addText('age_to', 'do:')
			->setAttribute('class', 'smallColumn');

		//přidání políček do skupiny
		$age->add($ageFrom);
		$age->add($ageTo);


		//skupina pro políčka s pohlavím a orientací
		$sex = $this->addGroup('Pohlaví a orientace');

		$sexType = array(
			'' => '--------',
			'1' => 'muž',
			'2' => 'žena',
			'3' => 'pár',
			'4' => 'pár mužů',
			'5' => 'pár žen',
			'6' => 'skupina'
		);

		$sexChoices = $this->addSelect('sex', 'pohlaví:', $sexType)
			->setAttribute('class', 'columnSelectWidth');

		$penisLengthFrom = $this->addText('penis_length_from', 'délka penisu:')
			->setAttribute('placeholder', 'od(cm)')
			->setAttribute('class', 'middleColumn');

		$penisLengthTo = $this->addText('penis_length_to', '')
			->setAttribute('placeholder', 'do(cm)')
			->setAttribute('class', 'middleColumn');

		$penisWidthType = array(
			'' => '--------',
			'1' => 'hubený',
			'2' => 'střední',
			'3' => 'tlustý',
		);

		$penisWidth = $this->addSelect('penis_width', 'šířka penisu: ', $penisWidthType)
			->setAttribute('class', 'columnSelectWidth');

		$braSize = array(
			'' => '--------',
			'1' => 'A',
			'2' => 'B',
			'3' => 'C',
			'4' => 'D',
			'5' => 'E',
			'6' => 'F',
		);

		$bra = $this->addSelect('bra_size', 'velikost prsou:', $braSize)
			->setAttribute('class', 'columnSelectWidth');

		$orientationType = array(
			'' => '--------',
			'1' => 'hetero',
			'2' => 'homo',
			'3' => 'bi',
			'4' => 'bi - chtěl bych zkusit',
		);
		$orientation = $this->addSelect('orientation', 'Sexuální orientace:', $orientationType)
			->setAttribute('class', 'columnSelectWidth');

		//přidání políček do skupiny
		$sex->add($sexChoices);
		$sex->add($penisLengthFrom);
		$sex->add($penisLengthTo);
		$sex->add($penisWidth);
		$sex->add($bra);
		$sex->add($orientation);

		//skupina pro políčka s tělem
		$body = $this->addGroup('Tělo');

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

		$shape = $this->addSelect('shape', 'postava:', $shapeTypes)
			->setAttribute('class', 'columnSelectWidth');
		$hair = $this->addSelect('hair_color', 'barva vlasů:', $hairColor)
			->setAttribute('class', 'columnWidth');
		$heightFrom = $this->addSelect('tallness_from', 'výška:', $tallness_from)
			->setAttribute('class', 'columnTallness');
		$heightTo = $this->addSelect('tallness_to', '', $tallness_to)
			->setAttribute('class', 'columnTallness');

		//přidání do skupiny
		$body->add($shape);
		$body->add($hair);
		$body->add($heightFrom);
		$body->add($heightTo);

		//skupina pro políčka s návyky
		$habits = $this->addGroup('Návyky');

		$drink = array(
			'' => '--------',
			'1' => 'často',
			'2' => 'ne',
			'3' => 'příležitostně',
		);

		$drinking = $this->addSelect('drink', 'pití:', $drink)
			->setAttribute('class', 'columnSelectWidth');
		$smoking = $this->addSelect('smoke', 'kouření:', $drink)
			->setAttribute('class', 'columnSelectWidth');

		//přidání do skupiny
		$habits->add($drinking);
		$habits->add($smoking);

		$gra = array(
			'' => '--------',
			'1' => 'základní',
			'2' => 'vyučen/a',
			'3' => 'střední',
			'4' => 'vyšší odborné',
			'5' => 'vysoké',
		);

		//skupina pro obecná políčka(vzdělání a bydliště)
		$general = $this->addGroup('Obecné');

		$state = array(
			'' => '--------',
			'1' => 'volný',
			'2' => 'ženatý/vdaná',
			'3' => 'rozvedený/á',
			'4' => 'oddělený/á',
			'5' => 'vdovec/vdova',
			'6' => 'zadaný/á',
		);

		$maritalState = $this->addSelect('marital_state', 'stav:', $state)
			->setAttribute('class', 'columnSelectWidth');

		$graduation = $this->addSelect('graduation', 'vzdělání:', $gra)
			->setAttribute('class', 'columnSelectWidth');

		$dataRaw = $this->cityDao->getCitiesData();
		$cityData = $this->getCityData($dataRaw);
		$districtData = $this->getDistrictData($dataRaw);
		$regionData = $this->getRegiontData($dataRaw);

		$city = $this->addSelect('city', 'bydliště:', $cityData)
			->setAttribute('class', 'columnSelectWidth');

		$district = $this->addSelect('district', '', $districtData)
			->setAttribute('class', 'columnSelectWidth');

		$region = $this->addSelect('region', '', $regionData)
			->setAttribute('class', 'columnSelectWidth');

		//přidání do skupiny
		$general->add($maritalState);
		$general->add($graduation);
		$general->add($city);
		$general->add($district);
		$general->add($region);

		//skupina pro políčka se zájmy koho potkat
		$intresetdIn = $this->addGroup('Chce potkat');

		$men = $this->addCheckbox('men', 'muži');
		$women = $this->addCheckbox('women', 'ženy');
		$couple = $this->addCheckbox('couple', 'pár');
		$coupleMen = $this->addCheckbox('men_couple', 'pár mužů');
		$coupleWomen = $this->addCheckbox('women_couple', 'pár žen');
		$more = $this->addCheckbox('more', 'skupina');

		//přidání do skupiny
		$intresetdIn->add($men);
		$intresetdIn->add($women);
		$intresetdIn->add($couple);
		$intresetdIn->add($coupleMen);
		$intresetdIn->add($coupleWomen);
		$intresetdIn->add($more);


		//skupina pro políčka se sexuálními praktikami
		$practics = $this->addGroup('Sexuální praktiky');

		$threesome = $this->addCheckbox('threesome', 'trojka');
		$anal = $this->addCheckbox('anal', 'anál');
		$group = $this->addCheckbox('group', 'skupinový sex');
		$bdsm = $this->addCheckbox('bdsm', 'BDSM');
		$swallow = $this->addCheckbox('swallow', 'polykání');
		$cum = $this->addCheckbox('cum', 'ejakulace');
		$oral = $this->addCheckbox('oral', 'orál');
		$piss = $this->addCheckbox('piss', 'pissing');
		$sexMassage = $this->addCheckbox('sex_massage', 'sexuální masáže');
		$petting = $this->addCheckbox('petting', 'petting');
		$fisting = $this->addCheckbox('fisting', 'fisting');
		$deepthroat = $this->addCheckbox('deepthroat', 'deepthroat');

		//přidání do skupiny
		$practics->add($threesome);
		$practics->add($anal);
		$practics->add($group);
		$practics->add($bdsm);
		$practics->add($swallow);
		$practics->add($cum);
		$practics->add($oral);
		$practics->add($piss);
		$practics->add($sexMassage);
		$practics->add($petting);
		$practics->add($fisting);
		$practics->add($deepthroat);


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

}
