<?php
namespace GGS\Components;

abstract class WebApplication extends Application
{
    public static $defaultAction    = 'index';

    public static function afterRun(array $config = array())
    {
        static::parseRequestAndInvokeControllerAction();
    }

    public static function exitWithException(\Exception $e, $message = 'WebApplication encountered an error.')
    {
        http_response_code(500);
        parent::exitWithException($e, $message);
    }

    protected static function parseRequestAndInvokeControllerAction()
    {
        $route  = static::ensureRouteIsProvided();
        list($controllerClassName, $actionMethodName)   = static::parseControllerClassNameAndActionMethodNameFromRequest($route);
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
        $route  = static::$request->getRouteFromQueryString();
        if (empty($route))
        {
            static::exitWithException(new \Exception('Bad Request: No route specified', 400));
        }
        return $route;
    }

    protected static function parseControllerClassNameAndActionMethodNameFromRequest($route)
    {
        list($controller, $action)  = static::parseControllerAndActionFromRequest($route);
        $action                     = (isset($action)) ? $action : static::$defaultAction;
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
        $controller->beforeAction($actionMethodName);
        $controller->$actionMethodName();
        $controller->afterAction($actionMethodName);
    }

    protected static function resolveDefaultAction()
    {
        return 'index';
    }
}