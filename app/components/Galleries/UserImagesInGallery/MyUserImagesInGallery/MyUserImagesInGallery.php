<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Rozšiřuje základní galerii o editaci nad obrázkama a vytváření nových obrázků
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POSComponent\Galleries\UserImagesInGallery;

use \Nette\Security\User,
	Nette\Utils\Strings,
	Nette\Application\Responses\JsonResponse;
use POS\Model\UserDao,
	POS\Model\UserAllowedDao;

class MyUserImagesInGallery extends BaseUserImagesInGallery {

	/** @var int ID galerie */
	private $galleryID;

	/**
	 * @var \POS\Model\UserAllowedDao
	 */
	private $userAllowedDao;

	public function __construct($galleryID, $images, UserDao $userDao, UserAllowedDao $userAllowedDao) {
		parent::__construct($images, $userDao);
		$this->galleryID = $galleryID;
		$this->userAllowedDao = $userAllowedDao;
	}

	/**
	 * vyrendrování
	 * @param type $mode
	 */
	public function render($mode, $paying, $private) {
		$templateName = "../MyUserImagesInGallery/myUserImagesInGallery.latte";

		$this->template->paying = $paying;
		$this->template->private = $private;
		$this->template->galleryID = $this->galleryID;
		$this->renderBase($mode, $this->getUser()->id, $templateName);
	}

	/**
	 * Handler pro signál pro získání dat pro autocomplete
	 * při přidávání povlených uživatelů do galerie
	 */
	public function handleGetUsersForSuggest() {
		$alreadyAllowed = $this->getAllowedIndexes();
		$usersDataRaw = $this->userDao->getUsernameAndIdForAllowGallery($alreadyAllowed);

		$usersData = array();

		foreach ($usersDataRaw as $data) {
			$usersData[] = array(
				"id" => $data->id,
				"user" => $data->user_name
			);
		}

		if ($this->presenter->isAjax()) {
			$this->presenter->sendResponse(new JsonResponse($usersData));
		}
	}

	/**
	 * Převede povolené lidi určité galerii na indexy do pole
	 * @return array
	 */
	private function getAllowedIndexes() {
		$alreadyAllowed = $this->userAllowedDao->getAllowedByGallery($this->presenter->getParameter("galleryID"));
		$indexes = array();

		foreach ($alreadyAllowed as $item) {
			$indexes[] = $item->userID;
		}
		$indexes[] = $this->presenter->user->id;

		return $indexes;
	}

}
