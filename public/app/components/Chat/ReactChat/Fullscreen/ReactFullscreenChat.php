<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat\React;

use POS\Chat\ChatManager;
use POS\Chat\ChatCoder;
use NetteExt\Helper\GetImgPathHelper;
use POSComponent\Chat\StandardCommunicator;

/**
 * Komponenta chatu napsaného v Reactu.
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ReactFullscreenChat extends ReactChat {

	private $userInChatID;

	/**
	 * Standardni konstruktor, predani sluzby chat manageru
	 */
	function __construct(ChatManager $manager, $loggedUser, $userInChatID, $parent = NULL, $name = NULL) {
		parent::__construct($manager, $loggedUser, $parent, $name);
		$this->userInChatID = $userInChatID;
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/reactFullscreen.latte');
		$user = $this->getPresenter()->getUser();
		$template->logged = $user->isLoggedIn() && $user->getIdentity();
		$template->loggedUser = $this->loggedUser;

		$template->userInChatCodedID = ChatCoder::encode($this->userInChatID);
		$template->loggedUser = $this->loggedUser;

		$getImagePathHelper = new GetImgPathHelper($this->getPresenter()->context->httpRequest->url);
		$session = $this->getPresenter()->getSession(StandardCommunicator::URL_SESSION_NAME);
		$template->loggedUserProfilePhotoUrl = $this->chatManager->getProfilePhotoUrl($this->loggedUser->id, $session, $getImagePathHelper);
		$template->loggedUserHref = $this->getPresenter()->link(':Profil:Show:', array('id' => $this->loggedUser->id));

		$template->render();
	}

}
