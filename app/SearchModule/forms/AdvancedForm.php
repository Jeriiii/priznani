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
			->setAttribute('placeholder', 18)
			->addRule(Form::RANGE, 'Věk musí být od %d do %d let.', array(18, 120));
		$ageTo = $this->addText('age_to', 'do: ')
			->setAttribute('placeholder', 120)
			->setAttribute('class', 'smallColumn')
			->addRule(Form::RANGE, 'Věk musí být od %d do %d let.', array(18, 120));

		//přidání políček do skupiny
		$age->add($ageFrom);
		$age->add($ageTo);


		//skupina pro políčka s pohlavím a orientací
		$sex = $this->addGroup('Pohlaví a orientace');

		$sexType = array(
			'' => '--------',
			'm' => 'muž',
			'w' => 'žena',
		);

		$sexChoices = $this->addSelect('sex', 'pohlaví:', $sexType)
			->setAttribute('class', 'columnSelectWidth');

		$penisLengthType = array(
			'' => '--------',
			'1' => 'malá',
			'2' => 'střední',
			'3' => 'velká',
			'4' => 'obrovská',
		);

		$penisLength = $this->addSelect('penis_length', 'délka penisu: ', $penisLengthType)
			->setAttribute('class', 'columnSelectWidth');

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
		$sex->add($penisLength);
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

		$shape = $this->addSelect('shape', 'postava:', $shapeTypes)
			->setAttribute('class', 'columnSelectWidth');
		$hair = $this->addText('hair_color', 'barva vlasů:')
			->setAttribute('class', 'columnWidth');
		$heightFrom = $this->addText('tallness_from', 'výška:')
			->setAttribute('placeholder', 'od')
			->setAttribute('class', 'smallColumn');
		$heightTo = $this->addText('tallness_to', '')
			->setAttribute('placeholder', 'do')
			->setAttribute('class', 'smallColumn');

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

		$graduation = $this->addSelect('graduation', 'vzdělání:', $gra)
			->setAttribute('class', 'columnSelectWidth');

		$cityDataRaw = $this->cityDao->getCitiesData();
		$cityData = $this->getCityData($cityDataRaw);

		$demograph = $this->addSelect('city', 'bydliště:', $cityData)
			->setAttribute('class', 'columnSelectWidth');

		//přidání do skupiny
		$general->add($graduation);
		$general->add($demograph);

		//skupina pro políčka se sexuálními praktikami
		$practics = $this->addGroup('Sexuální praktiky');

		$notCare = $this->addCheckbox('notCare', 'nezáleží');
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
		$practics->add($notCare);
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
		$this->onSuccess[] = callback($this, 'advancedFormSubmitted');

		return $this;
	}

	public function advancedFormSubmitted(AdvancedForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		//roztřídění dat o bydlišti
		$cityData = explode(',', $values->city);
		unset($values->city);
		$values->cityID = $cityData[0];
		$values->districtID = $cityData[1];
		$values->regionID = $cityData[2];


		$presenter->redirect('Search:advanced', (array) $values);
	}

	/**
	 * upraví data o bydlišti do lepšího formátu pro formulář ("ID,ID,ID")
	 * @param string $cityDataRaw
	 * @return array
	 */
	private function getCityData($cityDataRaw) {
		$cityData = array();

		foreach ($cityDataRaw as $item) {
			$cityData[$item->id . "," . $item->districtID . "," . $item->regionID] = $item->city . "," . $item->district . ", " . $item->region;
		}
		return $cityData;
	}

}
