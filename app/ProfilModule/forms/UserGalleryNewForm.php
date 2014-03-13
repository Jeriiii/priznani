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
			->addRule(Form::FILLED, "Musíte zadat jméno galerii.");
		$this->addTextArea("description_gallery", "Popis galerie", 30, 6)
			->addRule(Form::MAX_LENGTH, "Maximální délka komentáře je %d znaků", 500);
		
                
                $this->addGroup('Fotografie (4 x 4MB)');
                
		$this->addUpload('foto', 'Přidat fotku:')
                        ->addRule(Form::IMAGE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/png,image/jpeg,image/gif')
                        ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024)
                        ->addRule(Form::FILLED, "Musíte vybrat soubor");
		$this->addText('description_image', 'Popis:');

                $this->addUpload('foto2', 'Přidat fotku:')
                        ->addRule(Form::IMAGE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/png,image/jpeg,image/gif')
                        ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024)
                        ->addRule(Form::FILLED, "Musíte vybrat soubor");
		$this->addText('description_image2', 'Popis:');
                
		$this->addUpload('foto3', 'Přidat fotku:')
                        ->addRule(Form::IMAGE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/png,image/jpeg,image/gif')
                        ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024)
                        ->addRule(Form::FILLED, "Musíte vybrat soubor");
		$this->addText('description_image3', 'Popis:');
                
                
		$this->addUpload('foto4', 'Přidat fotku:')
                        ->addRule(Form::IMAGE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/png,image/jpeg,image/gif')
                        ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024)
                        ->addRule(Form::FILLED, "Musíte vybrat soubor");
		$this->addText('description_image4', 'Popis:');        
                
                
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
                $image2 = $values->foto2;
                $image3 = $values->foto3;
                $image4 = $values->foto4;
                
		$presenter = $this->getPresenter();
		$uID = $presenter->getUser()->getId();
                
		unset($values->image);
                unset($values->image2);
                unset($values->image3);
                unset($values->image4);
		unset($values->agreement);
              //  \Nette\Diagnostics\Debugger::Dump($values->foto->getName()." ".$values['description_image']." ".$values['foto']);die();
                
                //galerie
                $values1['name'] = $values->name;
                $values1['description'] = $values->description_gallery;
                
                
                
                //1st foto
                $values2['userID'] = $uID;
                $values2['suffix'] = $this->suffix( $image->getName() );
                $values2['description'] = $values->description_image;

                $idGallery = $presenter->context->createUsersGallery()
                        ->insert($values1);
                $values2['galleryID'] = $idGallery;
 
		$id = $presenter->context->createUsersFoto()
			->insert($values2);
               
                $bestImageID['bestImageID'] = $id;
                $presenter->context->createUsersGallery()
                        ->where('id', $idGallery)
                        ->update($bestImageID);
                
                $this->upload($image, $id, $values2['suffix'], "userGalleries" . "/" . $uID ."/".$idGallery, 500, 700, 100, 130);
                
                if(!empty($image2)){
                    //2nd foto
                    $values3['userID'] = $uID;
                    $values3['suffix'] = $this->suffix( $image->getName() );
                    $values3['description'] = $values->description_image2;
                    $values3['galleryID'] = $idGallery;
                    
                    $id2 = $presenter->context->createUsersFoto()
			->insert($values3);
                    
                    
                    $this->upload($image2, $id2, $values3['suffix'], "userGalleries" . "/" . $uID ."/".$idGallery, 500, 700, 100, 130);
                }
                
                if(!empty($image3)){
                    //3rd foto
                    $values4['userID'] = $uID;
                    $values4['suffix'] = $this->suffix( $image->getName() );
                    $values4['description'] = $values->description_image3;
                    $values4['galleryID'] = $idGallery;
                    
                   $id3 = $presenter->context->createUsersFoto()
			->insert($values4);
                   
                   $this->upload($image3, $id3, $values4['suffix'], "userGalleries" . "/" . $uID ."/".$idGallery, 500, 700, 100, 130);
                }
                
                if(!empty($image4)){
                    //4th foto
                    $values5['userID'] = $uID;
                    $values5['suffix'] = $this->suffix( $image->getName() );
                    $values5['description'] = $values->description_image4;
                    $values5['galleryID'] = $idGallery;
                    
                   $id4 = $presenter->context->createUsersFoto()
			->insert($values4);
                   
                   $this->upload($image4, $id4, $values5['suffix'], "userGalleries" . "/" . $uID ."/".$idGallery, 500, 700, 100, 130);
                }

		$presenter->flashMessage('Galerie byla vytvořena. Počkejte prosím na schválení adminem.');
		$presenter->redirect('Galleries:', array("galleryID" => $id));
 	}
}
