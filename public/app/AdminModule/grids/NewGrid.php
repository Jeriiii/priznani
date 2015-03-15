<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * DataGrid pro procházení měst, okresů a krajů. Vše najednou.
 *
 * @author Daniel Holubář
 */

namespace POS\Grids;

use Grido\Grid;
use POS\Model\NewsDao;
use POS\Model\DistrictDao;
use POS\Model\RegionDao;

class NewGrid extends Grid {

	/** @var \POS\Model\NewsDao @inject */
	public $newsDao;

	public function __construct(NewsDao $newsDao, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->newsDao = $newsDao;

		$this->setModel($newsDao->getAll());

		$this->addColumns();
		$this->addActionHrefs();
	}

	/**
	 * Přidá sloupce do grida.
	 */
	private function addColumns() {
		$name = $this->addColumnText("name", "Název");
		$name->setFilterText();

		$release = $this->addColumnText("release", "Vydáno");
		$release->setCustomRender(function($item) {
			return $item->release == 1 ? "ANO" : "NE";
		});
		$release->setSortable();

		$created = $this->addColumnText("created", "Vytvořeno");
		$created->setSortable();
	}

	/**
	 * Přidá tlačítka s akcemi do grida.
	 */
	private function addActionHrefs() {
		$delete = $this->addActionHref("delete", "Smazat", "delete!");
		$delete->setConfirm(function($item) {
			return "Opravdu chcete smazat {$item->name}?";
		});

		$edit = $this->addActionHref("edit", "Upravit", "edit");

		$release = $this->addActionHref("release", "Vydat", "release!");
	}

}
