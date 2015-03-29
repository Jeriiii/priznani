<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 27.3.2015
 */

/**
 * Komponenta na hodnocení obrázků.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POSComponent;

use POS\Model\UserImageDao;
use POS\Model\RateImageDao;
use POS\Model\ImageLikesDao;
use Nette\DateTime;
use Nette\Database\Table\ActiveRow;
use POS\Model\UserCategoryDao;
use POS\Model\UserCategory;
use Nette\Http\Session;

class PhotoRating extends BaseProjectControl {

	/** @var \POS\Model\UserImageDao */
	public $userImageDao;

	/** @var \POS\Model\RateImageDao @inject */
	public $rateImageDao;

	/** @var ArrayHash|ActiveRow řádek z tabulky users */
	public $loggedUser;

	/** @var \POS\Model\ImageLikesDao @inject */
	public $imageLikesDao;

	/** @var \POS\Model\UserCategoryDao @inject */
	public $userCategoryDao;

	/** @var Session */
	private $session;

	public function __construct(UserImageDao $userImageDao, RateImageDao $rateImageDao, ImageLikesDao $imageLikesDao, $loggedUser, UserCategoryDao $userCategoryDao, Session $session, $parent, $name) {
		parent::__construct($parent, $name);

		$this->userImageDao = $userImageDao;
		$this->rateImageDao = $rateImageDao;
		$this->imageLikesDao = $imageLikesDao;
		$this->userCategoryDao = $userCategoryDao;
		$this->session = $session;
		$this->loggedUser = $loggedUser;
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render() {
		$userCategory = new UserCategory($this->loggedUser->property, $this->userCategoryDao, $this->session);
		$categoryIDs = $userCategory->getCategoryIDs(FALSE);

		$image = $this->userImageDao->getFrontPage($this->loggedUser->id, $categoryIDs);
		$this->template->image = $image;

		if ($image instanceof ActiveRow) {
			$user = $image->gallery->user;

			$this->template->image = $image;
			$this->template->padding = $this->getPadiing($image);
			$this->template->user = $user;
			$this->template->age = $this->getAgeYear($user);
		}

		$this->template->setFile(dirname(__FILE__) . '/photoRating.latte');
		$this->template->render();
	}

	/**
	 * Vrátí věk uživatele.
	 * @param ActiveRow $user Uživatel, kterému obrázek patří.
	 * @return int Věk věk uživatele, kterému obrázek patří.
	 */
	private function getAgeYear(ActiveRow $user) {
		$birthday = new DateTime($user->property->age);
		$now = new DateTime();
		$age = $now->diff($birthday);
		return $age->y;
	}

	/**
	 * Vypočítá a vrátí padding (šířky) obrázku.
	 * @param ActiveRow $image Aktuálně zobrazovaný obrázek.
	 * @return int Padding, co by se měl použít: padding: 0 $padding
	 */
	private function getPadiing(ActiveRow $image) {
		/* míry obrázku */
		$imgWidth = $image->widthGalScrn;
		$imgHeight = $image->heightGalScrn;
		$imgRatio = $imgWidth / $imgHeight;

		/* max šířka obrázku z css/photoRating/default.less */
		$maxWidth = 510;
		$maxHeight = 310;
		$maxRatio = $maxWidth / $maxHeight;

		if ($maxRatio > $imgRatio) {
			$ratioHeight = $maxHeight / $imgHeight;
			$newWidth = $imgWidth * $ratioHeight; //nová šířka obrázku, bude jiná, aby se zachoval poměr stran
			$padding = ($maxWidth - $newWidth) / 2;
		} else {
			$padding = 0;
		}

		return $padding;
	}

	/**
	 * Označí obrázek jako ohodnocený (ale nedá mu like) a revaliduje komponentu
	 * @param int $imageID
	 */
	public function handleNext($imageID) {
		$this->rateImageDao->insert(array(
			RateImageDao::COLUMN_USER_ID => $this->loggedUser->id,
			RateImageDao::COLUMN_IMAGE_ID => $imageID
		));

		$this->redrawControl();
	}

	/**
	 * Označí obrázek jako ohodnocený (a dá mu like) a revaliduje komponentu
	 * @param int $imageID
	 */
	public function handleLike($imageID) {
		$this->rateImageDao->insert(array(
			RateImageDao::COLUMN_USER_ID => $this->loggedUser->id,
			RateImageDao::COLUMN_IMAGE_ID => $imageID
		));

		$this->imageLikesDao->insert(array(
			ImageLikesDao::COLUMN_USER_ID => $this->loggedUser->id,
			ImageLikesDao::COLUMN_IMAGE_ID => $imageID
		));

		$this->redrawControl();
	}

}
