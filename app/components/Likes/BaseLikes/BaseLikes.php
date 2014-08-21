<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POSComponent\BaseLikes;

use POSComponent\BaseProjectControl;
use POS\Model\ImageLikesDao;
use Nette\Application\Responses\JsonResponse;

/**
 * Komponenta pro vykreslení aktivit uživatele.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class BaseLikes extends BaseProjectControl {

	/**
	 * @var \POS\Model\ImageLikesDao
	 */
	public $imageLikesDao;

	/**
	 * ID lajkujícího uživatele
	 */
	protected $userID;

	/**
	 * obrázek
	 */
	protected $image;

	/**
	 * @param object $image obrázek, který by se lajkoval
	 * @param int $userID Id uživatele, který může lajknout
	 */
	public function __construct(ImageLikesDao $imageLikesDao = NULL, $image = NULL, $userID = NULL) {
		parent::__construct();
		$this->userID = $userID;
		$this->image = $image;
		$this->imageLikesDao = $imageLikesDao;
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/baseLikes.latte');
		$template->image = $this->image;
		if ($this->getPresenter()->user->isLoggedIn()) {
			$template->liked = $this->getLikedByUser($this->userID, $this->image->id);
		}
		$template->render();
	}

	/**
	 *
	 * @param int $userID ID užovatele, kterého hledáme
	 * @param int $imageID ID obrázku, který hledáme
	 * @return bool
	 */
	public function getLikedByUser($userID, $imageID) {
		$liked = $this->imageLikesDao->likedByUser($userID, $imageID);
		return $liked;
	}

}
