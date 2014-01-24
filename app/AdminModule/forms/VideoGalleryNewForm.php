<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer,
	Nette\Http\UrlScript,
	Nette\Http\Request;


class VideoGalleryNewForm extends ItemGalleryNewForm
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
		
		$this->addText("youtube_link", "Odkaz na youtube", 30, 400)
			->addRule(Form::FILLED, "Musíte zadat kód odkazu.");
		$this->addSubmit("submit", "Vytvořit");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(VideoGalleryNewForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		
		//načtení parametru z youtube odkazu
		$videoLink = new UrlScript($values["youtube_link"]);
		unset($values["youtube_link"]);
		$request = new Request($videoLink);
		$videoParam = $request->getQuery("v");
		
		//uložení paramteru do databáze
		$videoID = $presenter->context->createVideos()
						->insert(array("code" => $videoParam));
		
		//die(dump($videoID));
		
		$values['galleryID'] = $this->id_gallery;
                
		$values['userID'] = $this->getPresenter()->getUser()->id;
		$values['user_name'] = "přiznáníosexu";
		$values['user_email'] = "info@priznaniosexu.cz";
		$values['user_phone'] = "0";
		$values['suffix'] = "";
		$values['videoID'] = $videoID;
		
		$id = $this->getPresenter()->context->createImages()
					->insert($values);
		
		$this->getPresenter()->flashMessage('Video bylo nahráno');
		$this->getPresenter()->redirect('Galleries:gallery', $this->id_gallery);
 	}
}
