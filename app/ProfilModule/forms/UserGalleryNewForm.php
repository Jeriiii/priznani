<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	NetteExt\Image;


class UserGalleryNewForm extends ImageBaseForm
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
		//$this->id_gallery = $presenter->galleryID;
		$this->addGroup('Infromace o galerii');
		$this->addText("name", "Jméno galerie:", 30, 35)
			->addRule(Form::FILLED, "Musíte zadat jméno obrázku.");
		$this->addTextArea("description_gallery", "Popis galerie", 30, 6)
			->addRule(Form::MAX_LENGTH, "Maximální délka komentáře je %d znaků", 500);
		
                
                $this->addGroup('Fotografie (4 x 4MB)');
		$this->addUpload('foto', 'Přidat fotku:')
                        ->addRule(Form::IMAGE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/png,image/jpeg,image/gif')
                        ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024)
                        ->addRule(Form::FILLED, "Musíte vybrat soubor");
		$this->addText('description_image', 'Popis:');

		$this->addCheckbox("agreement", 
				Html::el('a')
					->href("http://priznanizparby.cz/soutez/fotografie.pdf")
					->setHtml('Souhlasím s podmínkami'))
			->addRule(Form::FILLED, "Musíte souhlasit s podmínkami.");
                
		$this->addSubmit("submit", "Vytvořit galerie");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(UserGalleryNewForm $form)
	{
		$values = $form->values;
		$image = $values->foto;

		$presenter = $this->getPresenter();
		
		unset($values->image);
		unset($values->agreement);
              //  \Nette\Diagnostics\Debugger::Dump($values->foto->getName()." ".$values['description_image']." ".$values['foto']);die();
                
                $values1['name'] = $values->name;
                $values1['description'] = $values->description_gallery;
                
                
                $values2['userID'] = $presenter->getUser()->getId();
                $values2['suffix'] = $this->suffix( $image->getName() );
                $values2['description'] = $values->description_image;
                $idGallery = $presenter->context->createUsersGallery()
                        ->insert($values1);
                $values2['galleryID'] = $idGallery;
                
		$id = $presenter->context->createUsersFoto()
			->insert($values2);
		
		$this->upload($image, $id, $values2['suffix'], "userGalleries" . "/" . $presenter->getUser()->getId() ."/".$idGallery, 500, 700, 100, 130);
		
		$presenter->flashMessage('Galerie byla vytvořena. Počkejte prosím na schválení adminem.');
		$presenter->redirect('Galleries:', array("galleryID" => $id));
 	}
}
