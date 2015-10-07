<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace JKB\Component\Statistics\Daily;

use Nette\Utils\DateTime;
use Nette\Utils\ArrayHash;
use NetteExt\DaoBox;
use Nette\Database\Table\ActiveRow;
use Nette\Security\User;
use ArrowStatistics\Manager;
use Nette\Application\UI\Presenter;
use JKB\Component\Statistics\ITable;
use JKB\Component\Statistics\Row;
use JKB\Component\Statistics\Cell;

/**
 * Denní statistiky zobrazené v tabulce s šipkami zlepšení / zhoršení
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
abstract class TableStatisics implements ITable {

	/** @var array Suma půjček všech poboček za jednotlivé dny (PO, ÚT ...). */
	public $allDailyStatistics = array();

	/** @var string Nadpis tabulky */
	public $header = '';

	/** @var string Kotva (link) na tabulku. */
	public $anchor = '';

	/** @var Presenter */
	public $presenter;

	/** @var Počet dnů, které se mají zobrazot */
	protected $countDay = 8;

	public function __construct(Presenter $p) {
		$this->presenter = $p;
	}

	/**
	 * HLAVNÍ METODA
	 * Vrátí řádky z tabulky statistik.
	 * @return array Statistiky po jednotlivých řádcích.
	 */
	public function getRows() {
		$rows = array();

		foreach ($this->getDataByUser() as $rowDB) {
			$row = $this->createRowFromDB($rowDB);
			$rows[] = $this->loadRowData($row);
		}

		if (count($rows) >= 1) {
			$rows[] = $this->getRowSums($rows);
		}

		return $rows;
	}

	/**
	 * Vrátí součty všech řádků.
	 * @return Row Řádek, ve kterém se nachází součty hodnot všech ostatních řádků.
	 */
	protected function getRowSums($rows) {
		$sumVals = array();

		foreach ($rows as $row) {
			for ($i = 0; $i < $this->countDay; $i ++) {
				$sumVals[$i] = array_key_exists($i, $sumVals) ? $sumVals[$i] : 0; //napoprví naplní pole se sumpu nulama

				$sumVals[$i] = $sumVals[$i] + $row->cells[$i]->val;
			}
		}

		/* vytvoří buňky */
		$sumCells = array();

		for ($i = 0; $i < $this->countDay; $i ++) {
			$text = "$sumVals[$i]";
			$sumCells[] = new Cell($sumVals[$i], $text);
		}

		/* přidá prázdné buňky aby měl řádek stejnou šířku jako ostatní */
		$emptyCell = new Cell(null, null);

		$sumCells[] = $emptyCell;
		$sumCells[] = $emptyCell;

		$sumRow = new Row(0, 'Celkový součet', $sumCells);
		$sumRow->highlightRow();

		return $sumRow;
	}

	/**
	 * Vrátí data z databáze co se mají dopočítat podle přihlášeného uživatele a jeho role.
	 * @return \Nette\Database\Table\Selection Řádky z DB, ke kterým se mají
	 * dopočítat statistiky.
	 */
	abstract protected function getDataByUser();

	/**
	 * Vytvoří a správně nastaví řádek v tabulce statistik podle dat z databáze.
	 * K tomuto řádku se teprve budou dopočítávat statistiky, jde tedy pouze
	 * o počáteční inicializaci.
	 * @param ActiveRow $rowDB
	 * @return ArrayHash Řádek v tabulce, zatím bez dopočítaných statistik.
	 */
	abstract protected function createRowFromDB($rowDB);

	/**
	 * Spočítá statistiky za jeden den pro jeden řádek v tabulce = spočítá jednu buňku tabulky.
	 * @param DateTime $day Den, ve kterém se mají statistiky počítat.
	 * @param Row $item Data k řádku, se kterým se právě pracuje.
	 * mají statistiky počítat (např. pro pobočku Brno nebo pro uživatele Láďa)
	 * Jde o pole (count => počet transakcí, price => počet peněz vydělané za tyto transakce)
	 */
	abstract protected function getDayliStat(DateTime $day, Row $item);

	/**
	 * Načte všechna data za všechny dny, co chce zobrazit o jedné pobočce
	 * @param ActiveRow $row Řádek v tabulce, ke kterému se mají dopočítat statistiky.
	 * @return ArrayHash Statistiky o pobočce
	 */
	protected function loadRowData($row) {
		$day = new DateTime;
		$day->modify('- ' . ($this->countDay - 1) . ' days');

		for ($i = 0; $i != $this->countDay; $i++) {
			$this->loadDailyDataInRow($row, $day, $i);
			$day->modify('+ 1 day');
		}
		$row->cells = array_reverse($row->cells);

		/* součet hodnot v řádku */
		$cellSumRow = new Cell(0, $row->sum);
		$cellSumRow->highlight();
		$row->cells[] = $cellSumRow;

		/* přidá značku zlepšení / zhoršení */
//		$textMark = $row->box->getImprovementMark();
//		$cellMark = new Cell(0, $textMark);
//		$row->cells[] = $cellMark;

		return $row;
	}

	/**
	 * Načte počet půjček za jeden den pro jednu pobočku.
	 * @param ArrayHash $row Statistiky pro jeden řádek.
	 * @param DateTime $day Den, ve který se mají počítat statistiky.
	 * @param int $i Pořadové číslo.
	 */
	protected function loadDailyDataInRow(Row $row, DateTime $day, $i) {
		$row->cells[$i] = $this->getDayliStat($day, $row);
		$row->sum = $row->sum + $row->cells[$i]->val;
	}

	/**
	 * Vrátí názvy sloupců. Každý sloupec má název jako nějaký den v týdnu.
	 * @return array Názvy dní.
	 */
	public function getColumnNames() {
		$dayNames = new DayNames();
		return $dayNames->getDayNames();
	}

	protected function getPresenter() {
		return $this->presenter;
	}

	/** Nadpis tabulky */
	public function getHeader() {
		return $this->header;
	}

	/** Kotva (link) na tabulku. Není povinné ho nastavovat,
	 * pokud nechcete vytvořit tlačítko s kotvou nad touto tabulkou. */
	public function getAnchor() {
		return $this->anchor;
	}

	/**
	 * Má být viditelný sloupec pro zlepšení? (některé tabulky na to
	 * nepotřebují zvláštní sloupec).
	 * @return boolean TRUE = má být viditelný, jinak false.
	 */
	public function visibleInviromentColumn() {
		return true;
	}

}
