<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace POSComponent\Chat;

/**
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
interface IChat {

	/**
	 * Pošle zprávu daného uživatele jinému uživateli
	 * @param int $idSender odesilatel
	 * @param int $idRecipient prijemce
	 * @param Strin $text text zpravy
	 */
	public function sendTextMessage($idSender, $idRecipient, $text);
}
