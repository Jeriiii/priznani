<?php

namespace JKB\Component\Statistics;

/**
 * Řádek v tabulce.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
interface IRow {

	/**
	 * Vrátí data která se mají vypsat do jednoho řádku
	 * @return Iterator Data do všech sloupců, která se dají proiterovat, např. array.
	 */
	public function getCells();
}
