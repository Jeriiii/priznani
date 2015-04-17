<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 17.4.2015
 */

namespace NetteExt;

/**
 * Třída sloužící ke kódování a dekódování vložených dat,
 * !!! kodovani by melo byt deterministicke - zakodovani stejne hodnoty jsou take stejna
 *  (kdyz zakoduji treba 87, vyhodi to vzdy stejnou kodovanou hodnotu) !!!
 *
 * @author Petr Kukrál <p.kukral@kukral.eu> a Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class DataCoder extends \Nette\Object {

	/**
	 * Zakoduje vlozena data do stringu kvuli bezpecnosti (prevede je na retezec)
	 * Data lze dekodovat metodou decodeData
	 * @param mixed $data data ke kodovani
	 * @return String zakodovana data
	 */
	public static function encode($data) {
		srand($data); //seed pro deterministmus
		return rand(100000, 999999) . '' . $data; //prida na zacatek sest cisel
	}

	/**
	 * Dekoduje data zakodovana metodou encodeData
	 * @param String $data zakodovana data
	 * @return String dekodovana data jako retezec
	 */
	public static function decode($data) {
		return substr($data, 6);
	}

}
