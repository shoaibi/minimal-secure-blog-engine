<?php
// this causes a lot of mess when enabled. Even though we handle the special case in Controller's beforeAction
// lets not try to burden the system.
ini_set('magic_quotes_gpc', false);

// some useful constants.
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'app');
define('VENDOR_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'vendor');

// get the psr4 autoloader running
require_once(VENDOR_PATH . DIRECTORY_SEPARATOR . 'autoload.php');

// get the system config
$config     = require_once(APP_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'main.php');

// now do some real work
\GGS\Components\WebApplication::run($config);