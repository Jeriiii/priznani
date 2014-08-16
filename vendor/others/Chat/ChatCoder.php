<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 *
 */

namespace POS\Chat;

/**
 * Description of Coder
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ChatCoder {
	/* !!! kodovani by melo byt deterministicke - zakodovani stejne hodnoty jsou take stejna !!! */

	/**
	 * Zakoduje vlozena data do stringu kvuli bezpecnosti (prevede je na retezec)
	 * Data lze dekodovat metodou decodeData
	 * @param mixed $data data ke kodovani
	 * @return String zakodovana data
	 */
	public function encodeData($data) {
		srand($data); //seed pro deterministmus
		return rand(100000, 999999) . '' . $data; //prida na zacatek sest cisel
	}

	/**
	 * Dekoduje data zakodovana metodou encodeData
	 * @param String $data zakodovana data
	 * @return String dekodovana data jako retezec
	 */
	public function decodeData($data) {
		return substr($data, 6);
	}

}
