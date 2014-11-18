<?php
namespace GGS\Components;

abstract class Controller extends ApplicationComponent
{
    const   ROUTE_PARAMETER = 'r';

    public static function getInstance(array $config)
    {
        // left here for future use cases
    }

    public static function isAjaxRequest()
    {
        $requestedWith  = static::getServerParameter('HTTP_X_REQUESTED_WITH');
        return (isset($requestedWith) && $requestedWith === 'XMLHttpRequest');
    }

    public static function isPostRequest()
    {
        $requestMethod  = static::getRequestMethodFromServer();
        return (isset($requestMethod) && !strcasecmp($requestMethod, 'POST'));
    }

    public static function getPostParameter($name, $defaultValue = null)
    {
        return isset($_POST[$name]) ? $_POST[$name] : $defaultValue;
    }

    public static function getQueryStringParameter($name, $defaultValue = null)
    {
        return isset($_GET[$name]) ? $_GET[$name] : $defaultValue;
    }

    public static function getServerParameter($name, $defaultValue = null)
    {
        return isset($_SERVER[$name]) ? $_SERVER[$name] : $defaultValue;
    }

    public static function getRequestMethodFromServer()
    {
        return static::getServerParameter('REQUEST_METHOD');
    }

    public static function getRouteFromQueryString()
    {
        return static::getQueryStringParameter(static::ROUTE_PARAMETER);
    }

    public static function createUrl($controller, $action, array $queryParameters = array())
    {
        $queryParameters    = array(static::ROUTE_PARAMETER => "{$controller}/{$action}") + $queryParameters;
        $queryString        = urldecode(http_build_query($queryParameters));
        return static::getBaseUrl() . '?'. $queryString;
    }

    public static function getBaseUrl()
    {
        $requestUri     = static::getServerParameter('REQUEST_URI');
        return strtok($requestUri, '?');
    }

    public static function redirect($url, $statusCode = 302)
    {
        header('Location: '.$url, true, $statusCode);
        exit;
    }

    protected static function getModelByRequest($modelClassName, $queryParameter = 'id')
    {
        $id     = static::getQueryStringParameter($queryParameter);
        if (!isset($id))
        {
            static::existWithException("Invalid Request: Missing id.", 400);
        }
        if (!is_numeric($id)|| $id < 1)
        {
            static::existWithException("Invalid Request: id must be positive integer", 400);
        }
        $id             = intval($id);
        $modelClassName = \GGS\Components\Model::getQualifiedModelClassName($modelClassName);
        $model          = $modelClassName::getByPk($id);
        if (!isset($model))
        {
            static::existWithException("No records found for: {$id}", 404);
        }
        return $model;
    }

    protected static function existWithException($message, $code = null)
    {
        \GGS\Components\Application::exitWithException(new \Exception($message, $code));
    }

    public function beforeAction($action)
    {
        // TODO: @Shoaibi: Critical: Check for referrer, Check for CSRF
        //md5(uniqid(rand(), true));
    }

    public function afterAction($action)
    {

    }
}