<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Image;


class ImageGalleryNewForm extends ItemGalleryNewForm
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
		
		$this->addUpload("image","Obrázek")
			->addRule(Form::IMAGE, "Obrázek musí být ve formátu gif, jpg nebo png")
			->addRule(Form::FILLED, "Musíte vybrat soubor");
		$this->addSubmit("submit", "Vytvořit");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(ImageGalleryNewForm $form)
	{
		$values = $form->values;
		$image = $values->image;
		
		unset($values->image);
		$values['suffix'] = $this->suffix( $image->getName() );
		$values['galleryID'] = $this->id_gallery;
                
                $values['userID'] = $this->getPresenter()->getUser()->id;
                $values['user_name'] = "přiznáníosexu";
                $values['user_email'] = "info@priznaniosexu.cz";
                $values['user_phone'] = "0";
		
		$id = $this->getPresenter()->context->createImages()
			->insert($values);
		
		$this->upload($image, $id, $values['suffix'], "galleries" . "/" . $this->id_gallery, "600"/*"768"*/, "1024");
		
		$this->getPresenter()->flashMessage('Obrázek byl vytvořen');
		$this->getPresenter()->redirect('Galleries:gallery', $this->id_gallery);
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
	
	public function suffix($file_name)
	{
		$temp = strstr($file_name, '.');
		return substr($temp,1,strlen($temp)-1);
	}
}
