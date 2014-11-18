<?php
namespace GGS\Components;
use GGS\Helpers\FormUtils;
use GGS\Helpers\StringUtils;

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

    public static function getBaseUrl($absolute = false)
    {
        $prefix         = null;
        if ($absolute)
        {
            $prefix     = static::resolveProtocolAndHostName();
        }
        //http://$_SERVER[HTTP_HOST]
        $requestUri     = static::getServerParameter('REQUEST_URI');
        $scriptPath     = strtok($requestUri, '?');
        return $prefix . $scriptPath;
    }

    protected function resolveProtocolAndHostName()
    {
        $hostName       = static::getServerParameter('HTTP_HOST');
        $https          = static::getServerParameter('HTTPS');
        if (!isset($https))
        {
            $serverProtocol = static::getServerParameter('SERVER_PROTOCOL', 'http');
        }
        else
        {
            $serverProtocol = empty($https) ? 'http' : 'https';
        }
        return "{$serverProtocol}://${hostName}";
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
            static::exitWithException("Invalid Request: Missing id.", 400);
        }
        if (!is_numeric($id)|| $id < 1)
        {
            static::exitWithException("Invalid Request: id must be positive integer", 400);
        }
        $id             = intval($id);
        $modelClassName = \GGS\Components\Model::getQualifiedModelClassName($modelClassName);
        $model          = $modelClassName::getByPk($id);
        if (!isset($model))
        {
            static::exitWithException("No records found for: {$id}", 404);
        }
        return $model;
    }

    protected static function exitWithException($message, $code = null)
    {
        \GGS\Components\WebApplication::exitWithException(new \Exception($message, $code));
    }

    protected static function stripSlashesFromGlobals()
    {
        if ((function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) ||
            (ini_get('magic_quotes_sybase') && (strtolower(ini_get('magic_quotes_sybase')) != "off")))
        {
            StringUtils::stripSlashesRecursive($_GET);
            StringUtils::stripSlashesRecursive($_POST);
            StringUtils::stripSlashesRecursive($_COOKIE);
        }
    }

    protected static function ensureReferrer()
    {
        $referrer   = static::getServerParameter('HTTP_REFERER');
        if(strpos($referrer, static::getBaseUrl(true)) !== 0)
        {
            static::exitWithEatSpamException();
        }
    }

    protected static function ensureHoneyPotFieldIsNotSet()
    {
        $fieldValue = static::getPostParameter(FormUtils::SPAM_CHECK_INPUT_NAME);
        if ($fieldValue !== '')
        {
            static::exitWithEatSpamException();
        }
    }

    protected static function ensureCsrfTokenValidatity()
    {
        // TODO: @Shoaibi: Critical: Check for referrer, Check for CSRF
        //md5(uniqid(rand(), true));
    }

    protected static function exitWithEatSpamException()
    {
        static::exitWithException('Eat Spam!', 400);
    }

    public function beforeAction($action)
    {
        static::stripSlashesFromGlobals();
        if (static::isPostRequest())
        {
            static::ensureReferrer();
            static::ensureHoneyPotFieldIsNotSet();
            static::ensureCsrfTokenValidatity();
        }
    }

    public function afterAction($action)
    {

    }
}