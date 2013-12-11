<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	Nette\Image;


class ImageNewForm extends Form
{
	public $id_gallery;

	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
		//graphics
//		$renderer = $this->getRenderer();
//		$renderer->wrappers['controls']['container'] = 'div';
//		$renderer->wrappers['pair']['container'] = 'div';
//		$renderer->wrappers['label']['container'] = NULL;
//		$renderer->wrappers['control']['container'] = NULL;
		//form
		
		$presenter = $this->getPresenter();
		$this->id_gallery = $presenter->context->createGalleries()
								->order("id DESC")
								->fetch()
								->id;
		
		$this->addText("name", "Jméno obrázku:", 30, 35)
			->addRule(Form::FILLED, "Musíte zadat jméno obrázku.");
		$this->addTextArea("comment", "Komentář obrázku", 30, 6)
			->addRule(Form::MAX_LENGTH, "Maximální délka komentáře je %d znaků", 500);
		$this->addText("user_name", "Jméno výherce (nezveřejníme):", 30, 50)
			->addRule(Form::FILLED, "Musíte zadat jméno výherce.");
		$this->addText("user_phone", "Telefon výherce (nezveřejníme):", 30, 50)
			->addRule(Form::MIN_LENGTH, "Minimální délka tel. čísla je %d znaků", 9)
			->addRule(Form::FILLED, "Musíte zadat telefon výherce.");
		$this->addText("user_email", "Email výherce (nezveřejníme):", 30, 200)
			->addRule(Form::EMAIL, "Email musít správný formát.")
			->addRule(Form::FILLED, "Musíte zadat telefon výherce.");
		$this->addUpload("image","Obrázek")
			->addRule(Form::IMAGE, "Obrázek musí být ve formátu gif, jpg nebo png")
			->addRule(Form::FILLED, "Musíte vybrat soubor");
		$this->addCheckbox("agreement", 
				Html::el('a')
					->href("http://priznanizparby.cz/soutez/fotografie.pdf")
					->setHtml('Souhlasím s podmínkami'))
			->addRule(Form::FILLED, "Musíte souhlasit s podmínkami.");
		$this->addSubmit("submit", "Nahrát fotku");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(ImageNewForm $form)
	{
		$values = $form->values;
		$image = $values->image;
		$presenter = $this->getPresenter();
		
		unset($values->image);
		unset($values->agreement);
		$values['suffix'] = $this->suffix( $image->getName() );
		$values['galleryID'] = $this->id_gallery;
		$values['userID'] = $presenter->getUser()->id;
		
		$id = $presenter->context->createImages()
			->insert($values);
		
		$this->upload($image, $id, $values['suffix'], "galleries" . "/" . $this->id_gallery, "525"/*"768"*/, "700");
		
		$presenter->flashMessage('Obrázek byl vytvořen. Počkejte prosím na schválení adminem.');
		$presenter->redirect('Competition:', array("imageID" => $id));
 	}
	
	public function upload($image, $id, $suffix, $folder, $max_height, $max_width){
		if($image->isOK() & $image->isImage())
		{		   
		    /* uložení souboru a renačtení */
		    $way = WWW_DIR."/images/" . $folder . "/" . $id . '.' . $suffix;
		    $image->move($way);
		    $image = Image::fromFile($way);
		    
		    /* kontrola velikosti obrázku, proporcionální zmenšení*/
		    if($image->height > $max_height){
			$image->resize(NULL, $max_height);
		    }
		    if($image->width > $max_width){
			$image->resize($max_width, NULL);
		    }
		    $image->sharpen();
		    $image->save(WWW_DIR."/images/" . $folder . "/" . $id . "." . $suffix);
			
		/* vytvoření miniatury*/
		    $max_height = 100;
		    $max_width = 130;
		    if($image->height > $max_height){
			$image->resize(NULL, $max_height);
		    }
		    if($image->width > $max_width){
			$image->resize($max_width, NULL);
		    }
		    $image->sharpen();
		    $image->save(WWW_DIR."/images/" . $folder . "/mini" . $id . "." . $suffix);
		 } else {
		    $this->addError('Chyba při nahrávání souboru. Zkuste to prosím znovu.');
		 }
		    
	}
	
	public function suffix($filename)
	{
		return pathinfo($filename, PATHINFO_EXTENSION);;
	}
}
