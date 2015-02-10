<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\StreamDao;
use Nette\Utils\Html;

/**
 * Základní formulář pro galerii
 */
class UserGalleryBaseForm extends UserGalleryImagesBaseForm {

	const MIN_APPROVED_IMGS = 3;

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, StreamDao $streamDao, $isPaying, $userID, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($userGalleryDao, $userImageDao, $streamDao, $parent, $name);

		$this->addGroup("");

		$this->addText("name", "Jméno galerie", 30, 150)
			->addRule(Form::FILLED, "Vyplňte prosím jméno galerie")
			->addRule(Form::MAX_LENGTH, "Maximální délka jména galerie je %d znaků", 150);
		$this->addTextArea('description', 'Popis galerie:', 100, 2)
			->addRule(Form::MAX_LENGTH, "Maximální délka popisu galerie je %d znaků", 500);

		$countApprovedImgs = $userImageDao->countAllowedImages($userID);

		if ($isPaying) {
			$unapprovedMsg = 'Pokud nastavíte galerii jako soukromou, <br />budou si ji moci prohlížet pouze vaši přátelé.';
			if ($countApprovedImgs < self::MIN_APPROVED_IMGS) {
				$unapprovedMsg = 'Tuto možnost můžete používat, až budete mít ' . self::MIN_APPROVED_IMGS . ' schválené fotky.<br />'
					. ' Nyní máte ' . $countApprovedImgs . ' schválených fotek.';
			}
			$private = $this->addCheckbox('private', Html::el()->setHtml(
					'<span class="tooltip-sign">Pouze pro přátele '
					. '<div class="tooltip-element">' . $unapprovedMsg . '</div></span>'));
			if ($countApprovedImgs < self::MIN_APPROVED_IMGS) {
				$private->setDisabled();
			}
		}

		return $this;
	}

}
