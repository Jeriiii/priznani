<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Helper pro zobrazení uživatele se jménem a miniaturou obrázku
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace NetteExt\Helper;

class ShowUserDataHelper {
	/* názvy helperů */

	const SEXY_LABEL_NAME = "sexyLabel";


	/*
	 * Pole pro každý typ uživatele - pokud má alespoň «klíč» bodů, bude se mu říkat «hodnota»
	 */
	/* číslo type v databázi */
	const TYPE_MAN = 1;

	/* konstanta určující hranice bodů, kterých může uživatel dosáhnout - když je muž */

	public static $LABEL_NAMES_MAN = array(
		0 => 'Zajíček',
		1 => 'Misionář',
		10 => 'Kanec',
		100 => 'Loverboy',
		500 => 'Sexy drak',
		1000 => 'Cassanova',
	);

	/* číslo type v databázi */

	const TYPE_WOMAN = 2;

	/* konstanta určující hranice bodů, kterých může uživatel dosáhnout - když je žena */

	public static $LABEL_NAMES_WOMAN = array(
		0 => 'Žákyňka',
		1 => 'Pusinka',
		10 => 'Kočka',
		100 => 'Dračice',
		500 => 'Královna sexu',
		1000 => 'Bohyně',
	);

	/* číslo type v databázi */

	const TYPE_COUPLE = 3;

	/* konstanta určující hranice bodů, kterých může uživatel dosáhnout - když je pár */

	public static $LABEL_NAMES_COUPLE = array(
		0 => 'Zajíčci',
		1 => 'Mazlíci',
		10 => 'Kanci',
		100 => 'Sexy',
		500 => 'Králové sexu',
		1000 => 'Bohové sexu',
	);

	/* číslo type v databázi */

	const TYPE_COUPLE_MAN = 4;
//
//	/* konstanta určující hranice bodů, kterých může uživatel dosáhnout - když je pár mužů */
//
//	private $LABEL_NAMES_COUPLE_MAN = array(
//		0 => 'Páreček',
//		10 => 'Králíci',
//		50 => 'Milovníci',
//		200 => 'Ďáblíci',
//	);
//
	/* číslo type v databázi */

	const TYPE_COUPLE_WOMAN = 5;
//
//	/* konstanta určující hranice bodů, kterých může uživatel dosáhnout - když je pár žen */
//
//	private $LABEL_NAMES_COUPLE_WOMAN = array(
//		0 => 'Páreček',
//		10 => 'Králíci',
//		50 => 'Milovníci',
//		200 => 'Ďáblíci',
//	);
//
	/* číslo type v databázi */

	const TYPE_GROUP = 6;

//
//	/* konstanta určující hranice bodů, kterých může uživatel dosáhnout - když je pár žen */
//
//	private $LABEL_NAMES_GROUP = array(
//		0 => 'Páreček',
//		10 => 'Králíci',
//		50 => 'Milovníci',
//		200 => 'Ďáblíci',
//	);
//
//	/* hranice bodů a jména pro případ chyby */
//	private $LABEL_NAMES_DEFAULT = array(
//		0 => 'Tajemný stín'
//	);

	/**
	 * Vypíše sexy nálepku uživatele podle jeho vlastností a skóre
	 * @param \Nette\Database\Table\ActiveRow|Nette\ArrayHash $user
	 * @return string řetězec nálepky
	 */
	public function showSexyLabel($user) {
		$label = '';
		$score = $user->property->score;
		$userType = $user->property->type;

		switch ($userType) {
			case self::TYPE_MAN:
				$label = $this->getSexyLabel($score, self::$LABEL_NAMES_MAN);
				break;

			case self::TYPE_WOMAN:
				$label = $this->getSexyLabel($score, self::$LABEL_NAMES_WOMAN);
				break;

			case self::TYPE_COUPLE || self::TYPE_COUPLE_MAN || self::TYPE_COUPLE_WOMAN || self::TYPE_GROUP:
				$label = $this->getSexyLabel($score, self::$LABEL_NAMES_COUPLE);
				break;
			default:
				$label = $this->getSexyLabel($score, self::$LABEL_NAMES_DEFAULT);
				break;
		}

		return $label;
	}

	/**
	 * Vrátí label z pole, jehož klíč je největší z těch, co jsou menší než skóre
	 * (tedy hodnotu toho klíče, který je nejvyšší a zároveň menší než dané skóre)
	 * @param type $score skóre
	 * @param array $labelNames pole ohodnocení z konstanty
	 * @return string label nejlepší hodnoty
	 */
	private function getSexyLabel($score, array $labelNames) {
		$bestScore = 0; //nejlepší dosažená meta (skóre z pole)
		$bestLabel = '';
		foreach ($labelNames as $minScore => $label) {
			if ($score >= $minScore && $minScore >= $bestScore) {//počítá i s případem, kdy klíče nejsou v poli popřadě
				$bestScore = $minScore;
				$bestLabel = $label;
			}
		}
		return $bestLabel;
	}

	/**
	 * Vrátí info o daném labelu.
	 * @param int $type Typ uživatele.
	 * @return array Pole s informacemi o popisku.
	 */
	private static function getLabelInfo($type) {
		switch ($type) {
			case self::TYPE_MAN:
				$label = self::$LABEL_NAMES_MAN;
				break;

			case self::TYPE_WOMAN:
				$label = self::$LABEL_NAMES_WOMAN;
				break;

			case self::TYPE_COUPLE || self::TYPE_COUPLE_MAN || self::TYPE_COUPLE_WOMAN || self::TYPE_GROUP:
				$label = self::$LABEL_NAMES_COUPLE;
				break;
		}

		return $label;
	}

	/**
	 * Vrátí text s informacemi o nálepkách k danému typu uživateli.
	 * @param int $type Typ uživatele.
	 * @return string Informace o nálepkách.
	 */
	public static function getLabelInfoText($type) {
		$arrInfo = self::getLabelInfo($type);
		$infoText = "";
		foreach ($arrInfo as $score => $name) {
			$infoText = $infoText . $score . " - " . $name . "<br />";
		}
		return $infoText;
	}

}
