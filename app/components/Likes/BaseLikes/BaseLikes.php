<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\BaseLikes;

use POSComponent\BaseProjectControl;
use POS\Model\ImageLikesDao;
use Nette\Application\Responses\JsonResponse;

/**
 * Komponenta pro vykreslení aktivit uživatele.
 *
 * @author Daniel Holubář
 */
class BaseLikes extends BaseProjectControl {

	/**
	 * @var \POS\Model\ImageLikesDao
	 */
	public $imageLikesDao;

	/**
	 * @var int ID lajkujícího uživatele
	 */
	protected $userID;

	/**
	 * @var Nette\Database\Table\ActiveRow Obrázek pro lajknutí
	 */
	protected $image;

	/**
	 * @var bool TRUE pokud uživatel dal like, jinak FALSE.
	 */
	protected $liked;

	/**
	 * Konstruktor základní komponenty, předáváme si v něm potřebné proměnné
	 * @param Nette\Database\Table\ActiveRow $image obrázek, který by se lajkoval
	 * @param int $userID Id uživatele, který může lajknout
	 */
	public function __construct(ImageLikesDao $imageLikesDao, $image, $userID) {
		parent::__construct();
		$this->userID = $userID;
		$this->image = $image;
		$this->imageLikesDao = $imageLikesDao;
		$this->liked = $this->getLikedByUser($this->userID, $this->image->id);
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/baseLikes.latte');
		$template->image = $this->image;
		if ($this->getPresenter()->user->isLoggedIn()) {
			$template->liked = $this->liked;
		}
		$template->render();
	}

}
