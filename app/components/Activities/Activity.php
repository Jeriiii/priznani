<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Nette\Utils\Html;

/**
 * Stará se o vložení správného textu do aktivity.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
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
	 * @return string Text aktivity
	 */
	public function getUserStatusAction($creator, $type, $status, $activityID) {

		$element = Html::el('div', array("class" => "activity-item"));
		$link = Html::el('a')->href('#')->addAttributes(array("data-activity" => $activityID));

		if ($type == "comment") {
			$element->setText('Uživatel ' . $creator . ' okomentoval váš status "' . $status . '"');
		} else {
			$element->setText('Uživatel ' . $creator . ' lajknul váš status "' . $status . '"');
		}
		$link->add($element);
		return $link;
	}

	/**
	 * Vrátí text k aktivitě statusu podle typu.
	 * @param string $creator Jméno vlastníka aktivity
	 * @param string $type Typ aktivity (comment, like, ...)
	 * @param \Nette\Database\Table\ActiveRow $image objekt obrázku
	 * @param int $activityID ID aktivity
	 * @return string Text aktivity
	 */
	public function getUserImageAction($creator, $type, $image, $activityID) {

		$element = Html::el('div', array("class" => "activity-item"));
		$attributes = array("imageID" => $image->id, "galleryID" => $image->galleryID);
		$link = Html::el('a')->href('profil.galleries/image', $attributes)->addAttributes(array("data-activity" => $activityID));

		if ($type == "comment") {
			$element->setText('Uživatel ' . $creator . ' okomentoval váš obrázek ' . $image->name);
		} else {
			$element->setText('Uživatel ' . $creator . ' lajknul váš obrázek ' . $image->name);
		}
		$link->add($element);
		return $link;
	}

	/**
	 * Vrátí text k aktivitě statusu podle typu.
	 * @param string $creator Jméno vlastníka aktivity
	 * @param string $type Typ aktivity
	 * @param int $activityID ID aktivity
	 * @return string Text aktivity
	 */
	public function getUserAction($creator, $type, $activityID) {

		$element = Html::el('div', array("class" => "activity-item"));
		$link = Html::el('a')->href('#')->addAttributes(array("data-activity" => $activityID));

		if ($type == 'poke') {
			$element->setText("Uživatel " . $creator . " vás štouchl!");
		}
		$link->add($element);
		return $link;
	}

}
