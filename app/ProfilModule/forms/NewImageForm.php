<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	NetteExt\Image;


class NewImageForm extends ImageBaseForm
{
	public $galleryID;

	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
                
             //   Nette\Diagnostics\Debugger::Dump($galleryID);die();
		//graphics
//		$renderer = $this->getRenderer();
//		$renderer->wrappers['controls']['container'] = 'div';
//		$renderer->wrappers['pair']['container'] = 'div';
//		$renderer->wrappers['label']['container'] = NULL;
//		$renderer->wrappers['control']['container'] = NULL;
		//form
                $presenter = $this->getPresenter();
                $this->galleryID = $presenter->getParam('galleryID');
                   
                
                $this->addGroup('Fotografie (4 x 4MB)');
                
		$this->addUpload('foto', 'Přidat fotku:')    
                        ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024);
                $this->addText('image_name', 'Název:')
                        ->addRule(Form::MAX_LENGTH, "Maximální délka názvu je %d znaků", 40)
                        ->AddConditionOn($this['foto'], Form::FILLED)
                        ->addRule(Form::FILLED, 'Zadejte název fotky');
		$this->addText('description_image', 'Popis:')
                        ->addRule(Form::MAX_LENGTH, "Maximální délka komentáře je %d znaků", 500)
                        ->AddConditionOn($this['foto'], Form::FILLED)
                        ->addRule(Form::FILLED, 'Zadejte popis fotky');

                
                $this->addUpload('foto2', 'Přidat fotku:')                                    
                        ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024);
                $this->addText('image_name2', 'Název:')
                        ->addRule(Form::MAX_LENGTH, "Maximální délka názvu je %d znaků", 40)
                        ->AddConditionOn($this['foto2'], Form::FILLED)
                        ->addRule(Form::FILLED, 'Zadejte název fotky');
		$this->addText('description_image2', 'Popis:')
                        ->addRule(Form::MAX_LENGTH, "Maximální délka komentáře je %d znaků", 500)
                        ->AddConditionOn($this['foto2'], Form::FILLED)
                        ->addRule(Form::FILLED, 'Zadejte popis fotky');
                
		$this->addUpload('foto3', 'Přidat fotku:')
                        ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024);
                $this->addText('image_name3', 'Název:')
                        ->addRule(Form::MAX_LENGTH, "Maximální délka názvu je %d znaků", 40)
                        ->AddConditionOn($this['foto3'], Form::FILLED)
                        ->addRule(Form::FILLED, 'Zadejte název fotky');
		$this->addText('description_image3', 'Popis:')
                        ->addRule(Form::MAX_LENGTH, "Maximální délka komentáře je %d znaků", 500)
                        ->AddConditionOn($this['foto3'], Form::FILLED)
                        ->addRule(Form::FILLED, 'Zadejte popis fotky');;
                
                
		$this->addUpload('foto4', 'Přidat fotku:')
                        ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024);               
                $this->addText('image_name4', 'Název:')
                        ->addRule(Form::MAX_LENGTH, "Maximální délka názvu je %d znaků", 40)
                        ->AddConditionOn($this['foto4'], Form::FILLED)
                        ->addRule(Form::FILLED, 'Zadejte název fotky');
		$this->addText('description_image4', 'Popis:')
                        ->addRule(Form::MAX_LENGTH, "Maximální délka komentáře je %d znaků", 500)
                        ->AddConditionOn($this['foto4'], Form::FILLED)
                        ->addRule(Form::FILLED, 'Zadejte popis fotky');  
                
                $this->addHidden('galleryID', $this->galleryID);
                
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
                
		$this->addSubmit("submit", "Přidat fotku");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(NewImageForm $form)
	{
                $values = $form->values;
		$image = $values->foto;
                $image2 = $values->foto2;
                $image3 = $values->foto3;
                $image4 = $values->foto4;
                
		$presenter = $this->getPres();
		$uID = $presenter->getUser()->getId();
                $idGallery = $values->galleryID;
       
                $arr = array($image, $image2, $image3, $image4);
 
                $this->addImages($arr, $values, $uID, $idGallery);
  

		$presenter->flashMessage('Fotky byly přidané.');
		$presenter->redirect('Galleries:listUserGalleryImages', array("galleryID" => $idGallery));
 	}
        
        
        private function getPres(){
             return $this->getPresenter();
        }
        
        
        private function addImages($arr, $values, $uID, $idGallery) {       
            
             foreach ($arr as $key => $image) {
                    
                    if($image->isOK()){                        
  
                    $valuesDB['suffix'] = $this->suffix( $image->getName() );
                                        
                    if($key != 0){ $valuesDB['name'] = $values->image_name."".$key; } 
                    else { $valuesDB['name'] = $values->image_name; }
                    
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
