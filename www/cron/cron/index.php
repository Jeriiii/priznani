<?php

require_once 'autoload.php'; //načtení tříd NETTE, načítají se jen ty třídy, co jsou potřeba
require_once 'MailReadJSON.php';
require_once 'DBConnection.php';
require_once 'urlBuilder.php';

use Tracy\Debugger;

Debugger::enable(Debugger::DEVELOPMENT);

use Cron\MailReadJSON;

$mailer = new MailReadJSON();

/* emaily s aktualitami */
$mailer->sendEmails(urlBuilder('mail-to-json'));
$mailer->readUrl(urlBuilder('mail-is-sended')); //označí emaily jako odeslané

/* emaily pro bývalé uživatele */
$mailer->setEmailType(MailReadJSON::TYPE_EMAIL_OLD_USERS);
$mailer->sendEmails(urlBuilder('mail-to-old-users-json'));
$mailer->readUrl(urlBuilder('mail-old-users-is-sended')); //označí emaily jako odeslané
