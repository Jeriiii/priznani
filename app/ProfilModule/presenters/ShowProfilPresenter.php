<?php

namespace ProfilModule;

use Nette\Application\UI\Form as Frm,
	Nette\ComponentModel\IContainer;
use POSComponent\Galleries\UserGalleries\UserGalleries;
use POSComponent\Galleries\UserImagesInGallery\UserImagesInGallery;

class ShowProfilPresenter extends ProfilBasePresenter {

	/**
	 * @var int ID uživatele, jehož profil je zobrazován
	 */
	private $userID;

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	/**
	 * @var \POS\Model\CoupleDao
	 * @inject
	 */
	public $coupleDao;

	/**
	 * @var \POS\Model\UserGalleryDao
	 * @inject
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\UserImageDao
	 * @inject
	 */
	public $userImageDao;

        /**
	 * @var \POS\Model\StreamDao
	 * @inject
	 */
	public $streamDao;
        /**
	 * @var \POS\Model\ConfessionDao
	 * @inject
	 */
	public $confessionDao;
        
        public $dataForStream;
	private $count = 0;

        /**
         * metoda nastavuje hodnoty predavanych parametru predtim, nez se sablona s uzivatelskym streamem vykresli.
         * Tyto hodnoty pak predava komponente Stream
         * @param type $id
         */
	public function actionDefault($id) {
                if (empty($id)) {
			$id = $this->getUser()->getId();
		}
		$this->dataForStream = $this->streamDao->getUserStreamPosts($id);
		$this->count = $this->dataForStream->count("id");
	}
                
        /**
         * vykresluje  uzivatelsky stream (zed s vlastnimi prispevky)
         * @param type $id
         */
	public function renderDefault($id) {
		if (empty($id)) {
			$id = $this->getUser()->getId();
		}

		$this->userID = $id;		
		$user = $this->getUserBaseInfo($id);

		$this->template->userData = $user;
                $this->template->userID = $id;
                $this->template->count = $this->count;
	}
        
        /**
         * vykresluje template se vsemi fotky uzivateli
         * @param type $id
         */
        public function renderUserImages($id){
                if (empty($id)) {
			$id = $this->getUser()->getId();
		}
		$user = $this->getUserBaseInfo($id);
		$this->userID = $id;
                
		$this->template->userData = $user;
		$this->template->userID = $id;
        }
        
        /**
         * vykresluje informace uzivatele, pripadne partnerovi
         * @param type $id
         */
        public function renderUserInfo($id){
            	if (empty($id)) {
			$id = $this->getUser()->getId();
		}

		$this->userID = $id;
		$this->template->userID = $id;

		$user = $this->getUserBaseInfo($id);
		$userProperty = $this->userDao->findProperties($id);

		$this->template->userData = $user;
		$this->template->userProfile = $this->userDao->getUserData($user->id);

		$property = $userProperty->user_property;
		if ($property == 'couple' || $property == 'coupleMan' || $property == 'coupleWoman') {
			$this->template->userPartnerProfile = $this->coupleDao->getPartnerData($userProperty->id_couple);
		}
        }
        
        /**
         * vrati zakladni uzivatelske informace z tabulky users jako napr. user_name nebo email apod.
         * @param type $id - user id
         * @return type
         */
        private function getUserBaseInfo($id){
            return $this->userDao->find($id);
        }
        
        /**
         * Vykresluje uzivatelsky stream, respektive jeho prispevky k hlavnemu streamu
         * @return \Stream
         */
	protected function createComponentStream() {
		return new \Stream($this->dataForStream, $this->streamDao, $this->userGalleryDao, $this->userImageDao, $this->confessionDao);
	}
        
        /**
         * vykresluje vsechny galerie daneho uzivatele
         * @return \POSComponent\Galleries\UserGalleries\UserGalleries
         */
	public function createComponentUserGalleries() {
		return new UserGalleries($this->userDao, $this->userGalleryDao);
	}

	/**
	 * vykresluje obrázky ze všech galerií daného uživatele
	 */
	protected function createComponentUserImagesAll() {
		$images = $this->userImageDao->getAllFromUser($this->userID);
		$gallery = $this->userGalleryDao->findByUser($this->userID);

		return new UserImagesInGallery($gallery->id, $images, $this->userDao);
	}
        
        /**
         * WebLoader pro minifikace skriptu
         * @return \WebLoader\Nette\JavaScriptLoader
         */
	public function createComponentJs() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js');
		$files->addFiles(array(
			'stream.js',
			'nette.ajax.js'
		));
		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new JavaScriptPacker($code, "None");
			return $packer->pack();
		});
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}
}
