<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	NetteExt\Image;
use POS\Model\ImageDao;

class ImageGalleryNewForm extends ItemGalleryNewForm {

	/**
	 * @var \POS\Model\ImageDao
	 */
	public $imageDao;

	public function __construct(ImageDao $imageDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		//graphics
		$renderer = $this->getRenderer();
		$renderer->wrappers['controls']['container'] = 'div';
		$renderer->wrappers['pair']['container'] = 'div';
		$renderer->wrappers['label']['container'] = NULL;
		$renderer->wrappers['control']['container'] = NULL;
		//form
		$this->imageDao = $imageDao;

		$this->addUpload("image", "Obrázek")
			->addRule(Form::IMAGE, "Obrázek musí být ve formátu gif, jpg nebo png")
			->addRule(Form::FILLED, "Musíte vybrat soubor");
		$this->addSubmit("submit", "Vytvořit");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(ImageGalleryNewForm $form) {
		$values = $form->values;
		$image = $values->image;

		unset($values->image);
		$values['suffix'] = $this->suffix($image->getName());
		$values['galleryID'] = $this->id_gallery;

		$values['userID'] = $this->getPresenter()->getUser()->id;
		$values['user_name'] = "přiznáníosexu";
		$values['user_email'] = "info@priznaniosexu.cz";
		$values['user_phone'] = "0";

		$imageItem = $this->imageDao->insert($values);

		$this->upload($image, $imageItem->id, $values['suffix'], "galleries" . "/" . $this->id_gallery, "500", "700", 100, 130);

		$this->getPresenter()->flashMessage('Obrázek byl vytvořen');
		$this->getPresenter()->redirect('Galleries:gallery', $this->id_gallery);
	}

}
