<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer,
	Nette\Utils\Strings;


class EshopGamesOrdersForm extends BaseForm
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
				->addRule(Form::FILLED, "Prosím, vyplňte Vaše jméno")
				->addRule(Form::MAX_LENGTH, null, 20);
		$this->addText('surname', 'Příjmení')
				->addRule(Form::FILLED, "Prosím, vyplňte Vaše příjimení")
				->addRule(Form::MAX_LENGTH, null, 30);
		$this->addText('email', 'Email')
				->addRule(Form::FILLED, "Prosím, vyplňte Váš email")
				->addRule(Form::EMAIL, "Zadejte email ve správném tvaru např. vasemail@seznam.cz")
				->addRule(Form::MAX_LENGTH, null, 255);
		$this->addText('phone', 'Telefon')
				->addRule(Form::FILLED, "Prosím, vyplňte Váš telefon")
				->addRule(Form::MAX_LENGTH, null, 20);
		$this->addCheckbox('print', 'Chci hru vytisknout (+ 39 Kč / hru) a zaslat poštou (+ 60 Kč / objednávku)');
		$this->addText('address', 'Adresa (pouze pokud chcete hru vytisknout)')
				->addConditionOn($this['print'], Form::EQUAL, TRUE)
					->addRule(Form::FILLED, "Prosím vyplňte Vaší adresu, kam Vám máme hru zaslat")
				->addRule(Form::MAX_LENGTH, null, 255);
		
		$this->addCheckbox('vasnivefantazie', 'Vášnivé fantazie');
		$this->addCheckbox('nespoutanevzruseni', 'Nespoutané vzrušení');
		$this->addCheckbox('zhaveukolypropary', 'Žhavé úkoly pro páry');
		$this->addCheckbox('sexyaktivity', 'Sexy aktivity');
		//$this->addCheckbox('ceskahralasky', 'Česká hra lásky');
		//$this->addCheckbox('nekonecnaparty', 'Nekonečná párty');
		//$this->addCheckbox('ceskachlastacka', 'Česká chlastačka');
		//$this->addCheckbox('milackuuklidto', 'Miláčku ukliď to');
		$this->addCheckbox('sexyhratky', 'Sexy hrátky');
		//$this->addCheckbox('manazeruvsen', 'Manažerův sen');
//		$this->addText('discount_coupon', 'Slevový kupón')
//				->addRule(Form::FILLED)
//				->addRule(Form::MAX_LENGTH, null, 50);
		$this->addText('note', 'Poznámka')
				->addRule(Form::MAX_LENGTH, null, 255);
		
		$this->addSubmit("submit", "Objednat");
		$this->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(EshopGamesOrdersForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		
		//uložení hodnot do databáze
		$values["create"] = new \Nette\DateTime;
		$presenter->context->createEshopGamesOrders()
			->insert($values);
		
		//dump($values["noloop"]);die();
		
		//poslání dat do origame
		if(empty($values["noloop"])) {
			unset($values["create"]);
			$this->postOrder($values);
		}
		
		$presenter->flashMessage('Děkujeme za objednávku. Bude vyřízena co nejdříve.');
		$presenter->redirect('this');
 	}
	
	public function postOrder($values) {
				
		// překlad
		$translator = array();
		$translator["name"] = "jmeno";
		$translator["surname"] = "prijmeni";
		$translator["address"] = "adresa";
		$translator["email"] = "email";
		//$translator["discount_coupon"] = "kod";
		$translator["phone"] = "telefon";
		$translator["note"] = "poznamka";
		$translator["print"] = "tisk";
		
		$translator_game["vasnivefantazie"]["name"] = "h1";
		$translator_game["vasnivefantazie"]["value"] = "vasnivefantazie";
		$translator_game["sexyaktivity"]["name"] = "h2";
		$translator_game["sexyaktivity"]["value"] = "sexyaktivity";
		$translator_game["ceskahralasky"]["name"] = "h3";
		$translator_game["ceskahralasky"]["value"] = "ceskahralasky";
		
		$translator_game["nekonecnaparty"]["name"] = "h4";
		$translator_game["nekonecnaparty"]["value"] = "nekonecnaparty";
		$translator_game["ceskachlastacka"]["name"] = "h5";
		$translator_game["ceskachlastacka"]["value"] = "ceskachlastacka";
		$translator_game["manazeruvsen"]["name"] = "h7";
		$translator_game["manazeruvsen"]["value"] = "manazeruvsen";
		
		$translator_game["sexyhratky"]["name"] = "h8";
		$translator_game["sexyhratky"]["value"] = "sexyhratky";
		$translator_game["milackuuklidto"]["name"] = "h9";
		$translator_game["milackuuklidto"]["value"] = "milackuuklidto";
		$translator_game["nespoutanevzruseni"]["name"] = "h10";
		$translator_game["nespoutanevzruseni"]["value"] = "nespoutanevzruseni";
		$translator_game["zhaveukolypropary"]["name"] = "h11";
		$translator_game["zhaveukolypropary"]["value"] = "zhave-ukoly-pro-pary";
		
		
		//vytvoření řetězce hodnot z formuláře
		$str = "priznaniosexu=1&kod=&vek=&";
		//$str .= "noloop=no&";
		//$str .= 'h3=&h4=&h5=&h9=&&h7=&';
		$values["note"] = "(Zasláno ze stránek Přiznání o sexu) " . $values["note"];
		
		$count = $values->count();
		$i = 0;
		foreach($values as $key => $value) {
			Strings::webalize($value);
			//je mozne to prelozit?
			if(array_key_exists($key, $translator)) {
				$str .= $translator[$key] . "=" . $value;
			}else{
				// jde o hru?
				if(array_key_exists($key, $translator_game)) {
					if(!empty($value)) {
						$value = $translator_game[$key]["value"];
						$str .= $translator_game[$key]["name"] . "=" . $value;
					}
				}else{
					$str .= $key . "=" . $value;
				}
			}
			
			$i++;
			if($i < ($count)) {
				$str .= "&";
			}
			
			if($i == ($count)) break;
		}
		
		//die($count.$str);
		//$url = 'http://priznaniosexu.cz/eshop/game?do=eshopGamesOrdersForm-submit';
		$url = 'http://www.origame.cz/objednavka.php';

		//open connection
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, $count + 2 + 7 /*+ 1*/);
		curl_setopt($ch, CURLOPT_POSTFIELDS,
					$str);
		//die();
		//execute post
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);
		
	}
}

