<?php

use	Nette\Application\UI\Form as Frm,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Database;

class DatingRegistrationPresenter extends BasePresenter
{
    public function startup() {
		parent::startup();
		
		$this->setLayout("datingLayout");
	}
    
	public function renderDefault(){
		$registrationDataUser = $this->getSession('registrationDataUser');
		$registrationDataUser->remove();
	}
	
	public function renderRegistered(){
		$registrationDataUser = $this->getSession('registrationDataUser');
		$this->template->registrationDataUser = $registrationDataUser;
		$this->flashMessage('Registrace byla úspěšná');
	}
	
	public function renderThirdRegManForm(){
	}
	
	protected function createComponentFirstRegForm($name) {
		return new Frm\DatingRegistrationFirstForm($this, $name);
	}
	
	protected function createComponentSecondRegForm($name) {
		return new Frm\DatingRegistrationSecondForm($this, $name);
	}
	
	protected function createComponentThirdRegManForm($name) {
		return new Frm\DatingRegistrationManThirdForm($this, $name);
	}
	
	protected function createComponentThirdRegWomanForm($name) {
		return new Frm\DatingRegistrationWomanThirdForm($this, $name);
	}
	protected function createComponentFourthRegWomanForm($name) {
		return new Frm\DatingRegistrationWomanFourthForm($this, $name);
	}
	protected function createComponentFourthRegManForm($name) {
		return new Frm\DatingRegistrationManFourthForm($this, $name);
	}
	
	public function actionSecondRegForm($age,$property,$interest) {
		$registrationDataUser = $this->getSession('registrationDataUser');
		$registrationDataUser->role = 'unconfirmed_user';
		$registrationDataUser->age = $age;
		$registrationDataUser->interested_in = $interest;
		$registrationDataUser->user_property = $property;
		
		$this->template->post = $registrationDataUser->age;
	}
		
