<?php

/**
 * Form presenter.
 *
 * Obsluha administrační části systému.
 * Formuláře.
 *
 * @author     Petr Kukrál
 * @package    Safira
 */

namespace AdminModule;

use Nette\Application\UI\Form as Frm;
use POS\Model\BaseConfessionDao;
use Nette\DateTime;

class FormsPresenter extends AdminSpacePresenter {

	/**
	 * @var \POS\Model\FormDao
	 * @inject
	 */
	public $formDao;

	/**
	 * @var \POS\Model\AdviceDao
	 * @inject
	 */
	public $adviceDao;

	/**
	 * @var \POS\Model\PartyDao
	 * @inject
	 */
	public $partyDao;

	/**
	 * @var \POS\Model\ConfessionDao
	 * @inject
	 */
	public $confessionDao;

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	/**
	 * @var \POS\Model\StreamDao
	 * @inject
	 */
	public $streamDao;





	/* smazat */
	public $type;
	public $show_mark;

	public function actionForms() {
		$this->template->forms = $this->formDao->getAll("DESC");
	}

	/**
	 * @param type $type O jaké jde přiznání - sex|pařba|poradna
	 * @param string $show_mark Jake prispevky se budou zobrazovat - virizene-nevyrizne-vse.
	 * @param type $selectAdminID Třízení přiznání podle ID tohoto odmina.
	 */
	public function renderFormsX($type, $show_mark, $selectAdminID = null) {
		$this->show_mark = $show_mark;
		$this->template->type = $type;
		$this->template->show_mark = $show_mark;

		$count_unmark = $this->getDao($type)->countUnprocessed();
		$this->template->unmark_counter = $count_unmark; //obsahuje počet nevyřízených přiznání pro danou sekci

		$last = $this->getDao($type)->getLastScheduledConfession();

		/* třídění podle vybraného admina */
		if (!empty($selectAdminID)) {
			$this->template->selectAdmin = $this->userDao->find($selectAdminID)->user_name;
		} else {
			$this->template->selectAdmin = "všichni";
		}

		/* pro třízení podle admina */
		$this->template->admins = $this->userDao->getInRoleAdmin();

		if (!$this->isAjax()) {
			$this->setPaginatorFormsXToTemplate($type, $show_mark, $selectAdminID);
		}
		$this->template->last = $last;
	}

	/**
	 * Nastaví paginátor pro formsX do šablony
	 */
	private function setPaginatorFormsXToTemplate($type, $show_mark, $selectAdminID = NULL) {
		$show_mark = $this->decodeMark($show_mark);
		$forms = $this->getDao($type)->getInMarkAndAdmin($show_mark, $selectAdminID);

		$vp = new \VisualPaginator($this, 'vp');
		$page = $vp->page;
		$paginator = $vp->getPaginator();
		$paginator->setItemCount($forms->count()); // celkový počet položek
		$paginator->setItemsPerPage(50); // počet položek na stránce
		$paginator->setPage($page); // číslo aktuální stránky
		$this->template->forms = $forms
			->limit($paginator->getLength(), $paginator->getOffset());
	}

	/**
	 * Dokóduje značku z šablony na značku z DAO.
	 * @param string $mark Název značky ze šablony.
	 */
	public function decodeMark($mark) {
		switch ($mark) {
			case "unmark":
				return BaseConfessionDao::MARK_UNPROCESSED;
			case "mark":
				return BaseConfessionDao::MARK_PROCESSED;
			case "rubbish":
				return BaseConfessionDao::MARK_INRUBBISH;
			case "fb": case "toFB":
				return BaseConfessionDao::MARK_TOFB;
			case "duplicate":
				return BaseConfessionDao::MARK_DUPLICATE;
			default:
				return $mark;
		}
	}

	/**
	 * Enkóduje značku z DAO do srozumitelného názvu.
	 * @param int $mark Číslo značky.
	 */
	public function encodeMark($mark) {
		switch ($mark) {
			case 0:
				return "nevyřízeno";
			case 1:
				return "vyřízeno";
			case 2:
				return "koš";
			case 3:
				return "na FB";
			case 4:
				return "duplicitní";
			default:
				return "neznámý";
		}
	}

