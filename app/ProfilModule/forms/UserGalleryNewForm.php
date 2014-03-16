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
            //            ->addRule(Form::IMAGE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/jpg,image/png,image/jpeg,image/gif')
                        ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024);
		$this->addText('description_image', 'Popis:')
                        ->AddConditionOn($this['foto'], Form::FILLED)
                        ->addRule(Form::FILLED, 'Zadejte popis fotky');

                
                $this->addUpload('foto2', 'Přidat fotku:')                        
            //            ->addRule(Form::IMAGE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/jpg,image/png,image/jpeg,image/gif')
                        ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024);
                
		$this->addText('description_image2', 'Popis:')
                        ->AddConditionOn($this['foto2'], Form::FILLED)
                        ->addRule(Form::FILLED, 'Zadejte popis fotky');
                
		$this->addUpload('foto3', 'Přidat fotku:')
           //             ->addRule(Form::IMAGE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/jpg,image/png,image/jpeg,image/gif')
                        ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024);
		$this->addText('description_image3', 'Popis:')
                        ->AddConditionOn($this['foto3'], Form::FILLED)
                        ->addRule(Form::FILLED, 'Zadejte popis fotky');;
                
                
		$this->addUpload('foto4', 'Přidat fotku:')
            //            ->addRule(Form::IMAGE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/jpg,image/png,image/jpeg,image/gif')
                        ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024);
                
                
		$this->addText('description_image4', 'Popis:')
                        ->AddConditionOn($this['foto4'], Form::FILLED)
                        ->addRule(Form::FILLED, 'Zadejte popis fotky');  
                
                /* Je-li první pole prázdné, kontroluji zda jsou ostatní naplněné. Jsou-li také prázdné, alert nahlásí, že je potřeba alespoň 1 fotku */
                $this['foto']->addConditionOn($this['foto2'], ~Form::FILLED)
                            ->addConditionOn($this['foto3'], ~Form::FILLED)
                            ->addConditionOn($this['foto4'], ~Form::FILLED)
                            ->addRule(Form::MIME_TYPE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/jpg,image/png,image/jpeg,image/gif')
                            ->addRule(Form::FILLED, "Musíte vybrat alespoň %d soubor",1);
                
                /* Je-li druhé pole prázdné, kontroluji zda jsou ostatní naplněné. Jsou-li také prázdné, alert nahlásí, že je potřeba alespoň 1 fotku */
                $this['foto2']->addConditionOn($this['foto'], ~Form::FILLED)
                            ->addConditionOn($this['foto3'], ~Form::FILLED)
                            ->addConditionOn($this['foto4'], ~Form::FILLED)
                            ->addRule(Form::MIME_TYPE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/jpg,image/png,image/jpeg,image/gif')
                            ->addRule(Form::FILLED, "Musíte vybrat alespoň %d soubor",1);
                
                /* Je-li třetí pole prázdné, kontroluji zda jsou ostatní naplněné. Jsou-li také prázdné, alert nahlásí, že je potřeba alespoň 1 fotku */                
                $this['foto3']->addConditionOn($this['foto'], ~Form::FILLED)
                            ->addConditionOn($this['foto2'], ~Form::FILLED)
                            ->addConditionOn($this['foto4'], ~Form::FILLED)
                            ->addRule(Form::MIME_TYPE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/jpg,image/png,image/jpeg,image/gif')
                            ->addRule(Form::FILLED, "Musíte vybrat alespoň %d soubor",1);
                
                /* Je-li čtvrté pole prázdné, kontroluji zda jsou ostatní naplněné. Jsou-li také prázdné, alert nahlásí, že je potřeba alespoň 1 fotku */
                $this['foto4']->addConditionOn($this['foto'], ~Form::FILLED)
                            ->addConditionOn($this['foto2'], ~Form::FILLED)
                            ->addConditionOn($this['foto3'], ~Form::FILLED)
                            ->addRule(Form::MIME_TYPE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/jpg,image/png,image/jpeg,image/gif')
                            ->addRule(Form::FILLED, "Musíte vybrat alespoň %d soubor",1);
                
                
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
                
		$presenter = $this->getPres();
		$uID = $presenter->getUser()->getId();
                
                $arr = array($image, $image2, $image3, $image4);
                
                //vytvoření galerie
                $valuesGallery['name'] = $values->name;
                $valuesGallery['description'] = $values->description_gallery;
                $valuesGallery['userId'] = $uID;
                
                $idGallery = $presenter->context->createUsersGallery()
                        ->insert($valuesGallery);
                
                $this->addImages($arr, $values, $uID, $idGallery);
		unset($values->agreement);
 
		$presenter->flashMessage('Galerie byla vytvořena. Počkejte prosím na schválení adminem.');
		$presenter->redirect('Galleries:');
 	}
        
        private function getPres(){
             return $this->getPresenter();
        }
        
        
        private function addImages($arr, $values, $uID, $idGallery) {       
            
             foreach ($arr as $key => $image) {
                    
                    if($image->isOK()){                        
                    
                    $valuesDB['suffix'] = $this->suffix( $image->getName() );
                    
                    if($key != 0){ $valuesDB['description'] = $values->description_image."".$key; } 
                    else { $valuesDB['description'] = $values->description_image; }
                    $valuesDB['galleryID'] = $idGallery;

                    $id = $this->getPres()->context->createUsersFoto()
                            ->insert($valuesDB);

                    $bestImageID['bestImageID'] = $id;
                    $this->getPres()->context->createUsersGallery()
                            ->where('id', $idGallery)
                            ->update($bestImageID);
                                        
                    $this->upload($image, $id, $valuesDB['suffix'], "userGalleries" . "/" . $uID ."/".$idGallery, 500, 700, 100, 130);
                    unset($image);
                    }
                }
        }
}
