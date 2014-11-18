<?php
$config = array(
    'name'      => 'GGS BackendTask',
    'debug'     => true,
    // WebApplication components
    'components' => array(
        'database' => array(
            'connectionString' => 'mysql:host=localhost;dbname=ggs',
            'username' => 'ggs',
            'password' => 'ggs',
        ),
        'view'  => array(
            'path'  => APP_PATH . DIRECTORY_SEPARATOR . 'views',
        ),
    ),
);

return $config;