	public function renderConfessionDetail($id, $type) {
		$confession = $this->getDao($type)->find($id);
		$admin = $this->userDao->find($confession->adminID);

		$mark = $this->encodeMark($confession->mark);

		$this->template->mark = $mark;
		$this->template->admin = $admin;
		$this->template->confession = $confession;
	}

	/**
	 * Přehodí a naplánuje přiznání mezi dvěma tabulkami
	 * @param int $id
	 * @param int $type
	 */
	public function handleChangeAndschedule($id, $type) {
		if ($type == 1) { //poradna
			$daoSource = $this->confessionDao;
			$daoTarget = $this->adviceDao;
			$typeTarget = 2;
		} elseif ($type == 2) { //priznani
			$daoSource = $this->adviceDao;
			$daoTarget = $this->confessionDao;
			$typeTarget = 1;
		}

		$this->switchConfession($id, $typeTarget, $daoSource, $daoTarget);
		$this->userDao->increaseAdminScore($this->getUser()->id, 3);
		$this->flashMessage("Text byl přehozen a naplánován");
		$this->redirect("this");
	}

	/**
	 * Přehodí přiznání z jedné tabulky do druhé
	 * @param int $id ID přiznání
	 * @param \POS\Model\BaseConfessionDao $daoSource Zdrojová tabulka
	 * @param \POS\Model\BaseConfessionDao $daoTarget Cílová tabulka
	 */
	public function switchConfession($id, $type, $daoSource, $daoTarget) {
		$confession = $daoSource->find($id);

		$targetID = $daoTarget->insertNoteCreate(
			$confession->note, $confession->create
		);

		$this->scheduling($type, $targetID);
		$daoSource->delete($id);
	}

	public function handleMoveToRubbish($id, $type) {
		$this->getDao($type)->updateMark($id, BaseConfessionDao::MARK_INRUBBISH);
		$this->userDao->increaseAdminScore($this->getUser()->id, 1);

		$this->flashMessage("Text byl přesunut do koše");
		$this->redirect("this");
	}

	public function getDao($type) {
		if ($type == 1) {
			return $this->confessionDao;
		} elseif ($type == 2) {
			return $this->adviceDao;
		} else {
			return $this->partyDao;
		}
	}

	protected function createComponentFormNewForm($name) {
		return new Frm\formNewForm($this, $name);
	}

	public function handlemarkForm($id, $type, $doit, $show_mark) {
		$mark = $this->decodeMark($doit);
		$this->mark($mark, $id, $type);

		if ($this->isAjax("changeButton")) {
			if ($this->decodeMark($show_mark) == BaseConfessionDao::MARK_UNPROCESSED) {
				$this->getDao($type)->assignAdmin($id, $this->getUser()->id);
			}

			$this->setPaginatorFormsXToTemplate($type, $show_mark);

			$this->template->id_color_row = $id;
			$this->userDao->increaseAdminScore($this->getUser()->id, 2);
			$this->invalidateControl('changeButton');
		}
	}

	/**
	 * Změní stav přiznání popřípadě ho i naplánuje
	 * @param int $mark Nový stav přiznání.
	 * @param int $id ID přiznání.
	 * @param int $type Typ přiznání sex|pařba|poradna.
	 */
	public function mark($mark, $id, $type) {
		switch ($mark) {
			case BaseConfessionDao::MARK_PROCESSED:
				$this->chooseAndSchedul($id, $type);
				break;
			case BaseConfessionDao::MARK_UNPROCESSED:
			case BaseConfessionDao::MARK_DUPLICATE:
			case BaseConfessionDao::MARK_INRUBBISH:
				$this->getDao($type)->updateMark($id, $mark);
				break;
			case BaseConfessionDao::MARK_TOFB:
				$this->chooseAndSchedul($id, $type);
				$this->getDao($type)->updateMarkWasOnFB($id, $mark);
				break;
		}
	}

