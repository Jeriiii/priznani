<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt\Install\DB;

use NetteExt\Install\Messages;

/**
 * Vytvoří nový patch
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Patch {

	/** @var string Název patche */
	private $patchName;

	/** @var string Celá cesta k patchím. */
	private $patchesPath;

	/** @var \NetteExt\Install\DB\Sql SQL co se má vložit do patche. */
	private $sql;

	/** @var Messages Zprávy co se zobrazí uživateli */
	private $messages;

	/** Název složky pro staré scripty v patchy */
	const SCRIPT_DIR_NAME = "parts";

	public function __construct(Messages $messages) {
		$this->messages = $messages;
		$this->patchName = $this->getPatchName();
		$this->sql = new Sql(null, FALSE);
		$this->patchesPath = $this->sql->getSQLRootDir() . "/" . Sql::DIR_PATCH . "/";
	}

	/**
	 * Vytvoří path z existujících SQL na úpravu DB (bez dat)
	 */
	public function create() {
		$this->sql->addDevelopSql();

		$this->createPatchDirs();
		$this->createPatchFiles();

		$this->messages->addMessage("Patch " . $this->patchName . " byl vytvořen. Zkontrolujte jeho správnost a poté smažte staré scripty");
	}

	/**
	 * Vytvoří složku na patch.
	 */
	private function createPatchDirs() {
		mkdir($this->patchesPath . $this->patchName, 741);
		/* složka na všechny původní scripty */
		mkdir($this->patchesPath . $this->patchName . "/" . self::SCRIPT_DIR_NAME, 741);
	}

	/**
	 * Vytvoří soubor patch s celým SQL a zkopíruje i všechny původní scripty.
	 */
	private function createPatchFiles() {
		$patch = fopen($this->patchesPath . $this->patchName . "/patch.sql", "w");
		foreach ($this->sql->getSql() as $scriptName => $script) {
			$this->copyPatchScript($script, $scriptName);
			$this->writeScriptToPatch($patch, $scriptName, $script);
		}
		fclose($patch);
	}

	/**
	 * Zapíše script do patche.
	 * @param string $patch Cesta k patchím.
	 * @param string $scriptName Název scriptu.
	 * @param string $script Celý script
	 */
	private function writeScriptToPatch($patch, $scriptName, $script) {
		$fullScriptName = "/************** " . $scriptName . " **************/";
		fwrite($patch, $fullScriptName . "\n" . $script . "\n");
	}

	/**
	 * Zkopíruje script do složky s patchem.
	 * @param string $script Celý script
	 * @param string $scriptName Název scriptu.
	 */
	private function copyPatchScript($script, $scriptName) {
		$scriptFile = fopen($this->patchesPath . $this->patchName . "/" . self::SCRIPT_DIR_NAME . "/" . $scriptName, "w");
		fwrite($scriptFile, $script);
		fclose($scriptFile);
	}

	/**
	 * Vrátí název patche.
	 * @return string Název patche.
	 */
	private function getPatchName() {
		$now = new \Nette\DateTime;
		$nowFormat = date_format($now, "Y-m-d");
		$patchName = "patch-" . $nowFormat;
		return $patchName;
	}

}
