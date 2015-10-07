<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace JKB\Component\Statistics\Daily;

use Nette\Utils\DateTime;
use Nette\Utils\ArrayHash;
use JKB\Model\IS\UserItemDao;
use NetteExt\DaoBox;
use Nette\Database\Table\ActiveRow;
use Nette\Security\User;
use ArrowStatistics\Manager;
use Nette\Application\UI\Presenter;
use JKB\Component\Statistics\Row;
use JKB\Component\Statistics\Cell;
use POS\Model\ChatMessagesDao;

/**
 * Denní statistiky poboček zobrazené v tabulce s šipkami zlepšení / zhoršení
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class TableUserMessagesStatisics extends TableStatisics {

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var \POS\Model\ChatMessagesDao @inject */
	public $chatMessagesDao;

	/** @var User Přihlášený uživatel. */
	private $user;

	public function __construct(DaoBox $daoBox, Presenter $p, User $user) {
		parent::__construct($p);

		$this->userDao = $daoBox->userDao;
		$this->chatMessagesDao = $daoBox->chatMessagesDao;
		$this->user = $user;
		$this->anchor = 'table-branch-statistics';
		$this->header = 'Denní statistiky odeslaných zpráv';
	}

	/**
	 * Vrátí pobočky podle přihlášeného uživatele a jeho role.
	 * @return \Nette\Database\Table\Selection Pobočky.
	 */
	protected function getDataByUser() {
		$users = $this->userDao->getInRoleAdvancedUsers();

		return $users;
	}

	/**
	 * Vytvoří řádek v tabulce ze záznamu v databázi.
	 * @param ActiveRow $userDB Uživatel systému.
	 * @return ArrayHash Pobočka
	 */
	protected function createRowFromDB($userDB) {
		$user = new Row($userDB->id, $userDB->user_name);
		$user->sum = 0;

		return $user;
	}

	/**
	 * Spočítá statistiky za jeden den pro jeden řádek v tabulce = spočítá jednu buňku tabulky.
	 * @param DateTime $day Den, ve kterém se mají statistiky počítat.
	 * @param Row $item Data k řádku, se kterým se právě pracuje.
	 * mají statistiky počítat (např. pro pobočku Brno nebo pro uživatele Láďa)
	 * Jde o pole (count => počet slov ve zprávách v danném dni)
	 */
	protected function getDayliStat(DateTime $day, Row $item) {
		$senderId = $item->id;
		$count = $this->chatMessagesDao->countWordByDay($day, $senderId);

		$text = $count;

		$cell = new Cell($count, $text);

		return $cell;
	}

}