	/**
	 * Vybere metodu pro naplánování a zavolá ji
	 * @param int $confNewID Přiznání, které se má naplánovat
	 * @param int $type Typ přiznání
	 */
	public function chooseAndSchedul($confNewID, $type) {
		if ($type == "1") {
			/* sex */
			$this->scheduling($type, $confNewID);
		} else {
			/* poradna a pařba */
			$this->scheduling($type, $confNewID, $addMinutes = 30);
		}
	}

	/**
	 * Naplanuje přiznání.
	 * @param int $type Typ přiznání sex|poradna|pařba.
	 * @param int $confNewID ID přiznání, co se má naplánovat.
	 * @param int $addMinutes Za kolik minut od posledního naplánovaného přiznání.
	 */
	private function scheduling($type, $confNewID, $addMinutes = 20) {
		$confNew = $this->getDao($type)->find($confNewID);
		if ($confNew->was_on_fb == 0) {
			/* naplánování přiznání a vložení do streamu */

			/* posledni naplanovane přiznání */
			$confLast = $this->getDao($type)->getLastScheduledConfession();

			$oldReleaseDate = $confLast->release_date;

			$newReleaseDate = $this->getReleaseDate($oldReleaseDate, $addMinutes);

			/* naplánování */
			$markProcessed = BaseConfessionDao::MARK_PROCESSED;
			$this->getDao($type)->updateMarkDate($confNewID, $markProcessed, $newReleaseDate);

			$this->addToStream($type, $confNewID);
		} else {
			/* jen přehození, přiznání již bylo naplánováno a hozeno do streamu dříve */
			$markProcessed = BaseConfessionDao::MARK_PROCESSED;
			$this->getDao($type)->updateMark($confNewID, $markProcessed);
		}
	}

	/**
	 * Vloží přiznání do streamu.
	 * @param int $type Typ přiznání sex|poradna|pařba.
	 * @param int $confNewID ID přiznání, co se má naplánovat.
	 */
	public function addToStream($type, $confNewID) {
		if ($type == 1) {
			/* vydání přiznání na streamu */
			$confession = $this->confessionDao->find($confNewID);
			$this->streamDao->addNewConfession($confNewID, $confession->create);
		} elseif ($type == 2) {
			/* vydání poradny na streamu */
			$advice = $this->adviceDao->find($confNewID);
			$this->streamDao->addNewAdvice($confNewID, $advice->create);
		}
	}

	/**
	 * Získá čas vydání = čas na kdy se má přiznání naplánovat.
	 * @param DateTime $oldReleaseDate Poslední naplánované přiznání
	 * @param int $addMinutes Za kolik minut od posledního naplánovaného přiznání.
	 * @return DateTime
	 */
	public function getReleaseDate($oldReleaseDate, $addMinutes) {
		$newReleaseDate = new \Nette\DateTime($oldReleaseDate);

		$modifyMorningHour = array(
			/* hour => modify hour */
			"01" => "1",
			"02" => "1",
			"03" => "2",
			"04" => "2",
			"05" => "2",
		);

		$now = new DateTime();

		/* kdyz by melo byt vydani do minulosti */
		if ($newReleaseDate < $now) {
			$newReleaseDate = $now;
			$newReleaseDate->setTime(date_format($newReleaseDate, "H") + 1, 0);
		}

		$hour = date_format($oldReleaseDate, "H");

		/* modify time */
		if (array_key_exists($hour, $modifyMorningHour)) {
			$newReleaseDate->modify("+" . $modifyMorningHour[$hour] . " hours");
		} else {
			$newReleaseDate->modify("+" . $addMinutes . " minutes");
		}

		return $newReleaseDate;
	}

	public function handledeleteFormX($type, $id) {
		$this->getDao($type)->delete($id);
		$this->userDao->increaseAdminScore($this->getUser()->id, 1);

		$this->flashMessage("Položka ve formuláři byla smazána.");
		$this->redirect("this");
	}

}

?>