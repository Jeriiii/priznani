<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Stream na velkých chat.
 *
 * @author Petr Kukrál
 */

namespace POSComponent\Stream;

use Nette\ComponentModel\IContainer;
use Nette\Database\Table\Selection;
use Nette\Application\UI\Form\MessageNewForm;
use POS\Model\ChatMessagesDao;

class ChatStream extends \POSComponent\BaseProjectControl implements \IStream {

	/** @var Selection Zprávy co jsou v konverzaci - při předání v konstruktoru by měli být nevyfiltrované */
	private $messages;

	/** @var int Posun (zpráv) od konce konverzace. 0 = načti poslední zprávy z konverzace */
	private $offset;

	/** @var int Maximální počet zpráv načtených na jeden požadavek */
	private $limit;

	/** @var ChatMessageDao */
	private $chatMessageDao;

	public function __construct(Selection $messages, ChatMessagesDao $chatMessagesDao, $limit = 30, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->limit = $limit;
		$this->messages = $messages;
		$this->chatMessagesDao = $chatMessagesDao;
	}

	public function render() {
		$this->template->setFile(dirname(__FILE__) . '/chatStream.latte');
		$this->template->render();
	}

	/**
	 * Tuto metodu zavolejte ze metody render. Nastavý data, který se mají vrátit v ajaxovém i normálním
	 * požadavku v závislosti na předaném offsetu (posunu od shora).
	 * @param int $offset Offset předaný metodou handleGetMoreData. Při vyrendrování komponenty je nula.
	 */
	public function handleGetMoreData($offset, $limit) {
		$this->offset = $offset;
		$this->limit = $limit;
		$this->setData($this->offset);

		if ($this->presenter->isAjax()) {
			$this->invalidateControl('stream-messages');
		} else {
			$this->redirect('this');
		}
	}

	/**
	 * Uloží předaný ofsset jako parametr třídy a invaliduje snippet s příspěvky
	 * @param int $offset O kolik příspěvků se mám při načítání dalších příspěvků z DB posunout.
	 */
	public function setData($offset) {
		$messages = $this->messages->limit($this->limit, $this->offset);
		$this->template->messages = $messages;
	}

	protected function createComponentMessageNewForm($name) {
		return new MessageNewForm($this->chatMessagesDao, $this, $name);
	}

}
