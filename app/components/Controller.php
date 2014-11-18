<?php
namespace GGS\Components;

abstract class Controller extends ApplicationComponent
{
    const   ROUTE_PARAMETER = 'r';

    public static function getInstance(array $config)
    {
        // left here for future use cases
    }

    public static function createUrl($controller, $action, array $queryParameters = array())
    {
        $currentUrl         = strtok($_SERVER['REQUEST_URI'], '?');
        $queryParameters    = array(static::ROUTE_PARAMETER => "{$controller}/{$action}") + $queryParameters;
        $queryString        = urldecode(http_build_query($queryParameters));
        return "{$currentUrl}?"  . $queryString;
    }

    protected static function getModelByRequest($modelClassName, $queryParameter = 'id')
    {
        if (!isset($_GET[$queryParameter]))
        {
            static::existWithException("Invalid Request: Missing id.", 400);
        }
        if (!is_numeric($_GET[$queryParameter])|| $_GET[$queryParameter] < 1)
        {
            static::existWithException("Invalid Request: id must be positive integer", 400);
        }
        $id             = intval($_GET[$queryParameter]);
        $modelClassName = \GGS\Components\Model::getQualifiedModelClassName($modelClassName);
        $model          = $modelClassName::getByPk($id);
        if (!isset($model))
        {
            static::existWithException("No records found for: {$id}", 404);
        }
        return $model;
    }
    protected static function existWithException($message, $code)
    {
        \GGS\Components\Application::exitWithException(new \Exception($message, $code));
    }

    public function beforeAction($action)
    {

    }

    public function afterAction($action)
    {

    }
}