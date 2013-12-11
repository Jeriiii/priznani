<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
    Nette\ComponentModel\IContainer;


class FileChangeForm extends Form
{

	public function __construct(IContainer $parent = NULL, $name = NULL)
	{	
		parent::__construct($parent, $name);
		//graphics
		$renderer = $this->getRenderer();
		$renderer->wrappers['controls']['container'] = 'div';
		$renderer->wrappers['pair']['container'] = 'div';
		$renderer->wrappers['label']['container'] = NULL;
		$renderer->wrappers['control']['container'] = NULL;
		//form
		$presenter = $this->getPresenter();
		
		$pages = $presenter->context->createTexts()
				->fetchPairs("id", "name");
		
		$id_file = $presenter->id_file;
		$file = $presenter->context->createFiles()
				->where("id", $id_file)
				->fetch();
		
		$this->addText('name', 'Jméno souboru:', 30, 200)
				->addRule(Form::FILLED, 'Je nutné zadat název souboru.');
		$this->addSelect("id_page", "Připojit ke stránce:", $pages)
			->setPrompt("- vyberte stránku -");
			//->addRule(Form::FILLED, "Vyberte stránku ke které se má soubor připojit");
		$this->addSelect('special_condition', "Speciální nastavení:", array(0 => "Normální soubor", 1 => "Obchodní podmínky"));
		
		$this->setDefaults(array(
			"name" => $file->name,
			"id_page" => $file->id_page,
			"special_condition" => $file->special_condition
		));
		
		$this->addSubmit('send', 'Změnit');
		$this->addProtection('Vypršel časový limit, odešlete formulář znovu');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(FileChangeForm $form)
	{
		$values =  $form->getValues();
		$presenter = $this->getPresenter();
		$id_file = $presenter->id_file;
		$presenter->context->createFiles()
			->where("id", $id_file)
			->update($values);

		$this->getPresenter()->flashMessage('Soubor byl změněn');
		$this->getPresenter()->redirect('Admin:files');
	}
	
	public function suffix($file_name)
	{
		$temp = strstr($file_name, '.');
		return substr($temp,1,strlen($temp)-1);
	}
}
