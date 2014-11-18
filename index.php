<?php
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'app');
define('VENDOR_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'vendor');
require_once(VENDOR_PATH . DIRECTORY_SEPARATOR . 'autoload.php');
$config     = require_once(APP_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'main.php');
GGS\Components\WebApplication::run($config);