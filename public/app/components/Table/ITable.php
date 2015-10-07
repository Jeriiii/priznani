<?php

namespace JKB\Component\Statistics;

/**
 * Rozhraní pro tabulky.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
interface ITable {

	/**
	 * Vrátí názvy sloupců. Každý sloupec má název jako nějaký den v týdnu.
	 * Názvy sloupců musí odpovídat jednotlivým sloupcům z leva do prava.
	 * @return array Názvy sloupců z levé části tabulky do prava
	 * ( názevsloupce1 | názevsloupce2 | názevsloupce3 | názevsloupce4 ).
	 */
	public function getColumnNames();

	/**
	 * HLAVNÍ METODA
	 * Vrátí řádky tabulky.
	 * @return array Řádky tabulky.
	 */
	public function getRows();

	/** Nadpis tabulky */
	public function getHeader();

	/** Kotva (link) na tabulku. Není povinné ho nastavovat,
	 * pokud nechcete vytvořit tlačítko s kotvou nad touto tabulkou. */
	public function getAnchor();

	/**
	 * Má být viditelný sloupec pro zlepšení? (některé tabulky na to
	 * nepotřebují zvláštní sloupec).
	 * @return boolean TRUE = má být viditelný, jinak false.
	 */
	public function visibleInviromentColumn();
}
