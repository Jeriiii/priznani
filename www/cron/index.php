<?php

include_once 'MailReadJSON.php';

function __autoload($className) {
	$dirs = array('Mail');

	foreach ($dirs as $dir) {
		if (file_exists($dir . '/' . $className . '.php')) {

		}
	}
}

$url = 'http://localhost/nette/pos/www/cron/mail-to-json?userName=mailuser&userPassword=a10b06001';
$mailer = new Cron\MailReadJSON();
$mailer->readUrl($url);

$message = new Nette\Mail\Message();
$message->setFrom('info@priznaniosexu.cz');
$message->addTo('test@test.cz');
$message->setBody('Toto je tělo emailu.');

$sendMailer = new \Nette\Mail\SendmailMailer();
$sendMailer->send($message);

