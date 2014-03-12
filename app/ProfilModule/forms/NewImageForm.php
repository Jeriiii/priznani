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
                   
                $this->addGroup('Přidat fotky');
		$this->addUpload('foto', 'Přidat fotku:')
                        ->addRule(Form::IMAGE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/png,image/jpeg,image/gif')
                        ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024)
                        ->addRule(Form::FILLED, "Musíte vybrat soubor");
		$this->addText('description_image', 'Popis:');
                $this->addHidden('galleryID', $this->galleryID);
                
		$this->addCheckbox("agreement", 
				Html::el('a')
					->href("http://priznanizparby.cz/soutez/fotografie.pdf")
					->setHtml('Souhlasím s podmínkami'))
			->addRule(Form::FILLED, "Musíte souhlasit s podmínkami.");
                
		$this->addSubmit("submit", "Přidat fotku");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(NewImageForm $form)
	{
		$values = $form->values;
		$image = $values->foto;

		$presenter = $this->getPresenter();
		
		unset($values->image);
		unset($values->agreement);
          //      \Nette\Diagnostics\Debugger::Dump($values->foto->getName()." ".$values['description_image']." ".$values['foto']);die();
                 
                $values2['userID'] = $presenter->getUser()->getId();
                $values2['suffix'] = $this->suffix( $image->getName() );
                $values2['description'] = $values->description_image;
                $values2['galleryID'] = $values->galleryID;
                
		$id = $presenter->context->createUsersFoto()
			->insert($values2);
		
		$this->upload($image, $id, $values2['suffix'], "userGalleries" . "/" . $presenter->getUser()->getId() ."/".$values2['galleryID'], 500, 700, 100, 130);
		
		$presenter->flashMessage('Fotky byly přidané. Počkejte prosím na schválení adminem.');
		$presenter->redirect('Galleries:listUserGalleryImages', array("galleryID" => $values2['galleryID']));
 	}
}
