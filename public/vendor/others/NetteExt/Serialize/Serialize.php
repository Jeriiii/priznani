<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt\Serialize;

/**
 * Třída sloužící pro serializaci problémových objektů (například objektů z DB).
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
use Nette\Database\Table\Selection;
use Nette\ArrayHash;
use Nette\Database\Table\ActiveRow;
use NetteExt\Serialize\Relation;

class Serializer {

	/**
	 * @var Nette\Database\Table\Selection Výběr z databáze co se má serializovat.
	 */
	private $sel;

	/**
	 * @var array Relace spojené s jedním řádkem ze $this->sel
	 */
	private $rels = array();

	public function __construct(Selection $sel) {
		$this->sel = $sel;
	}

	/**
	 * Vrátí celé selection jako pole.
	 * @return Nette\ArrayHash Pole vytvoření ze selection.
	 */
	public function toArrayHash() {
		$data = array();
		foreach ($this->sel as $row) {
			$arrItem = $this->serializeRow($row);
			$dataRow = ArrayHash::from($arrItem);
			if (array_key_exists("id", $arrItem)) {
				$data[$row->id] = $dataRow;
			} else {
				$data[] = $dataRow;
			}
		}
		return ArrayHash::from($data);
	}

	/**
	 * Vrátí pole IDček (i zanořených z relací).
	 * @return array Pole IDček.
	 */
	public function getIDs() {
		return array_keys((array) $this->toArrayHash());
	}

	/**
	 * Přidá další relaci k základnímu prvku.
	 * @param NetteExt\Serialize\Relation $rel Další relace co se má přidat.
	 */
	public function addRel($rel) {
		$this->rels[] = $rel;
	}

	/**
	 * Serializuje jeden řádek ze selection.
	 * @param \Nette\Database\Table\ActiveRow $row
	 * @return type
	 */
	private function serializeRow(ActiveRow $row) {
		$arrItem = $row->toArray();

		foreach ($this->rels as $rel) {
			$relName = $rel->getName();
			$relData = $rel->getData($row);
			$arrItem[$relName] = $relData;
		}

		return $arrItem;
	}

}
