<?php
namespace GGS\Components;

class Request extends ApplicationComponent
{
    const   ROUTE_PARAMETER = 'r';

    /**
     * @var Request
     */
    private static $instance;

    protected function __construct()
    {
    }

    public static function getInstance(array $config)
    {
        if (!isset(static::$instance))
        {
            static::$instance   = new static();
        }
        return static::$instance;
    }

    public function isAjaxRequest()
    {
        $requestedWith  = static::getServerParameter('HTTP_X_REQUESTED_WITH');
        return (isset($requestedWith) && $requestedWith === 'XMLHttpRequest');
    }

    public function isPostRequest()
    {
        $requestMethod  = static::getRequestMethodFromServer();
        return (isset($requestMethod) && !strcasecmp($requestMethod, 'POST'));
    }

    public function getPostParameter($name, $defaultValue = null)
    {
        return isset($_POST[$name]) ? $_POST[$name] : $defaultValue;
    }

    public function getQueryStringParameter($name, $defaultValue = null)
    {
        return isset($_GET[$name]) ? $_GET[$name] : $defaultValue;
    }

    public function getServerParameter($name, $defaultValue = null)
    {
        return isset($_SERVER[$name]) ? $_SERVER[$name] : $defaultValue;
    }

    public function getRequestMethodFromServer()
    {
        return static::getServerParameter('REQUEST_METHOD');
    }

    public function getUserIP()
    {
        $remoteAddr     = static::getServerParameter('REMOTE_ADDR');
        $forwardedFor   = static::getServerParameter('HTTP_X_FORWARDED_FOR', $remoteAddr);
        $clientIP       = static::getServerParameter('HTTP_CLIENT_IP', $forwardedFor);
        return (!empty($clientIP))? $clientIP : null;
    }

    public function getUserAgent()
    {
        $userAgent  = static::getServerParameter('HTTP_USER_AGENT');
        return (!empty($userAgent)) ? $userAgent : null;
    }

    public function getRouteFromQueryString()
    {
        return static::getQueryStringParameter(static::ROUTE_PARAMETER);
    }

    public function createUrl($controller, $action, array $queryParameters = array())
    {
        $queryParameters    = array(static::ROUTE_PARAMETER => "{$controller}/{$action}") + $queryParameters;
        $queryString        = urldecode(http_build_query($queryParameters));
        return static::getBaseUrl() . '?'. $queryString;
    }

    public function getBaseUrl($absolute = false)
    {
        $prefix         = null;
        if ($absolute)
        {
            $prefix     = static::resolveProtocolAndHostName();
        }
        $requestUri     = static::getServerParameter('REQUEST_URI');
        $scriptPath     = strtok($requestUri, '?');
        return $prefix . $scriptPath;
    }

    public function getBaseUrlWithoutScript($absolute = false)
    {
        $baseUrlWithScript      = static::getBaseUrl($absolute);
        $baseUrlWithoutScript   = substr($baseUrlWithScript, 0, strrpos($baseUrlWithScript, '/', -1));
        return $baseUrlWithoutScript;
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

    public function redirect($url, $statusCode = 302)
    {
        header('Location: '.$url, true, $statusCode);
        exit;
    }
}