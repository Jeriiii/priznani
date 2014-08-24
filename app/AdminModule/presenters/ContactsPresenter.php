<?php

namespace AdminModule;

use Nette;
use App\Forms as Frm;

/**
 * TempPresenter Description
 */
class ContactsPresenter extends AdminSpacePresenter {

	/**
	 * @var \POS\Model\ContactDao
	 * @inject
	 */
	public $contactDao;

	const PAGINATOR_ITEMS_PER_PAGE = 10;

	public function renderDefault() {
		$paginator = $this["paginator"]->getPaginator();
		$this->template->contacts = $this->contactDao->getLimit($paginator->itemsPerPage, $paginator->offset);
	}

	/**
	 * Komponenta pro stránkování zpráv
	 * @param type $name
	 * @return \VisualPaginator
	 */
	protected function createComponentPaginator($name) {
		$vp = new \VisualPaginator($this, $name);
		$paginator = $vp->getPaginator();
		$paginator->itemCount = $this->contactDao->getCount();
		$paginator->itemsPerPage = self::PAGINATOR_ITEMS_PER_PAGE;
		return $vp;
	}

	/**
	 * Označí zprávu jako přečtenou
	 * @param type $messageID ID zprávy
	 */
	public function handleViewed($messageID) {
		$this->contactDao->markViewed($messageID);

		if ($this->isAjax()) {
			$this->redrawControl('messages');
		} else {
			$this->redirect('this');
		}
	}

}
