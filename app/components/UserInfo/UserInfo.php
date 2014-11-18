<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace POSComponent\UserInfo;

use POSComponent\BaseProjectControl,
	POS\Model\UserDao;

/**
 * Description of UserInfo
 *
 * @author Jan Sehnal <niveavisac@gmail.com>
 */
class UserInfo extends BaseProjectControl {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	public function __construct(UserDao $userDao, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->userDao = $userDao;
	}

	public function render($mode, $userId, $templateName = "userInfo.latte") {

		/* zobrazí všechny informace o uživateli */
		if ($mode == "listAll") {
			$this->template->userProperties = $this->userDao->getUserData($userId);
			$this->template->listFew = FALSE;
			$this->template->userID = $userId;
		}

		/* zobrazí pouze zkrácené info o uživateli */
		if ($mode == "listFew") {
			$this->template->userProperties = $this->userDao->getUserShortInfo($userId);
			$this->template->listFew = TRUE;
			$this->template->userID = $userId;
		}

		$this->template->setFile(dirname(__FILE__) . '/' . $templateName);
		$this->template->render();
	}

}