	public function actionPreThirdRegForm($email, $user_name, $pass, $first_sentence, $about_me) {
		$registrationDataUser = $this->getSession('registrationDataUser');
		$registrationDataCouple = $this->getSession('registrationDataCouple');
		
		$registrationDataUser->email = $email;
		$registrationDataUser->user_name = $user_name;
		
		
		$registrationDataUser->password = $pass;
		$registrationDataUser->first_sentence = $first_sentence;
		$registrationDataUser->about_me = $about_me;
		
		if($registrationDataUser->user_property == "man"){

			$this->setView("thirdRegManForm");
		}else if($registrationDataUser->user_property == "woman"){
			$this->setView("thirdRegWomanForm");
		}else if($registrationDataUser->user_property == "group"){
			$this->redirect("DatingRegistration:register");	
		}else if($registrationDataUser->user_property == "coupleWoman"){
			$registrationDataCouple->user_property = 'woman';
			$this->setView("thirdRegWomanForm");
		}else if($registrationDataUser->user_property == "couple"){
			$registrationDataCouple->user_property = 'man';
			$this->setView("thirdRegWomanForm");
		}else if($registrationDataUser->user_property == "coupleMan"){
			$registrationDataCouple->user_property = 'man';
			$this->setView("thirdRegManForm");
		}
	}
		
	
	public function actionRegister($state, $orientation, $tallness, $shape, $smoke, $drink,$graduation, $bra_size, $hair_colour, $penis_length, $penis_width) {
		$registrationDataUser = $this->getSession('registrationDataUser');//session s datama prvniho registrovaneho uzivatele pro vkládání do db
		$registrationDataUser->marital_state = $state;
		$registrationDataUser->orientation = $orientation;
		$registrationDataUser->tallness = $tallness;
		$registrationDataUser->shape = $shape;
		$registrationDataUser->smoke = $smoke;
		$registrationDataUser->drink = $drink;
		$registrationDataUser->graduation = $graduation;
		$registrationDataUser->bra_size = $bra_size;
		$registrationDataUser->hair_colour = $hair_colour;
		$registrationDataUser->penis_length = $penis_length;
		$registrationDataUser->penis_width = $penis_width;

		if($registrationDataUser->registered != 1){
			$registrationDataUser->registered = 1;

			$tableUsers = $this->context->createUsers();

			$row = $tableUsers->insert(array( 
			'role' => $registrationDataUser->role,
			'last_active' => new DateTime,
			'age' => $registrationDataUser->age,
			'user_property' => $registrationDataUser->user_property,
			'interested_in' => $registrationDataUser->interested_in,
			'created' => new DateTime,
			'email' => $registrationDataUser->email,
			'user_name' => $registrationDataUser->user_name,
			'password' =>  $registrationDataUser->password,
			'first_sentence' => $registrationDataUser->first_sentence,
			'about_me' => $registrationDataUser->about_me,
			'marital_state' => $registrationDataUser->marital_state,
			'orientation' => $registrationDataUser->orientation,
			'tallness' => $registrationDataUser->tallness,
			'shape' => $registrationDataUser->shape,
			'penis_length' => $registrationDataUser->penis_length,
			'penis_width' => $registrationDataUser->penis_width,
			'smoke' => $registrationDataUser->smoke,
			'drink' => $registrationDataUser->drink,
			'graduation' => $registrationDataUser->graduation,
			'bra_size' => $registrationDataUser->bra_size,
			'hair_colour' => $registrationDataUser->hair_colour,
			));
			$registrationDataUser->firstMemberId = $row->id;
		}
			if($registrationDataUser->user_property == "coupleWoman"){
				$this->setView("fourthRegWomanForm");

			}else if($registrationDataUser->user_property == "coupleMan" || $registrationDataUser->user_property == "couple"){
				$this->setView("fourthRegManForm");
			}else{
				/* dokončení registrace */
				//$registrationDataUser->firstMemberId = NULL;
				//$this->setView("registered");
				$this->redirect("Profil:EditProfil:", array("id" => $registrationDataUser->firstMemberId));
			}
	}
	public function actionRegisterCouple($age, $state, $orientation, $tallness, $shape, $smoke, $drink,$graduation, $bra_size, $hair_colour, $penis_length, $penis_width) {
		$registrationDataCouple = $this->getSession('registrationDataCouple');
		$registrationDataUser = $this->getSession('registrationDataUser');
		$tableCouple = $this->context->createCouple();

		$registrationDataCouple->age = $age;
		$registrationDataCouple->marital_state = $state;
		$registrationDataCouple->orientation = $orientation;
		$registrationDataCouple->tallness = $tallness;
		$registrationDataCouple->shape = $shape;
		$registrationDataCouple->smoke = $smoke;
		$registrationDataCouple->drink = $drink;
		$registrationDataCouple->graduation = $graduation;
		$registrationDataCouple->bra_size = $bra_size;
		$registrationDataCouple->hair_colour = $hair_colour;
		$registrationDataCouple->penis_length = $penis_length;
		$registrationDataCouple->penis_width = $penis_width;
		

		$row = $tableCouple->insert(array(
		'age' => $registrationDataCouple->age,
		'user_property' => $registrationDataCouple->user_property,
		'marital_state' => $registrationDataCouple->marital_state,
		'orientation' => $registrationDataCouple->orientation,
		'tallness' => $registrationDataCouple->tallness,
		'shape' => $registrationDataCouple->shape,
		'user_property' => $registrationDataCouple->user_property,
		'penis_length' => $registrationDataCouple->penis_length,
		'penis_width' => $registrationDataCouple->penis_width,
		'smoke' => $registrationDataCouple->smoke,
		'drink' => $registrationDataCouple->drink,
		'graduation' => $registrationDataCouple->graduation,
		'bra_size' => $registrationDataCouple->bra_size,
		'hair_colour' => $registrationDataCouple->hair_colour,
		));
		$registrationDataCouple->coupleId = $row->id;


		$this->context->createUsers() 
			->find($registrationDataUser->firstMemberId)
			->update(array(
			"id_couple" => $row->id 
			));

		$this->setView("registered");

	}
	
	public function actionRegistered(){
		//SEND EMAIL -> $registrationDataUser->email
	}
	
}