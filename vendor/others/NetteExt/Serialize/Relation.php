<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt\Serialize;

/**
 * Relační položky co se mají vytáhnout z databáze.
 * Například pokud uživatel z tabulky users má mít i uloženou profilovou
 * fotku.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
use Nette\Database\Table\ActiveRow;
use Nette\Object;
use Nette\ArrayHash;

class Relation extends Object {

	private $relations = array();

	/**
	 * @var string Název relace tak, aby se dala vytáhnout z DB (tedy
	 * povětšinou název sloupce bez ID v názvu )
	 */
	private $relName;

	public function __construct($relName) {
		$this->relName = $relName;
	}

	/**
	 * Přidá relaci k tomuto prvku. Tedy pracuje jako
	 * $item->predchoziRelace->tatoRelace
	 * Tím se umožňuje stromová struktura relací.
	 * @param \NetteExt\Serialize\Relation $rel Podrelace která se má přidat
	 * k této relaci.
	 */
	public function addRel(Relation $rel) {
		$this->relations[] = $rel;
	}

	/**
	 * Vrátí všechna data z relace jednoho řádku jako pole.
	 * @param NetteExt\Serialize\Relation $item Řádek z databáze který slouží
	 * jako rodič (= nadřazená tabulka) v této relaci.
	 * @return Nette\ArrayHash Pole dat z relací.
	 */
	public function getData($item) {
		$childItem = $item->offsetGet($this->relName);

		if (empty($childItem)) {
			return FALSE;
		}

		$arrChildItem = $childItem->toArray();

		foreach ($this->relations as $rel) {
			$relName = $rel->getName();
			$relData = $rel->getData($childItem);
			$arrChildItem[$relName] = $relData;
		}

		return ArrayHash::from($arrChildItem);
	}

	/**
	 * @return Název relace.
	 */
	public function getName() {
		return $this->relName;
	}

}
