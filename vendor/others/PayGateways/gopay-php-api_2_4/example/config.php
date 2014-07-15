<?php
define('GOID', 8540279704);
define('SECURE_KEY', "ocxgXEL5psb7PAllKuCSblc9");

/*
 * defaultni jazykova mutace platebni brany GoPay
 */
define('LANG', 'cs');

/*
 * URL eshopu - pro urceni absolutnich cest 
 */
define('HTTP_SERVER', 'http://www.eshop.cz/');

define('SUCCESS_URL', HTTP_SERVER . 'example/view_pages/payment_success.php');
define('FAILED_URL', HTTP_SERVER . 'example/view_pages/payment_failed.php');

/*
 * URL skriptu volaneho pri navratu z platebni brany
 */
define('CALLBACK_URL', HTTP_SERVER . 'example/soap/callback.php');

/*
 * URL skriptu vytvarejiciho platbu na GoPay
 */
define('ACTION_URL', HTTP_SERVER . 'example/soap/payment.php');

/**
 *  Volba Testovaciho ci Provozniho prostredi
 *  Testovaci prostredi - GopayConfig::TEST
 *  Provozni prostredi  - GopayConfig::PROD
 */
require_once(dirname(__FILE__) . "/../api/gopay_config.php");
GopayConfig::init(GopayConfig::TEST);

?>