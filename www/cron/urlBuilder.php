<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 2.3.2015
 */

/**
 * Vytvoří url ze zadané action
 * @param string $action Action presenteru.
 */
function urlBuilder($action = 'mail-to-json') {
	$domain = 'http://localhost/nette/pos/www';
	$user = 'userName=mailuser&userPassword=a10b06001';
	$presenter = 'cron-email';
	$url = $domain . '/' . $presenter . '/' . $action . '?' . $user;
	return $url;
}
