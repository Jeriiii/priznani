<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer,
	Kdyby\BootstrapFormRenderer\BootstrapRenderer;


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
		$this->addCheckbox('print', 'Chci hru vytisknout a zaslat poštou');
		$this->addText('address', 'Adresa (pouze pokud chcete hru vytisknout)')
				->addRule(Form::FILLED)
				->addRule(Form::MAX_LENGTH, null, 255);
		
		$this->addCheckbox('vasnivefantazie', 'Vášnivé fantazie');
		$this->addCheckbox('nespoutanevzruseni', 'Nespoutané vzrušení');
		$this->addCheckbox('zhaveukolypropary', 'Žhavé úkoly pro páry');
		//$this->addCheckbox('ceskahralasky', 'Česká hra lásky');
		//$this->addCheckbox('nekonecnaparty', 'Nekonečná párty');
		$this->addCheckbox('sexyaktivity', 'Sexy aktivity');
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
		$translator["zhaveukolypropary"] = "zhave-ukoly-pro-pary";
		
		
		//vytvoření řetězce hodnot z formuláře
		$str = "priznaniosexu=1&kod=&vek=&";
		//$str .= "noloop=no&";
		$str .= 'ceskahralasky=&nekonecnaparty=&ceskachlastacka=&milackuuklidto=&&manazeruvsen=&';
		
		$count = $values->count();
		$i = 0;
		foreach($values as $key => $value) {
			if(array_key_exists($key, $translator)) {
				$str .= $translator[$key] . "=" . $value;
			}else{
				$str .= $key . "=" . $value;
			}
			
			$i++;
			if($i < ($count - 1)) {
				$str .= "&";
			}
			
			if($i == ($count - 1)) break;
		}
		
		die($count.$str);
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

