<?php
namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;


class AdvancedSearchForm extends SearchBaseForm
{
	private $userModel;
	private $id_user;
	private $record;
	
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
	/*			
		$presenter = $this->getPresenter();
		$this->userModel = $this->getPresenter()->context->userModel;
		$this->id_user = $presenter->getUser()->getId();
		$userInfo = $presenter->context->userModel->findUser(array('id' => $this->id_user));
		*/

		$orientation = array(
			'' => '-- Vyberte hledané pohlaví --',
			'hetero' => 'hetero',
			'homo' => 'homo',
			'bi' => 'bi',
			'biTry' => 'bi - chtěl bych zkusit',
		);
		$this->addSelect('orientation', 'Sexuální orientace:', $orientation);
 
		$items = array(
		'want_to_meet_men' => 'muže', 
		'want_to_meet_women' => 'ženy',
		'want_to_meet_couple' => 'páry', 
		'want_to_meet_couple_men' => 'pár mužů', 
		'want_to_meet_couple_women' => 'pár žen', 
		'want_to_meet_group' => 'skupina');
		
		$this->addCheckboxList('interested_in', 'Zajímám se o:',  $items);
				//->addRule('::Checked', 'Musí být vybrán alespoň jeden údaj ze Zajímám se o!')
				
 	$this->addText('age_from', 'Věk od')
			->setType('number')
			->addRule(Form::RANGE, 'Věk musí být od %d do %d let.', array(18, 120));
		
		$this->addText('age_to', 'Věk do')
			->setType('number')
			->addRule(Form::RANGE, 'Věk musí být od %d do %d let.', array(18, 120));
		
		$UserTallnessOption = array(
			'' => '-- Vyberte hledaná výška --',
			'160' => '< 160 cm',
			'170' => '160 - 170 cm',
			'180' => '170 - 180 cm',
			'190' => '180 - 190 cm',
			'200' => '> 190 cm',
		);
		$this->addSelect('tallness', 'Výška:', $UserTallnessOption);
			
		$UserShapeOption = array(
			'' => '-- Vyberte preferovaná postava --',
			'0' => 'hubená',
			'1' => 'štíhlá',
			'2' => 'normální',
			'3' => 'atletická',
			'4' => 'plnoštíhlá',
			'5' => 'při těle',
		);
		$this->addSelect('shape', 'Postava:', $UserShapeOption);

		$UserHabitOption = array(
			'' => '-- Vyberte hledaný návyk --',
			'often' => 'často',
			'no' => 'ne',
			'occasionlly' => 'příležitostně',
		);
		$this->addSelect('smoke', 'Kouření:', $UserHabitOption);
		$this->addSelect('drink', 'Pití:', $UserHabitOption);

		$UserGraduationOption = array(
			'' => '-- Vyberte hledané vzdělání --',
			'zs' => 'základní',
			'sou' => 'vyučen/a',
			'sos' => 'střední',
			'vos' => 'vyšší odborné',
			'vs' => 'vysoké',
		);
		$this->addSelect('graduation', 'Vzdělání:', $UserGraduationOption);	
			
 
		$this->onSuccess[] = callback($this, 'editformSubmitted');
		$this->addSubmit('search', 'Vyhledat');
		
		return $this; 		
	}
	
	public function editformSubmitted($form)
	{
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$search = array(
			"orientation" => $values->orientation,
			"interested_in_men" => isset($values->interested_in)&&in_array("want_to_meet_men", $values->interested_in)?1:0,
			"interested_in_women" => isset($values->interested_in)&&in_array("want_to_meet_women", $values->interested_in)?1:0,
			"interested_in_couple" => isset($values->interested_in)&&in_array("want_to_meet_couple", $values->interested_in)?1:0,
			"interested_in_couple_men" => isset($values->interested_in)&&in_array("want_to_meet_couple_men", $values->interested_in)?1:0,
			"interested_in_couple_women" => isset($values->interested_in)&&in_array("want_to_meet_couple_women", $values->interested_in)?1:0,
			"interested_in_group" => isset($values->interested_in)&&in_array("want_to_meet_group", $values->interested_in)?1:0,
			"age_from" => $values->age_from,
			"age_to" => $values->age_to,
			"tallness" => $values->tallness,
			"shape" => $values->shape,
			"graduation" => $values->graduation
		);
		
		$presenter->redirect('Search:AdvancedSearch', $search/*array('filter' => $values)*/);
		
		$presenter->terminate();
		
	} 
}