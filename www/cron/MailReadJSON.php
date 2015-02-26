<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cron;

/**
 * Description of MailReadJSON
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class MailReadJSON {

	public function __construct() {
		;
	}

	public function readUrl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch);
		curl_close($ch);

		var_dump(json_decode($result, true));
		die();
	}

}
