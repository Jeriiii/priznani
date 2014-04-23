<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Mail\Message;


class AddItemForm extends BaseForm
{
	
	private $tableName = "confession";
	
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
		$this->addAntispam();
		
		$this->addTextarea("note", "Přiznání", 30, 30, 1)
			->addRule(Form::FILLED, "Vyplňte prosím text zprávy.");
		/* ochrana proti spamu */
		$this->addText('nick', 'Toto pole prosím nevyplňujte.');
		$this->addSubmit("submit", "Odeslat");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	/*
	 * ochrana proti spamu a proti odeslani priznani znovu
	 */
	public function protection($values, $presenter, $template)
	{
		if($this->table_name == "date")
			$exist_confession = FALSE;
		else
			$exist_confession = $this->getTable($presenter)
								->existConnectionLikeText($values->note);
		
		if(!empty($values->nick))
		{
		   $presenter->flashMessage('Toto je ochrana proti robotovi. Zkuste to prosím znovu. Jestli problem pretrvava obratte se prosim na spravce.');
		   $presenter->redirect("this");
		}
		if($exist_confession)
		{
			$confession = $this->getTable($presenter)
							->getConnectionLikeText($values->note);
			$presenter->flashMessage('Přiznání s tímto textem již existuje. Můžete ho sledovat na tomto odkaze.');
			$presenter->redirect('Page:' . $template, $confession->id);
		}
		
		unset($values["nick"]);
		unset($values['spam']);
		
		return $values;
	}


	public function baseSubmitted($form, $template)
	{
		$presenter = $this->getPresenter();
		$values = $this->protection($form->values, $presenter, $template);
		
		$values["create"] = new \Nette\DateTime;
		
		/* u inzerátů seznamky se ještě ukládá uživatel, který inzerát vložil */
		if($this->table_name == "date") {
			$insertData = array(
				"create" => $values["create"],
				"note" => $values->note,
				"userID" => $presenter->getUser()->id
			);
		}
		else 
		{
			$insertData = array(
				"create" => $values["create"],
				"note" => $values->note,
			);
		}
		
		$id = $this->getTable($presenter)
				->insert($insertData)
				->id;
                
		if($this->table_name == "advice"){
			   //Vložení nové otázky k poradně tabulky activity_stream 
			   if($presenter->getUser()->loggedIn){
				   $presenter->context->createStream()->addNewAdvice($id, $presenter->getUser()->id); 
			   } else {
				   $presenter->context->createStream()->addNewAdvice($id, NULL); 
			   }

		} else {
			if($presenter->getUser()->loggedIn){
				//Vložení nového přiznání do tabulky activity_stream                   
				$presenter->context->createStream()->addNewConfession($id, $presenter->getUser()->id);                                          
			} else {
				$presenter->context->createStream()->addNewConfession($id, NULL);                                          
			}
		}

                
		$presenter->flashMessage('Přiznání bylo vytvořeno, na této adrese můžete sledovat STAV svého přiznání.');
		if($this->table_name == "date") $presenter->redirect("this");
		$presenter->redirect('Page:' . $template, $id);
 	}
	
	public function getTable($presenter) {
		if($this->tableName == "advice")
			return $presenter->context->createAdvices();
		elseif($this->tableName == "party")
			return $presenter->context->createPartyConfessions();
		elseif($this->tableName == "date")
			return $presenter->context->createDateAdvertisements();
		else
			return $presenter->context->createForms1();
	}
}
