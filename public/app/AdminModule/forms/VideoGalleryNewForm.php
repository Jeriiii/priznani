<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer,
	Nette\Http\UrlScript,
	Nette\Http\Request;
use POS\Model\VideoDao;
use POS\Model\ImageDao;

class VideoGalleryNewForm extends ItemGalleryNewForm {

	/**
	 * @var \POS\Model\VideoDao
	 */
	public $videoDao;

	/**
	 * @var \POS\Model\ImageDao
	 */
	public $imageDao;

	public function __construct(VideoDao $videoDao, ImageDao $imageDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		//graphics
		$renderer = $this->getRenderer();
		$renderer->wrappers['controls']['container'] = 'div';
		$renderer->wrappers['pair']['container'] = 'div';
		$renderer->wrappers['label']['container'] = NULL;
		$renderer->wrappers['control']['container'] = NULL;
		//form
		$this->videoDao = $videoDao;
		$this->imageDao = $imageDao;
		$this->addText("youtube_link", "Odkaz na youtube", 30, 400)
			->addRule(Form::FILLED, "Musíte zadat kód odkazu.");
		$this->addSubmit("submit", "Vytvořit");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(VideoGalleryNewForm $form) {
		$values = $form->values;
		$presenter = $this->getPresenter();

		//načtení parametru z youtube odkazu
		$videoLink = new UrlScript($values["youtube_link"]);
		unset($values["youtube_link"]);
		$request = new Request($videoLink);
		$videoParam = $request->getQuery("v");

		//uložení paramteru do databáze
		$videoID = $this->videoDao->insert(array("code" => $videoParam));

		$values['galleryID'] = $this->id_gallery;

		$values['userID'] = $presenter->getUser()->id;
		$values['user_name'] = "přiznáníosexu";
		$values['user_email'] = "info@priznaniosexu.cz";
		$values['user_phone'] = "0";
		$values['suffix'] = "";
		$values['videoID'] = $videoID;

		$this->imageDao->insert($values);

		$presenter->flashMessage('Video bylo nahráno');
		$presenter->redirect('Galleries:gallery', $this->id_gallery);
	}

}
