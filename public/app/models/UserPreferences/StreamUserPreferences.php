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
use POS\Model\UserCategoryDao;
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
	const INIT_ITEMS_COUNT = 4;

	/** @var \Nette\ArrayHash všechny preferenční příspevky tohoto uživatele - data pro tento jediný požadavek */
	public $data;

	/** @var Nette\Http\SessionSection Sečna do které se ukládá stav příspěvků na streamu */
	protected $streamSection;

	/** @var \POS\Model\StreamDao */
	protected $streamDao;

	public function __construct($user, UserDao $userDao, StreamDao $streamDao, UserCategoryDao $userCategoryDao, Session $session) {
		parent::__construct($user, $userDao, $userCategoryDao, $session);
		$this->streamDao = $streamDao;

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
		$this->data = NULL;
		return $this;
	}

	/**
	 * Obnoví příspěvek z databáze, pokud byl již načten v paměti.
	 * @param int $streamItemId ID položky ve streamu
	 */
	public function reloadItem($streamItemId) {
		/* byl již příspěvek načten do sesseion? */
		$itemId = intval($streamItemId);

		if ($this->data->offsetExists($itemId)) {
			$streamItem = $this->streamDao->findNoFetch($itemId);
			if ($streamItem) {

				$newItems = $this->getSerializer($streamItem)->toArrayHash(); /* musí to být takhle, protože Serializer má na vstupu Selection */
				$newItem = $newItems->offsetGet($itemId);

				$this->data->offsetSet($newItem->id, $newItem);
				$this->setData($this->data);
			}
		}
	}

	/**
	 * Načte z databáze nová data z databáze, uloží je do sešny a přidá k aktuálním datům
	 */
	public function addNewData() {
		$newestItems = $this->streamDao->getAllItemsWhatFitsSince($this->getUserCategories(), $this->user->id, $this->getNewestId());
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

	/**
	 * Implementace funkce limit, aby fungovala jako ta v PDO objektech
	 * @param int $limit limit vrácených položek
	 * @param offset $offset offset vrácených položek
	 * @return \POS\UserPreferences\StreamUserPreferences položky z dat
	 */
	public function limit($limit, $offset = 0) {
		$this->loadNewItems($limit, $offset);
		$this->cutData($limit, $offset);
		return $this;
	}

	/**
	 * Vrátí počet položek v aktuálních datech.
	 * @return int počet položek
	 */
	public function count() {
		if (empty($this->data)) {//pokud objekt ani neexistuje
			return 0;
		}
		return $this->data->count();
	}

	/**
	 * Donačte nové položky do seznamu a sešny podle potřeby. Potřebné položky
	 * se udávají stejným způsobem jako v mySQL, jen nad aktuálními načtenými daty
	 * @param int $limit limit počtu položek
	 * @param int $offset offset položek
	 */
	private function loadNewItems($limit, $offset) {
		if ($this->data->count() < $limit + $offset) {
			//$minCount = $offset - $this->data->count();
			$streamItems = $this->streamDao->getAllItemsWhatFits($this->getUserCategories(), $this->user->id, $limit, $offset);
			$newItems = $this->getSerializer($streamItems);
			$this->appendToData($newItems->toArrayHash());
		}
	}

	/**
	 * Přidá položky streamu z jiného ArrayHash před položky v datech
	 * @param ArrayHash $newItems vkládané pole položek streamu (také v ArrayHash)
	 */
	private function prependToData($newItems) {
		if ($newItems) {
			foreach ($this->data as $item) {
				$newItems->offsetSet($item->id, $item);
			}
			$this->streamSection->cachedStreamItems = $newItems;
		}
	}

	/**
	 * Přidá položky streamu z jiného ArrayHash za položky v datech
	 * @param ArrayHash $newItems vkládané pole položek streamu (také v ArrayHash)
	 */
	private function appendToData($newItems) {
		if ($newItems) {
			foreach ($newItems as $item) {
				$this->data->offsetSet($item->id, $item);
			}
			$this->streamSection->cachedStreamItems = $this->data;
		}
	}

	/**
	 * Ořeže aktuální data podle daného limitu a offsetu. (jako v mySQL)
	 * Neukládá data do session cache
	 * @param int $limit limit dat
	 * @param int $offset offset dat
	 */
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

	/**
	 * Vrátí nejnovější položku aktuálních dat (horní položku), pokud existuje
	 * @return ArrayHash|NULL
	 */
	public function getNewestItem() {
		if (empty($this->data)) {
			return NULL;
		}
		foreach ($this->data as $streamItem) {
			return $streamItem; //první na seznamu
		}
	}

	/**
	 * Vrátí ID nejnovější položky v datech
	 * @return int id položky streamu
	 */
	public function getNewestId() {
		$newestItem = $this->getNewestItem();
		if ($newestItem) {
			return $newestItem->id;
		} else {
			return 0;
		}
	}

	/**
	 * Naplní nejlepší vhodná data do sešny. Použití pouze pro inicializaci
	 */
	private function initializeStreamItems() {

		$streamItems = $this->streamDao->getAllItemsWhatFits($this->getUserCategories(), $this->user->id, self::INIT_ITEMS_COUNT);
		$serializer = $this->getSerializer($streamItems);
		$this->setData($serializer->toArrayHash());
	}

	/**
	 * Uloží data do proměnné data a do sešny.
	 * @param ArrayHash $data data v arrayHashi
	 */
	private function setData($data) {
		$this->streamSection->cachedStreamItems = $data; //nastaveni pole do sešny
		$this->data = $this->streamSection->cachedStreamItems;
	}

	/**
	 * Vytvoří serializér se všemi potřebnými závislostmi
	 * @param \Nette\Database\Table\Selection $streamItems PDO objekt, který se má serializovat
	 * @return \NetteExt\Serialize\Serializer
	 */
	public function getSerializer($streamItems) {
		$userGalleryLastImageRel = new Relation('lastImage');

		$userGalleryRel = $this->getUserGalleryRel($userGalleryLastImageRel);
		$galleryRel = $this->getGalleryRel($userGalleryLastImageRel);
		$userRel = $this->getUserRel($galleryRel);
		$statusRel = $this->getStatusRel($userRel);
		$confessionRel = $this->getConfessionRel();

		$serializer = $this->createSerializer($streamItems, $galleryRel, $statusRel, $userRel, $userGalleryRel, $confessionRel);
		return $serializer;
	}

	private function createSerializer($streamItems, Relation $galleryRel, Relation $statusRel, Relation $userRel, Relation $userGalleryRel, Relation $confessionsRel) {
		$serializer = new Serializer($streamItems);

		$adviceRel = new Relation('advice');

		$serializer->addRel($galleryRel);
		$serializer->addRel($statusRel);
		$serializer->addRel($userGalleryRel);
		$serializer->addRel($adviceRel);
		$serializer->addRel($userRel);
		$serializer->addRel($confessionsRel);

		return $serializer;
	}

	/**
	 * Vytvoří novou rel na user galerii
	 * @param \NetteExt\Serialize\Relation $galleryLastImageRel Relace na poslední obrázek v galerii
	 * @return \NetteExt\Serialize\Relation
	 */
	private function getUserGalleryRel($galleryLastImageRel) {
		$galleryRel = new Relation('userGallery');
		$galleryRel->addRel($galleryLastImageRel);
		$galleryRel->addRel(new Relation('user'));
		return $galleryRel;
	}

	/**
	 * Vytvoří novou rel na galerii
	 * @param \NetteExt\Serialize\Relation $galleryLastImageRel Relace na poslední obrázek v galerii
	 * @return \NetteExt\Serialize\Relation
	 */
	private function getGalleryRel($galleryLastImageRel) {
		$galleryRel = new Relation('gallery');
		$galleryRel->addRel($galleryLastImageRel);
		return $galleryRel;
	}

	/**
	 * Vytvoří novou rel na uživatele
	 * @param \NetteExt\Serialize\Relation $galleryRel Relace na galerie
	 * @return \NetteExt\Serialize\Relation
	 */
	private function getUserRel($galleryRel) {
		$userRel = new Relation('user');
		$profilFoto = new Relation('profilFoto');
		$profilFoto->addRel($galleryRel);
		$userRel->addRel($profilFoto);
		$property = new Relation("property");
		$userRel->addRel($property);
		return $userRel;
	}

	/**
	 * Vytvoří novou rel na statusy
	 * @param \NetteExt\Serialize\Relation $userRel Relace na uživatele
	 * @return \NetteExt\Serialize\Relation
	 */
	private function getStatusRel($userRel) {
		$statusRel = new Relation('status');
		$statusRel->addRel($userRel);
		return $statusRel;
	}

	private function getConfessionRel() {
		$confRel = new Relation('confession');
		return $confRel;
	}

	/**
	 * Implementace interfacu iteratoru, aby bylo možné procházet položky foreachem
	 * @return type
	 */
	public function getIterator() {
		return $this->data->getIterator();
	}

	/**
	 * Vrátí položku aktivních dat podle klíče
	 * @param int $key klíče
	 * @return ArrayHash položka
	 */
	public function offsetGet($key) {
		return $this->data->offsetGet($key);
	}

}
