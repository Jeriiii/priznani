<?php

// absolute filesystem path to this web root
define('WWW_DIR', __DIR__);

// absolute filesystem path to the application root
define('APP_DIR', WWW_DIR . '/../app');

// absolute filesystem path to the libraries
define('LIBS_DIR', WWW_DIR . '/../vendor/others/');


$_SERVER['TESTING'] = TRUE;
////nette
$container = require __DIR__ . "/../../bootstrap.php";
$GLOBALS['container'] = $container;

