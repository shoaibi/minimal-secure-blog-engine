<?php
/**
 * This file contains application config.
 * Any attributes that contain scalar values are assigned to Web application's direct properties.
 * Attributes containing nested arrays are usually special configs, say components.
 */
$config = array(
    'name'      => 'GGS BackendTask',
    // do we want a little more detailed errors than just "Aw, Snap!"?
    'debug'     => true,
    // WebApplication components
    'components' => array(
        'database' => array(
            // remember to update the connection string, username and password here.
            'connectionString' => 'mysql:host=localhost;dbname=ggs',
            'username' => 'ggs',
            'password' => 'ggs',
        ),
        'view'  => array(
            // directory where views are to ebe found
            'path'  => APP_PATH . DIRECTORY_SEPARATOR . 'views',
        ),
        // left here to ensure component is enabled and loaded into application so we can use Application::$request
        // instead of namespaced class name
        // additional config options may be made available in future.
        'request'   => array(),
    ),
);

return $config;