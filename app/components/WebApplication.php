<?php
namespace GGS\Components;

/**
 * Class to handle boostrap and run of web app
 * Class WebApplication
 * @package GGS\Components
 */
abstract class WebApplication extends Application
{
    /**
     * Default action to invoke when there is none specified
     * @var string
     */
    public static $defaultAction    = 'index';

    /**
     * @inheritdoc
     */
    public static function afterRun(array $config = array())
    {
        static::parseRequestAndInvokeControllerAction();
    }

    /**
     * @inheritdoc
     */
    public static function exitWithException(\Exception $e, $message = 'WebApplication encountered an error.')
    {
        // if we have code set in exception we respond with that as http status code, else 500 it is.
        $httpCode       = $e->getCode();
        $httpCode       = (isset($httpCode))? $httpCode : 500;
        http_response_code($httpCode);
        parent::exitWithException($e, $message);
    }

    /**
     * Parse request and run requested action
     */
    protected static function parseRequestAndInvokeControllerAction()
    {
        // get the route
        $route  = static::ensureRouteIsProvided();
        // get the controller class name and action method name
        list($controllerClassName, $actionMethodName)   = static::parseControllerClassNameAndActionMethodNameFromRequest($route);
        // run the action if it exists
        static::invokeControllerActionIfExists($controllerClassName, $actionMethodName);
    }

    /**
     * Parse controller and action from request.
     * @param $route
     * @return array
     */
    protected static function parseControllerAndActionFromRequest($route)
    {
        $controllerAction       = explode('/', $route);
        // request does not have an action specified? resolve to default action
        $controllerAction[1]    = (isset($controllerAction[1])) ? $controllerAction[1] : static::$defaultAction;
        return $controllerAction;
    }

    /**
     * Resolve qualified controller component class name based on config key
     * @param $controllerRouteKey
     * @return string
     */
    protected static function resolveControllerClassName($controllerRouteKey)
    {
        return static::resolveClassNameWithNamespace($controllerRouteKey, '\GGS\Controllers\\');
    }

    /**
     * Resolve action function name based on route key
     * @param $actionRouteKey
     * @return string
     */
    protected static function resolveActionName($actionRouteKey)
    {
        return 'action' . ucfirst($actionRouteKey);
    }

    /**
     * Resolve route from request
     * @return string
     */
    protected static function ensureRouteIsProvided()
    {
        $route  = static::$request->getRouteFromQueryString();
        if (empty($route))
        {
            static::exitWithException(new \Exception('Bad Request: No route specified', 400));
        }
        return $route;
    }

    /**
     * Resolve controller class namea nd action function name based on route
     * @param $route
     * @return array
     */
    protected static function parseControllerClassNameAndActionMethodNameFromRequest($route)
    {
        // get the controller and action
        list($controller, $action)  = static::parseControllerAndActionFromRequest($route);

        if (!isset($controller, $action))
        {
            // if either is empty, die
            static::exitWithException(new \Exception('Bad Request: Missing controller or action', 400));
        }
        $controllerClassName        = static::resolveControllerClassName($controller);
        $actionMethodName           = static::resolveActionName($action);
        return array($controllerClassName, $actionMethodName);
    }

    /**
     * Run the requested controller action
     * @param $controllerClassName
     * @param $actionMethodName
     */
    protected static function invokeControllerActionIfExists($controllerClassName, $actionMethodName)
    {
        $controller                 = new $controllerClassName();
        if (!method_exists($controller, $actionMethodName) || !is_callable(array($controller, $actionMethodName)))
        {
            // do we have a publicly visible function against the action method name?
            static::exitWithException(new \Exception('Unable to find requested action', 404));
        }
        // invoke the action, watch the magic happen
        $controller->action($actionMethodName);
    }
}