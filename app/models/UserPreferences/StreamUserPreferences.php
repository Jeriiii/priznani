<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Uživatelské preference pro stream.
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */

namespace POS\UserPreferences;

use POS\Model\UserPropertyDao;
use Nette\Database\Table\ActiveRow;
use POS\Model\UserDao;
use POS\Model\StreamDao;
use POS\Model\StreamCategoriesDao;
use Nette\Http\Session;
use NetteExt\Serialize\Serializer;
use NetteExt\Serialize\Relation;
use Nette\ArrayHash;
use POS\UserPreferences\IDataAddable;

/**
 * Vybere do streamu nejvhodnější data vzhledem k preferencím daného uživatele
 */
class StreamUserPreferences extends BaseUserPreferences implements IUserPreferences, \IteratorAggregate, IDataAddable {

	/**
	 * Název sekce v session, kterou používá pro ukládání
	 */
	const NAME_SESSION_STREAM_ITEMS = "StreamItemsData";

	/**
	 * Počet prvků uložených do sešny při její inicializaci (když neexistuje a kvůli tomu se vytváří)
	 */
	const INIT_ITEMS_COUNT = 20;

	/** @var \Nette\ArrayHash všechny preferenční příspevky tohoto uživatele - data pro tento jediný požadavek */
	public $data;

	/** @var Nette\Http\SessionSection Sečna do které se ukládá stav příspěvků na streamu */
	protected $streamSection;

	/** @var \POS\Model\StreamDao */
	protected $streamDao;

	/** @var \POS\Model\StreamCategoriesDao */
	protected $streamCategoriesDao;

	public function __construct(ActiveRow $userProperty, UserDao $userDao, StreamDao $streamDao, StreamCategoriesDao $streamCategoriesDao, Session $session) {
		parent::__construct($userProperty, $userDao, $session);
		$this->streamDao = $streamDao;
		$this->streamCategoriesDao = $streamCategoriesDao;

		$this->data = NULL;

		$this->streamSection = $session->getSection(self::NAME_SESSION_STREAM_ITEMS);
		$this->streamSection->setExpiration("45 min");
	}

	/**
	 * Obnoví data v session tak, že je smaže a začne s jejich načítáním znovu.
	 * @return StreamUserPreferences tento iterovatelný objekt schopný nahradit Selection
	 */
	public function calculate() {
		$this->streamSection->cachedStreamItems = NULL;
		$this->data = $this->streamSection->cachedStreamItems;
		return $this;
	}

	public function addNewData() {
		$newestItems = $this->streamDao->getAllItemsWhatFitsSince(array(
			$this->userProperty->offsetGet(UserPropertyDao::COLUMN_PREFERENCES_ID)
			), $this->getNewestId());
		$newData = $this->getSerializer($newestItems);
		$this->prependToData($newData->toArrayHash());
	}

	/**
	 * Vrací nejvhodnější příspěvky na stream uživatele
	 * @return StreamUserPreferences tento iterovatelný objekt schopný nahradit Selection
	 */
	public function getBestStreamItems() {
		$this->data = $this->streamSection->cachedStreamItems;
		if ($this->data === NULL) {
			$this->initializeStreamItems(); //pokud uživatel přišel prvně
		} else {//není třeba ptát se dvakrát při prvním příchodu
			$this->addNewData(); //dopočítání příspěvk;, které mezitím přibyly
		}
		return $this;
	}

	public function limit($limit, $offset = 0) {
		$this->loadNewItems($limit, $offset);
		$this->cutData($limit, $offset);
		return $this;
	}

	public function count() {
		if (empty($this->data)) {
			return 0;
		}
		return $this->data->count();
	}

	private function loadNewItems($limit, $offset) {
		if ($this->data->count() < $limit + $offset) {
			$minCount = $offset - $this->data->count();
			$streamItems = $this->streamDao->getAllItemsWhatFits(array(
				$this->userProperty->offsetGet(UserPropertyDao::COLUMN_PREFERENCES_ID)
				), $minCount, $limit);
			$newItems = $this->getSerializer($streamItems);
			$this->appendToData($newItems->toArrayHash());
		}
	}

	private function prependToData($newItems) {
		if ($newItems) {
			foreach ($this->data as $item) {
				$newItems->offsetSet($item->id, $item);
			}
			$this->streamSection->cachedStreamItems = $newItems;
		}
	}

	private function appendToData($newItems) {
		if ($newItems) {
			foreach ($newItems as $item) {
				$this->data->offsetSet($item->id, $item);
			}
			$this->streamSection->cachedStreamItems = $this->data;
		}
	}

	private function cutData($limit, $offset) {
		$actualPosition = 0;
		$newData = new ArrayHash();
		foreach ($this->data as $streamItem) {
			if ($actualPosition >= $offset) {//pokud jsem se už dostal do daného rozsahu
				$newData->offsetSet($streamItem->id, $streamItem);
			}
			$actualPosition++;
			if ($actualPosition >= $limit + $offset) {//pokud jsem už za limitem
				break;
			}
		}
		$this->data = $newData;
	}

	public function getNewestItem() {
		if (empty($this->data)) {
			return NULL;
		}
		foreach ($this->data as $streamItem) {
			return $streamItem; //první na seznamu
		}
	}

	public function getNewestId() {
		$newestItem = $this->getNewestItem();
		if ($newestItem) {
			return $newestItem->id;
		} else {
			return 0;
		}
	}

	/**
	 * Naplní nejlepší vhodná data do sešny.
	 */
	private function initializeStreamItems() {
		$streamItems = $this->streamDao->getAllItemsWhatFits(array(
			$this->userProperty->offsetGet(UserPropertyDao::COLUMN_PREFERENCES_ID)
			), self::INIT_ITEMS_COUNT);
		$serializer = $this->getSerializer($streamItems);
		$this->streamSection->cachedStreamItems = $serializer->toArrayHash(); //nastaveni pole do sešny
		$this->data = $this->streamSection->cachedStreamItems;
	}

	public function getSerializer($streamItems) {
		$serializer = new Serializer($streamItems);
		$confessionsRel = new Relation('confession');
//		$videoRel = new Relation('video');
		$galleryRel = new Relation('gallery');
		$statusRel = new Relation('status');
		$userGalleryRel = new Relation('userGallery');
		$userGalleryLastImageRel = new Relation('lastImage');
		$adviceRel = new Relation('advice');
		$userRel = new Relation('user');

//		$serializer->addRel($videoRel);
		$galleryRel->addRel($userGalleryLastImageRel);
		$serializer->addRel($galleryRel);
		$serializer->addRel($statusRel);
		$serializer->addRel($userGalleryRel);
		$serializer->addRel($adviceRel);
		$serializer->addRel($userRel);
		$serializer->addRel($confessionsRel);
		return $serializer;
	}

	public function getIterator() {
		return $this->data->getIterator();
	}

	public function offsetGet($key) {
		return $this->data->offsetGet($key);
	}

}
