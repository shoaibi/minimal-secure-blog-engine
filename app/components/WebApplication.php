<?php
namespace GGS\Components;

abstract class WebApplication extends Application
{
    public static function run(array $config = array())
    {
        parent::run($config);
        static::parseRequestAndInvokeControllerAction();
    }

    protected static function parseRequestAndInvokeControllerAction()
    {
        static::ensureRouteIsProvided();
        list($controllerClassName, $actionMethodName)   = static::parseControllerClassNameAndActionMethodNameFromRequest();
        static::invokeControllerActionIfExists($controllerClassName, $actionMethodName);
    }

    protected static function parseControllerAndActionFromRequest($route)
    {
        return explode('/', $route);
    }

    protected static function resolveControllerClassName($controllerRouteKey)
    {
        return static::resolveClassNameWithNamespace($controllerRouteKey, '\GGS\Controllers\\');
    }

    protected static function resolveActionName($actionRouteKey)
    {
        return 'action' . ucfirst($actionRouteKey);
    }

    protected static function ensureRouteIsProvided()
    {
        if (empty($_GET['r']))
        {
            static::exitWithException(new \Exception('Bad Request: No route specified', 400));
        }
    }

    protected static function parseControllerClassNameAndActionMethodNameFromRequest()
    {
        list($controller, $action)  = static::parseControllerAndActionFromRequest($_GET['r']);
        if (!isset($controller, $action))
        {
            static::exitWithException(new \Exception('Bad Request: Missing controller or action', 400));
        }
        $controllerClassName        = static::resolveControllerClassName($controller);
        $actionMethodName           = static::resolveActionName($action);
        return array($controllerClassName, $actionMethodName);
    }

    protected static function invokeControllerActionIfExists($controllerClassName, $actionMethodName)
    {
        $controller                 = new $controllerClassName();
        if (!method_exists($controller, $actionMethodName) || !is_callable(array($controller, $actionMethodName)))
        {
            static::exitWithException(new \Exception('Unable to find requested action', 404));
        }
        $controller->$actionMethodName();
    }
}