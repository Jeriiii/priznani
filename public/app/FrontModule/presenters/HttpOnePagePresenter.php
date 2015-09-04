<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 22.5.2015
 */

use Nette\Application\Responses\JsonResponse;
use POS\Model\CommentImagesDao;
use POS\Model\CommentStatusesDao;
use POS\Model\CommentConfessionsDao;
use POS\Model\StatusDao;
use NetteExt\Uploader\ImageUploader;
use NetteExt\Uploader\ImagesToUpload;
use NetteExt\Uploader\ImageToUpload;
use NetteExt\Image;
use Nette\Http\FileUpload;

/**
 * Slouží pro rychlou komunikaci s dalšími zařízeními (např. mobilními).
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class HttpOnePagePresenter extends BasePresenter {

	/** @var \POS\Model\ImageLikesDao @inject */
	public $imageLikesDao;

	/** @var \POS\Model\LikeStatusDao @inject */
	public $likeStatusDao;

	/** @var \POS\Model\LikeConfessionDao @inject */
	public $likeConfessionDao;

	/** @var \POS\Model\CommentImagesDao @inject */
	public $commentImagesDao;

	/** @var \POS\Model\CommentStatusesDao @inject */
	public $commentStatusesDao;

	/** @var \POS\Model\CommentConfessionsDao @inject */
	public $commentConfessionsDao;

	/** @var \POS\Model\StatusDao @inject */
	public $statusDao;

	/** @var \POS\Model\StreamDao @inject */
	public $streamDao;

	/** @var \POS\Model\UserGalleryDao @inject */
	public $userGalleryDao;

	/** @var \POS\Model\UserImageDao @inject */
	public $userImageDao;

	public function startup() {
		parent::startup();
		if (!$this->getUser()->isLoggedIn()) {
			$json = array('msg' => 'You must logged in');
			$rsp = new JsonResponse($json);
			$this->sendResponse($rsp);
		}
	}

	/**
	 * Načte komentáře k obrázku a vrátí je ve formátu JSON.
	 * @param int $imageId
	 */
	public function actionUserImageComments($imageId) {
		$comments = $this->commentImagesDao->getAllComments($imageId);

		$rsp = $this->getJsonComments($comments);
		$this->sendResponse($rsp);
	}

	/**
	 * Načte komentáře ke statusu a vrátí je ve formátu JSON.
	 * @param int $statusId
	 */
	public function actionStatusComments($statusId) {
		$comments = $this->commentStatusesDao->getAllComments($statusId);

		$rsp = $this->getJsonComments($comments);
		$this->sendResponse($rsp);
	}

	/**
	 * Načte komentáře k přiznání a vrátí je ve formátu JSON.
	 * @param int $confessionId
	 */
	public function actionConfessionComments($confessionId) {
		$comments = $this->commentConfessionsDaoDao->getAllComments($confessionId);

		$rsp = $this->getJsonComments($comments);
		$this->sendResponse($rsp);
	}

	/**
	 * Vrátí komentáře ve formátu JSON
	 * @param type $comments
	 * @return JsonResponse
	 */
	private function getJsonComments($comments) {
		$commentsArr = array();

		foreach ($comments as $comment) {
			$comm = array();
			$comm['userId'] = $comment->userID;
			$comm['userName'] = $comment->user->user_name;
			$comm['comment'] = $comment->comment;
			$comm['likes'] = $comment->likes;
			$commentsArr[] = $comm;
		}

		$rsp = new JsonResponse(array('data' => $commentsArr), "application/json; charset=utf-8");

		return $rsp;
	}

	/**
	 * Pošle zpět systému (který vyvolal request) zprávu o úspěšném provedení operace.
	 * @param string $msg Zpráva, kterou chceme vrátit.
	 */
	private function sendSuccessJSON($msg) {
		$json = array('msg' => $msg);
		$rsp = new JsonResponse($json);
		$this->sendResponse($rsp);
	}

	/**
	 * Přidá status do stránky.
	 * @param string $status Text statusu.
	 */
	public function handleAddStatus($status) {
		$stat = $this->statusDao->insert(array(
			StatusDao::COLUMN_TEXT => $status,
			StatusDao::COLUMN_USER_ID => $this->loggedUser->id
		));

		$loggedUserID = $this->loggedUser->id;
		$categoryID = $this->loggedUser->property->preferencesID;

		$this->streamDao->addNewStatus($stat->id, $loggedUserID, $categoryID);

		$this->sendSuccessJSON('statusAdded');
	}

	public function handleUploadImage() {
		$imgName = $_FILES['image']['name'];

		if (isset($imgName)) { //byl obrázek nahrán?
			$userID = $this->loggedUser->id;
			$email = isset($_POST['email']) ? $_POST['email'] : '';

			$response['file_name'] = basename($_FILES['image']['name']);
			$response['email'] = $email;

			$defaultGallery = $this->userGalleryDao->findDefaultGallery($userID);
			if (empty($defaultGallery)) {
				$galleryID = $this->userGalleryDao->createDefaultGallery($userID)->id;
			} else {
				$galleryID = $defaultGallery->id;
			}

			$file = new FileUpload($_FILES['image']);
			$image = new ImageToUpload($file);
			$imagesToUpload = new ImagesToUpload($userID, $galleryID);
			$imagesToUpload->addImage($image);

			$this->imageUploader->saveImages($imagesToUpload);

			// File successfully uploaded
			$response['message'] = 'File uploaded successfully!';
			$response['error'] = false;
		} else {
			// File parameter is missing
			$response['error'] = true;
			$response['message'] = 'Not received any file!F';
		}

		// Echo final json response to client
		$rsp = new JsonResponse($response);
		$this->sendResponse($rsp);
	}

	/*	 * ************************************************************** */
	/*	 * ******************* like příspěvku *************************** */
	/*	 * ************************************************************** */

	public function handleLikeUserImage($imageID, $ownerID) {
		$this->imageLikesDao->addLiked($imageID, $this->loggedUser->id, $ownerID);
		$this->sendSuccessJSON('liked');
	}

	public function handleLikeStatus($statusID, $ownerID) {
		$this->likeStatusDao->addLiked($statusID, $this->loggedUser->id, $ownerID);
		$this->sendSuccessJSON('liked');
	}

	public function handleLikeConfession($confessionID) {
		$this->likeConfessionDao->addLiked($confessionID, $this->loggedUser->id);
		$this->sendSuccessJSON('liked');
	}

	/*	 * ************************************************************** */
	/*	 * ******************** komentáře příspěvku ********************* */
	/*	 * ************************************************************** */

	public function handleAddCommentUserGallery($comment, $imageId) {
		$this->commentImagesDao->insert(array(
			CommentImagesDao::COLUMN_COMMENT => $comment,
			CommentImagesDao::COLUMN_IMAGE_ID => $imageId,
			CommentImagesDao::COLUMN_USER_ID => $this->loggedUser->id
		));

		$this->sendSuccessJSON('commented');
	}

	public function handleAddCommentConfession($comment, $confessionId) {
		$this->commentImagesDao->insert(array(
			CommentConfessionsDao::COLUMN_COMMENT => $comment,
			CommentConfessionsDao::COLUMN_CONFESSION_ID => $confessionId,
			CommentConfessionsDao::COLUMN_USER_ID => $this->loggedUser->id
		));

		$this->sendSuccessJSON('commented');
	}

	public function handleAddCommentStatus($comment, $statusId) {
		$this->commentImagesDao->insert(array(
			CommentStatusesDao::COLUMN_COMMENT => $comment,
			CommentStatusesDao::COLUMN_STATUS_ID => $statusId,
			CommentStatusesDao::COLUMN_USER_ID => $this->loggedUser->id
		));

		$this->sendSuccessJSON('commented');
	}

}
