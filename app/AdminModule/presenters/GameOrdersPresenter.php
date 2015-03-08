<?php

namespace AdminModule;

use Nette;
use App\Forms as Frm;
use Grido\Grid;

/**
 * Pracuje s daty objednávek her a obstarává jejich vykreslení
 *
 * @author Daniel Holubář
 */
class GameOrdersPresenter extends AdminSpacePresenter {

	/**
	 * @var \POS\Model\EshopGameOrderDao
	 * @inject
	 */
	public $eshopGameOrderDao;

	/**
	 * komponenta vykresluje přehled objednávek
	 * @param type $name
	 */
	protected function createComponentGrid($name) {
		$grid = new Grid($this, $name);
		$grid->setModel($this->eshopGameOrderDao->getTable());

		$this->addColumns($grid);
		$this->addFilters($grid);

		//řazení podle id vzestupně
		$grid->setDefaultSort(array('id' => 'ASC'));
	}

	/**
	 * Přidá filtry do gridu.
	 * @param Grido\Grid $grid
	 */
	public function addFilters($grid) {
		/* filtry */
		$grid->addFilterText('name', 'Jméno');
		$grid->addFilterText('surname', 'Příjmení');
		$grid->addFilterText('email', 'Email');

		//Filtr vyfiltruje objednávky s datem rovným nebo větším vloženému
		$grid->addFilterDate('create_from', 'Objednávky od:')->setWhere(function($value, $selection) {
			$date = date('Y-m-d H:i:s', strtotime($value));
			$selection->where('create >= ?', $date);
		});
		//Filtr vyfiltruje objednávky s datem rovným nebo menším vloženému
		//K datu se přičte 23:59:59
		$grid->addFilterDate('create_to', 'Objednávky do:')->setWhere(function($value, $selection) {
			$date = date('Y-m-d H:i:s', strtotime($value . ' + 86399 seconds'));
			$selection->where('create <= ?', $date);
		});
		/* konec filtrů */

		/* seznam her */
		$list = array(
			'' => '',
			'vasnivefantazie' => 'Vášnivé fantazie',
			'nespoutanevzruseni' => 'Nespoutené vzrušení',
			'zhaveukolypropary' => 'Žhavé úkoly pro páry',
			'ceskahralasky' => 'Česká hra lásky',
			'nekonecnaparty' => 'Nekonečná párty',
			'sexyaktivity' => 'Sexy aktivity',
			'ceskachlastacka' => 'Česká chlastačka',
			'sexyhratky' => 'Sexy hrátky',
			'manazeruvsen' => 'Manažerův sen',
		);
		/* konec seznamu */

		/* nastavení podmínek filtru her */
		$grid->addFilterSelect('game', 'Druh hry', $list)
			->setCondition(array(
				'vasnivefantazie' => array('vasnivefantazie', '=', 1),
				'nespoutanevzruseni' => array('nespoutanevzruseni', '=', 1),
				'zhaveukolypropary' => array('zhaveukolypropary', '=', 1),
				'ceskahralasky' => array('ceskahralasky', '=', 1),
				'nekonecnaparty' => array('nekonecnaparty', '=', 1),
				'sexyaktivity' => array('sexyaktivity', '=', 1),
				'ceskachlastacka' => array('ceskachlastacka', '=', 1),
				'sexyhratky' => array('sexyhratky', '=', 1),
				'manazeruvsen' => array('manazeruvsen', '=', 1),
		));
		/* konec nastavení */
	}

	/**
	 * Přidá sloupce do gridu
	 * @param Grido\Grid $grid
	 */
	public function addColumns($grid) {
		$grid->addColumnText("name", "Jméno");
		$grid->addColumnText("surname", "Příjmení");
		$grid->addColumnText("email", "Email");
		$grid->addColumnDate("create", "Objednáno");

		$grid->addColumnText("vasnivefantazie", "v.f.")->setReplacement(array(1 => "✓", 0 => "×"));
		$grid->addColumnText("nespoutanevzruseni", "n.v.")->setReplacement(array(1 => "✓", 0 => "×"));
		$grid->addColumnText("zhaveukolypropary", "z.u.")->setReplacement(array(1 => "✓", 0 => "×"));
		$grid->addColumnText("ceskahralasky", "c.h.")->setReplacement(array(1 => "✓", 0 => "×"));
		$grid->addColumnText("nekonecnaparty", "n.p.")->setReplacement(array(1 => "✓", 0 => "×"));
		$grid->addColumnText("sexyaktivity", "s.a.")->setReplacement(array(1 => "✓", 0 => "×"));
		$grid->addColumnText("ceskachlastacka", "c.ch.")->setReplacement(array(1 => "✓", 0 => "×"));
		$grid->addColumnText("milackuuklidto", "m.u.")->setReplacement(array(1 => "✓", 0 => "×"));
		$grid->addColumnText("sexyhratky", "s.h.")->setReplacement(array(1 => "✓", 0 => "×"));
		$grid->addColumnText("manazeruvsen", "m.s.")->setReplacement(array(1 => "✓", 0 => "×"));
	}

}
