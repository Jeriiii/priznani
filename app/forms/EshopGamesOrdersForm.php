<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Mail\Message;


class EshopGameForm extends BaseForm
{
	
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
//		//graphics
//		$renderer = $this->getRenderer();
//		$renderer->wrappers['controls']['container'] = 'div';
//		$renderer->wrappers['pair']['container'] = 'div';
//		$renderer->wrappers['label']['container'] = NULL;
//		$renderer->wrappers['control']['container'] = NULL;
		//form
		$this->addText('name', 'Jméno')
				->addRule(Form::FILLED)
				->addRule(Form::MAX_LENGTH, null, 20);
		$this->addText('surname', 'Příjmení')
				->addRule(Form::FILLED)
				->addRule(Form::MAX_LENGTH, null, 30);
		$this->addText('email', 'Email')
				->addRule(Form::FILLED)
				->addRule(Form::MAX_LENGTH, null, 255);
		$this->addText('phone', 'Telefon')
				->addRule(Form::FILLED)
				->addRule(Form::MAX_LENGTH, null, 20);
		$this->addCheckbox('print', 'Chci hru vytisknout a zaslat poštou');
		$this->addText('address', 'Adresa (pouze pokud chcete hru vytisknout)')
				->addRule(Form::FILLED)
				->addRule(Form::MAX_LENGTH, null, 255);
		
		$this->addCheckbox('vasnivefantazie', 'Vášnivé fantazie');
		$this->addCheckbox('nespoutanevzruseni', 'Nespoutané vzrušení');
		$this->addCheckbox('zhave-ukoly-pro-pary', 'Žhavé úkoly pro páry');
		$this->addCheckbox('ceskahralasky', 'Česká hra lásky');
		$this->addCheckbox('nekonecnaparty', 'Nekonečná párty');
		$this->addCheckbox('sexyaktivity', 'Sexy aktivity');
		$this->addCheckbox('ceskachlastacka', 'Česká chlastačka');
		$this->addCheckbox('milackuuklidto', 'Miláčku ukliď to');
		$this->addCheckbox('sexyhratky', 'Sexy hrátky');
		$this->addCheckbox('manazeruvsen', 'Manažerův sen');
		$this->addText('discount_coupon', 'Slevový kupón')
				->addRule(Form::FILLED)
				->addRule(Form::MAX_LENGTH, null, 50);
		$this->addText('note', 'Poznámka')
				->addRule(Form::FILLED)
				->addRule(Form::MAX_LENGTH, null, 255);
		
		$this->addSubmit("submit", "Objednat");
		$this->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(EshopGameForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		
		//uložení hodnot do databáze
		$values["create"] = new \Nette\DateTime;
		$presenter->context->createEshopGamesOrders()
			->insert($values);
		
		//poslání dat do origame
		unset($values["create"]);
		$this->postOrder($values);
		
		$presenter->flashMessage('Děkujeme za objednávku. Bude vyřízena co nejdříve.');
		$presenter->redirect('this');
 	}
	
	public function postOrder($values) {
		// TO DO
		
		$note = "blablabladfsghbtrdehtrhtyhtyhty";
		$nick = "";
		
		$values["priznaniosexu"] = 1;
		
		// překlad
		$translator = array();
		$translator["name"] = "jmeno";
		$translator["surname"] = "prijmeni";
		$translator["address"] = "adresa";
		$translator["email"] = "email";
		$translator["discount_coupon"] = "kod";
		$translator["phone"] = "telefon";
		$translator["note"] = "poznamka";
		$translator["print"] = "tisk";
		
		//vytvoření řetězce hodnot z formuláře
		$str = "";
		//$str = "note=$note&nick=$nick&form_created=igaiiaecbf";
		$lastKey = end(array_keys($values));
		
		foreach($values as $key => $value) {
			if(array_key_exists($key, $translator)) {
				$str .= $translator[$key] . "=" . $value;
			}else{
				$str .= $key . "=" . $value;
			}
			
			if($key != $lastKey) {
				$str .= "&";
			}
		}
		
		$url = 'http://priznaniosexu.cz/?do=form1Form-submit';

		//open connection
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,
					$str);

		//execute post
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);
	}
}

