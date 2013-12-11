<?php

namespace ProfilModule;
use Nette\Application\UI\Form as Frm,
	Nette\ComponentModel\IContainer;
	
class ShowProfilPresenter extends ProfilBasePresenter
{
	private $userModel;
	private $user;
	private $userPartner;
	private $fotos;
	private $id;

    public function startup()
    {
        parent::startup();
		$this->userModel = $this->context->userModel;
    }
 
	
	public function renderDefault($id)
	{				 
		if(empty($id)){ 
			$this->user = $this->userModel->findUser(array("id" => $this->getUser()->getId())); 
		} else {
			$this->user = $this->userModel->findUser(array("id" => $id /* $this->getUser()->getId()*/ ));
		}

		$this->userPartner = $this->userModel->findUserPartner(array("id" => $this->user->id_couple));
		$this->fotos = $this->context->createUsersFoto()->findUserFoto(array("userId" => $this->user->id));
	
		/* user data */
//		$labels = array('Naposledy online', 'Věk','Vztah', 'Vytvoření profilu', 'Email', 'Uživatelské jméno', 'První věta', 'O mně', 'Status', 'Sexuální orientace', 'Výška', 'Typ těla', 'Délka penisu', 'Šířka penisu', 'kouřeni', 'alkohol', 'vzdělání', 'Velikost košíku', 'Barva vlasů', 'Trojka', 'Anální sex', 'Skupinový sex', 'BDSM', 'Polykání', 'Sperma', 'Orální sex', 'Piss', 'Sex masáž', 'Osahávání', 'Fisting', 'Hluboké kouření');
//		$data = array($this->user->last_active, $this->user->age, $this->user->user_property, $this->user->created, $this->user->email, $this->user->user_name, $this->user->first_sentence, $this->user->about_me, $this->user->marital_state, $this->user->orientation, $this->user->tallness, $this->user->shape, $this->user->penis_length, $this->user->penis_width, $this->user->smoke, $this->user->drink, $this->user->graduation, $this->user->bra_size, $this->user->hair_colour, $this->user->threesome, $this->user->anal, $this->user->group, $this->user->bdsm, $this->user->swallow, $this->user->cum, $this->user->oral, $this->user->piss, $this->user->sex_massage, $this->user->petting, $this->user->fisting, $this->user->deepthrought);
			
		$this->template->userData = $this->user;
		//$this->template->userProfile = array_combine($labels, $data);	
		$this->template->userProfile = $this->context->createUsers()->getUserData($this->user->id);

		/* test if has foto*/
		if(isset($this->fotos)){
			$this->template->fotos = $this->fotos;
			$this->template->hasFoto = true;
		} else {
			$this->template->hasFoto = false;
		}
		
		/* if has partner => render his data */
//		if($this->user->id_couple != null){
//			$labelsPartner = array('Statut', 'Sexuální orientace', 'Výška', 'Typ těla', 'Délka penisu', 'Šířka penisu', 'kouřeni', 'alkohol', 'vzdělání', 'Velikost košíku', 'Barva vlasů');	
//			$dataPartner = array( $this->userPartner->marital_state, $this->userPartner->orientation, $this->userPartner->tallness, $this->userPartner->shape, $this->userPartner->penis_length, $this->userPartner->penis_width, $this->userPartner->smoke, $this->userPartner->drink, $this->userPartner->graduation, $this->userPartner->bra_size, $this->userPartner->hair_colour);		
//		}
		
		if($this->user->user_property == 'couple' || $this->user->user_property == 'coupleMan' || $this->user->user_property == 'coupleWoman'){
			$this->template->userPartnerProfile = $this->context->createCouple()->getPartnerData($this->user->id_couple);//array_combine($labelsPartner, $dataPartner);
		}
//		if($this->user->user_property == 'man'){
//			$this->setView("singleManProfil");
//		}
//		
//		if($this->user->user_property == 'woman'){
//			$this->setView("singleWomanProfil");
//		}
//		
//		if($this->user->user_property == 'couple'){
//			$this->setView("coupleShowWomanMan");								
//			$this->template->userPartnerProfile = array_combine($labelsPartner, $dataPartner);
//		}
//		
//		if($this->user->user_property == 'coupleMan'){
//			$this->setView("coupleShowManMan");								
//			$this->template->userPartnerProfile = array_combine($labelsPartner, $dataPartner);
//		}
//		if($this->user->user_property == 'coupleWoman'){
//			$this->setView("coupleShowWomanWoman");								
//			$this->template->userPartnerProfile = array_combine($labelsPartner, $dataPartner);
//		}		
//		if($this->user->user_property == 'group'){
//			$this->setView("ShowProfilGroup");								
//		}		
	}
	
	

}
