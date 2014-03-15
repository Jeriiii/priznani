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
		$presenter = $this->getPresenter();
		
		$uID = $presenter->getUser()->getId();
                
		unset($values->image);
                unset($values->image2);
                unset($values->image3);
                unset($values->image4);
		unset($values->agreement);
                
                $idGallery = $values->galleryID;
                
                //1st foto
                if($values->foto->isOK()){

                    $values2['userID'] = $uID;
                    $values2['suffix'] = $this->suffix( $image->getName() );
                    $values2['description'] = $values->description_image;
                    $values2['galleryID'] = $idGallery;


                    $id = $presenter->context->createUsersFoto()
                            ->insert($values2);

                    $bestImageID['bestImageID'] = $id;
                    $presenter->context->createUsersGallery()
                            ->where('id', $idGallery)
                            ->update($bestImageID);
                
                $this->upload($image, $id, $values2['suffix'], "userGalleries" . "/" . $uID ."/".$idGallery, 500, 700, 100, 130);
                }
                
                
                //2nd foto
                if($values->foto2->isOK()){
                    
                    $values3['userID'] = $uID;
                    $values3['suffix'] = $this->suffix( $image2->getName() );
                    $values3['description'] = $values->description_image2;
                    $values3['galleryID'] = $idGallery;
                    
                    $id2 = $presenter->context->createUsersFoto()
			->insert($values3);
                    
                    $bestImageID['bestImageID'] = $id2;
                    $presenter->context->createUsersGallery()
                        ->where('id', $idGallery)
                        ->update($bestImageID);
                    
                    $this->upload($image2, $id2, $values3['suffix'], "userGalleries" . "/" . $uID ."/".$idGallery, 500, 700, 100, 130);
                }
                
                
                //3rd foto
                if($values->foto3->isOK()){
                    
                    $values4['userID'] = $uID;
                    $values4['suffix'] = $this->suffix( $image3->getName() );
                    $values4['description'] = $values->description_image3;
                    $values4['galleryID'] = $idGallery;
                    
                   $id3 = $presenter->context->createUsersFoto()
			->insert($values4);
                   
                   $bestImageID['bestImageID'] = $id3;
                   $presenter->context->createUsersGallery()
                        ->where('id', $idGallery)
                        ->update($bestImageID);
                   
                   $this->upload($image3, $id3, $values4['suffix'], "userGalleries" . "/" . $uID ."/".$idGallery, 500, 700, 100, 130);
                }
                
                 //4th foto
                if($values->foto4->isOK()){
                
                    $values5['userID'] = $uID;
                    $values5['suffix'] = $this->suffix( $image4->getName() );
                    $values5['description'] = $values->description_image4;
                    $values5['galleryID'] = $idGallery;
                    
                   $id4 = $presenter->context->createUsersFoto()
			->insert($values5);
                   
                   $bestImageID['bestImageID'] = $id4;
                   $presenter->context->createUsersGallery()
                        ->where('id', $idGallery)
                        ->update($bestImageID);
                   
                   $this->upload($image4, $id4, $values5['suffix'], "userGalleries" . "/" . $uID ."/".$idGallery, 500, 700, 100, 130);
                }

		$presenter->flashMessage('Fotky byly přidané.');
		$presenter->redirect('Galleries:listUserGalleryImages', array("galleryID" => $idGallery));
 	}
}
