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

use \Nette\Database\Table\ActiveRow;
use Nette\Utils\Html;

class ShowProfHelper {
	/* názvy helperů */

	const NAME = "showProf";
	const NAME_MIN = "showProfMin";
	const NAME_MIN_DIV = "showProfMinDiv";

	/* typy nastavení */
	const TYPE_EL_SPAN = "span";
	const TYPE_EL_TABLE = "tab";

	/** @var GetImgPathHelper */
	private $getImgPathHelper;

	/** @var array Callback na fci link */
	private $linkCallback;

	public function __construct(GetImgPathHelper $getImgPathHelper, $linkCallback) {
		$this->getImgPathHelper = $getImgPathHelper;
		$this->linkCallback = $linkCallback;
	}

	/**
	 * Vytvoří miniaturu profilové fotky užvatele, vedle něj napíše jeho jméno
	 * a celé to obalí do odkazu na Profil:Show presenter
	 * @param \Nette\Database\Table\ActiveRow|Nette\ArrayHash $user
	 * @param array|null $href Vlasní odkaz. První prvek pole je odkaz, druhý prvek je pole parametrů.
	 * @param boolen $min TRUE = Zobrazení bez jména vedle fotky.
	 * @param string $el název elementu, kterým bude profil obalen
	 * @return \Nette\Utils\Html
	 */
	public function showProf($user, $href = null, $min = FALSE, $el = 'span') {
		return $this->createShowProf($user, $href, $min, $el);
	}

	/**
	 * Vytvoří celý element ve spanech.
	 * @param \Nette\Database\Table\ActiveRow|Nette\ArrayHash $user Zádnam o uživateli.
	 * @param array $href Vlasní odkaz. První prvek pole je odkaz, druhý prvek je pole parametrů.
	 * @param boolean $min TRUE = Zobrazení bez jména vedle fotky.
	 * @param string $el název elementu, kterým bude profil obalen
	 * @return \Nette\Utils\Html
	 */
	private function createShowProf($user, $href, $min, $el) {
		/* Výsledek je celý v odkazu */
		$elLink = $this->createLink($href, $user);

		/* profilová fotka */
		$elPhoto = $this->createPhoto($el, $user);
		$elLink->add($elPhoto);

		/* přidá jméno */
		if (!$min) {
			$elName = Html::el($el, $user->user_name);
			$elLink->add($elName);
		}



		return $elLink;
	}

	/**
	 * Vytvoří profilovou fotku
	 * @param string $el Typ elementu, ko kterého se má obrázek vložit.
	 * @param \Nette\Database\Table\ActiveRow $user Zádnam o uživateli.
	 * @return \Nette\Utils\Html
	 */
	private function createPhoto($el, $user) {
		$elPhoto = Html::el($el, array('class' => 'generatedProfile'));
		$img = Html::el("img");

		if (isset($user->profilFotoID)) {
			$src = $this->getImgPathHelper->getImgMinPath($user->profilFoto, GetImgPathHelper::TYPE_USER_GALLERY);
		} else {
			//$femalePhoto = $user->property->user_property == "women" ? TRUE : FALSE;
			$src = $this->getImgPathHelper->getImgDefProf(/* $femalePhoto */);
		}

		$img->src($src);
		$img->alt($user->user_name);
		$elPhoto->add($img);
		return $elPhoto;
	}

	/**
	 * Vytvoří link kolem odkazu
	 * @param array $href Vlasní odkaz. První prvek pole je odkaz, druhý prvek je pole parametrů.
	 * @param \Nette\Database\Table\ActiveRow $user Zádnam o uživateli.
	 * @return \Nette\Utils\Html
	 */
	private function createLink($href, $user) {
		$elLink = Html::el('a');

		/* pokud není href nastavený, použije se odkaz na už. profil */
		if (empty($href)) {
			$href = array(":Profil:Show:", array("id" => $user->id));
		}

		$href = $this->link($href);
		$elLink->href($href);
		$elLink->title($user->user_name);

		return $elLink;
	}

	/**
	 * Vytváří linky stejně jako metoda link z presenteru.
	 * @param array $href Vlasní odkaz. První prvek pole je odkaz, druhý prvek je pole parametrů.
	 * @return string Výsledný odkaz.
	 */
	private function link($href) {
		return call_user_func_array($this->linkCallback, $href);
	}

}
