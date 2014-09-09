<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

use Nette\Utils\Html;

/**
 * Stará se o vložení správného textu do aktivity.
 *
 * @author Daniel Holubář
 */
class Activity {

	public function __construct() {

	}

	/**
	 * Vrátí text k aktivitě statusu podle typu.
	 * @param string $creator Jméno vlastníka aktivity
	 * @param string $type Typ aktivity (comment, like, ...)
	 * @param string $status Text statusu
	 * @param int $activityID ID aktivity
	 * @return array elementy pro složení aktivity
	 */
	public function getUserStatusAction($creator, $type, $status, $activityID) {

		$result = array();
		if ($type == "comment") {
			$result["divText"] = 'Uživatel ' . $creator . ' okomentoval váš status "' . $status . '"';
		} else {
			$result["divText"] = 'Uživatel ' . $creator . ' lajknul váš status "' . $status . '"';
		}
		$result["divClass"] = "activity-item";
		$result["href"] = "#";
		$result["activityID"] = $activityID;

		return $result;
	}

	/**
	 * Vrátí text k aktivitě statusu podle typu.
	 * @param string $creator Jméno vlastníka aktivity
	 * @param string $type Typ aktivity (comment, like, ...)
	 * @param \Nette\Database\Table\ActiveRow $image objekt obrázku
	 * @param int $activityID ID aktivity
	 * @return array elementy pro složení aktivity
	 */
	public function getUserImageAction($creator, $type, $image, $activityID) {
		$result = array();
		if ($type == "comment") {
			$result["divText"] = 'Uživatel ' . $creator . ' okomentoval váš obrázek ' . $image->name;
		} else {
			$result["divText"] = 'Uživatel ' . $creator . ' lajknul váš obrázek ' . $image->name;
		}
		$result["divClass"] = "activity-item";
		$result["href"] = 'profil.galleries/image?imageID=' . $image->id . "&galleryID=" . $image->galleryID;
		$result["activityID"] = $activityID;

		return $result;
	}

	/**
	 * Vrátí text k aktivitě statusu podle typu.
	 * @param string $creator Jméno vlastníka aktivity
	 * @param string $type Typ aktivity
	 * @param int $activityID ID aktivity
	 * @return array elementy pro složení aktivity
	 */
	public function getUserAction($creator, $type, $activityID) {

		$result = array();
		if ($type == "poke") {
			$result["divText"] = "Uživatel " . $creator . " vás štouchl!";
		}
		$result["divClass"] = "activity-item";
		$result["href"] = '#';
		$result["activityID"] = $activityID;

		return $result;
	}

}
