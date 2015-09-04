<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	NetteExt\Image;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\StreamDao;
use POS\Model\CompetitionsImagesDao;
use POS\Model\UsersCompetitionsDao;
use NetteExt\Uploader\ImageUploader;

/**
 * vkládá nové fotky do uživatelské galerie
 */
class NewCompetitionImageForm extends UserGalleryImagesBaseForm {

	/**
	 * @var \POS\Model\UserGalleryDao
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\ImageGalleryDao
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\UsersCompetitionsDao
	 */
	public $usersCompetitionsDao;

	/**
	 * @var POS\Model\CompetitionsImagesDao
	 * @inject
	 */
	public $competitionsImagesDao;

	/**
	 * počet možných polí pro obrázky při vytvoření galerie
	 */
	const NUMBER_OF_IMAGE = 1;

	private $galleryID;

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, StreamDao $streamDao, $galleryID, UsersCompetitionsDao $usersCompetitionsDao, CompetitionsImagesDao $competitionsImagesDao, ImageUploader $imageUploader, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($userGalleryDao, $userImageDao, $streamDao, $imageUploader, $parent, $name);

		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->usersCompetitionsDao = $usersCompetitionsDao;
		$this->competitionsImagesDao = $competitionsImagesDao;

		$this->galleryID = $galleryID;

		$this->addGroup("Soutěžní fotka");

		$this->addImageFields(self::NUMBER_OF_IMAGE);

		$this->addGroup("Kontaktní údaje výherce (nezveřejníme)");
		$this->addText('name', 'Jméno:')
			->addRule(Form::FILLED, 'Prosím vyplňte jméno');
		$this->addText('surname', 'Příjmení:')
			->addRule(Form::FILLED, 'Prosím vyplňte příjmení');
		$this->addText('phone', 'Telefon:')
			->addRule(Form::FILLED, 'Prosím vyplňte telefon');
		$this->addCheckbox("agreement", Html::el('a')
				->href("http://datenode.cz/soutez/fotografie.pdf")
				->setHtml('Souhlasím s podmínkami'))
			->addRule(Form::FILLED, "Musíte souhlasit s podmínkami.");

		$this->addSubmit("submit", "Přidat fotku")->setAttribute('class', 'btn-main medium');

		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(NewCompetitionImageForm $form) {
		$values = $form->values;

		unset($values->agreement);
		$images = $this->getArrayWithImages($values, self::NUMBER_OF_IMAGE);
		$isFill = $this->isFillImage($images);

		if ($isFill == FALSE) {
			$this->addError("Musíte vybrat soubor");
		} else {
			$presenter = $this->getPresenter();
			$uID = $presenter->getUser()->getId();

			$gallery = $this->userGalleryDao->findByUser($uID);
			if (empty($gallery)) {
				$gallery = $this->userGalleryDao->createDefaultGallery($uID);
			}

			$allow = $this->saveImages($images, $uID, $gallery->id);
			$galleryData = $this->userGalleryDao->getLastImageByUser($uID);
			$this->competitionsImagesDao->insert(array(
				"imageID" => $galleryData->lastImageID,
				"competitionID" => $this->galleryID,
				"userID" => $uID,
				"name" => $values->name,
				"surname" => $values->surname,
				"phone" => $values->phone
			));
			$this->usersCompetitionsDao->updateLastImage($this->galleryID, $galleryData->lastImageID);

			if ($allow) {
				$presenter->flashMessage('Fotka byla přidaná.');
			} else {
				$presenter->flashMessage('Fotka byla přidaná. Nyní je ve frontě na schválení.');
			}
			$presenter->redirect('UsersCompetitions:', $galleryData->lastImageID, $this->galleryID);
		}
	}

}
