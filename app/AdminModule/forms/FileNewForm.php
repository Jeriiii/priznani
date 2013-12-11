<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
    Nette\ComponentModel\IContainer;


class FileNewForm extends Form
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
		
		$this->addText('name', 'Jméno souboru:', 30, 200)
				->addRule(Form::FILLED, 'Je nutné zadat název souboru.');
		$this->addUpload('file', 'Nahrát soubor:')
			->addRule(Form::MIME_TYPE, 'Soubor musí být formátu pdf,doc,docx,xls,xlsx .', 'application/pdf,application/x-pdf,application/acrobat,applications/vnd.pdf,text/pdf,text/x-pdf,.docm,application/vnd.ms-word.document.macroEnabled.12,application/msword,application/vnd.ms-excel,application/vnd.openxmlformats-
	officedocument.wordprocessingml.document,application/zip')
			->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost souboru je 20 MB.', 20 * 1024 * 1024 /* v bitech */)
			->addCondition(Form::FILLED);
		$this->addSelect("id_page", "Připojit ke stránce:", $pages)
			->setPrompt("- vyberte stránku -");
			//->addRule(Form::FILLED, "Vyberte stránku ke které se má soubor připojit");
		$this->addSelect('special_condition', "Speciální nastavení:", array(0 => "Normální soubor", 1 => "Obchodní podmínky"));
		$this->addSubmit('send', 'Nahrát');
		$this->addProtection('Vypršel časový limit, odešlete formulář znovu');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(FileNewForm $form)
	{
		$values =  $form->getValues();
		$file = $values['file'];
		unset($values['file']);
		$values['suffix'] =  $this->suffix($file->getName());
		$this->getPresenter()->context->createFiles()
			->insert($values);
		
		$file_way = $this->getPresenter()->context->params['appDir']."/../www/files/page_files/";
		$file_id = $this->getPresenter()->context->createFiles()
			->order("id DESC")
			->fetch();
		if ($file->isOK()) {
			$file->move($file_way.$file_id.'.'.$values['suffix']);
		}
		$this->getPresenter()->flashMessage('Soubor byl nahrán');
		$this->getPresenter()->redirect('Admin:files');
	}
	
	public function suffix($file_name)
	{
		$temp = strstr($file_name, '.');
		return substr($temp,1,strlen($temp)-1);
	}
}
