<?php
namespace GGS\Components;

abstract class Controller extends ApplicationComponent
{
    const   ROUTE_PARAMETER = 'r';

    public static function getInstance(array $config)
    {
        // left here for future use cases
    }

    public static function createUrl($controller, $action)
    {
        $currentUrl = strtok($_SERVER['REQUEST_URI'], '?');
        return "{$currentUrl}?" . static::ROUTE_PARAMETER . "={$controller}/{$action}";
    }
}