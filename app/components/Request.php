<?php
namespace GGS\Components;

/**
 * Request component to deal with the data in http request
 * Class Request
 * @package GGS\Components
 */
class Request extends ApplicationComponent
{
    /**
     * Parameter to look for when finding requested route
     */
    const   ROUTE_PARAMETER = 'r';

    /**
     * @var Request
     */
    private static $instance;

    /**
     * Left here for future use, no special Request component properties available yet.
     */
    protected function __construct()
    {
    }

    /**
     * @inheritdoc
     */
    public static function getInstance(array $config)
    {
        if (!isset(static::$instance))
        {
            static::$instance   = new static();
        }
        return static::$instance;
    }

    /**
     * Resolve if current request is ajax
     * @return bool
     */
    public function isAjaxRequest()
    {
        $requestedWith  = static::getServerParameter('HTTP_X_REQUESTED_WITH');
        return (isset($requestedWith) && $requestedWith === 'XMLHttpRequest');
    }

    /**
     * Resolve if current request is Post
     * @return bool
     */
    public function isPostRequest()
    {
        $requestMethod  = static::getRequestMethodFromServer();
        return (isset($requestMethod) && !strcasecmp($requestMethod, 'POST'));
    }

    /**
     * Get value of a parameter from the POST super global, return default if missing
     * @param $name
     * @param null $defaultValue
     * @return null
     */
    public function getPostParameter($name, $defaultValue = null)
    {
        return isset($_POST[$name]) ? $_POST[$name] : $defaultValue;
    }

    /**
     * Get value of a parameter from the GET super global, return default if missing
     * @param $name
     * @param null $defaultValue
     * @return null
     */
    public function getQueryStringParameter($name, $defaultValue = null)
    {
        return isset($_GET[$name]) ? $_GET[$name] : $defaultValue;
    }

    /**
     * Get value of a parameter from the SERVER super global, return default if missing
     * @param $name
     * @param null $defaultValue
     * @return null
     */
    public function getServerParameter($name, $defaultValue = null)
    {
        return isset($_SERVER[$name]) ? $_SERVER[$name] : $defaultValue;
    }

    /**
     * Get REQUEST_METHOD from SERVER super global
     * @return null
     */
    public function getRequestMethodFromServer()
    {
        return static::getServerParameter('REQUEST_METHOD');
    }

    /**
     * Get current visitor's IP
     * @return null|string
     */
    public function getUserIP()
    {
        // Order of preference: HTTP_CLIENT_IP, HTTP_X_FORWARDED_FOR, REMOTE_ADDR
        $remoteAddr     = static::getServerParameter('REMOTE_ADDR');
        $forwardedFor   = static::getServerParameter('HTTP_X_FORWARDED_FOR', $remoteAddr);
        $clientIP       = static::getServerParameter('HTTP_CLIENT_IP', $forwardedFor);
        return (!empty($clientIP))? $clientIP : null;
    }

    /**
     * Get current visitor's user agent
     * @return null
     */
    public function getUserAgent()
    {
        $userAgent  = static::getServerParameter('HTTP_USER_AGENT');
        return (!empty($userAgent)) ? $userAgent : null;
    }

    /**
     * Get requested route from query string
     * @return null
     */
    public function getRouteFromQueryString()
    {
        return static::getQueryStringParameter(static::ROUTE_PARAMETER);
    }

    /**
     * Resolve a url for provided controller, action and queryParameters
     * @param $controller
     * @param $action
     * @param array $queryParameters
     * @param bool $absolute
     * @return string
     */
    public function createUrl($controller, $action, array $queryParameters = array(), $absolute = false)
    {
        // using + to ensure that the route parameter is the first one in query string, not that it matters
        $queryParameters    = array(static::ROUTE_PARAMETER => "{$controller}/{$action}") + $queryParameters;
        // urldecode? because http_build_query also encodes the / in the route and its super ugly
        $queryString        = urldecode(http_build_query($queryParameters));
        return static::getBaseUrl($absolute) . '?'. $queryString;
    }

    /**
     * Resolve the base url
     * @param bool $absolute
     * @return string
     */
    public function getBaseUrl($absolute = false)
    {
        $prefix         = null;
        if ($absolute)
        {
            // need to resolve the procotol and hostname prefix
            $prefix     = static::resolveProtocolHostNameAndPort();
        }
        // get the request uri
        $requestUri     = static::getServerParameter('REQUEST_URI');
        // get the part before query string
        $scriptPath     = strtok($requestUri, '?');
        return $prefix . $scriptPath;
    }

    /**
     * Resolbe base url without the script name
     * @param bool $absolute
     * @return string
     */
    public function getBaseUrlWithoutScript($absolute = false)
    {
        // get base url
        $baseUrlWithScript      = static::getBaseUrl($absolute);
        // remove anything after last /, mostly like script name
        $baseUrlWithoutScript   = substr($baseUrlWithScript, 0, strrpos($baseUrlWithScript, '/', -1));
        return $baseUrlWithoutScript;
    }

    /**
     * Resolve current request's protocol and hostname
     * @return string
     */
    protected function resolveProtocolHostNameAndPort()
    {
        $port           = static::getServerParameter('SERVER_PORT');
        $hostName       = static::getServerParameter('HTTP_HOST');
        // preference order: HTTPS, SERVER_PROTOCOL, http
        $https          = static::getServerParameter('HTTPS');
        if (!isset($https))
        {
            $serverProtocol = static::getServerParameter('SERVER_PROTOCOL', 'http');
        }
        else
        {
            $serverProtocol = empty($https) ? 'http' : 'https';
        }
        $urlPrefix  = "{$serverProtocol}://${hostName}";
        if (!in_array($port, array(80, 443)))
        {
            $urlPrefix  = rtrim($urlPrefix, '/') . "{$port}/";
        }
        return $urlPrefix;
    }

    /**
     * Set the redirection header
     * @param $url
     * @param int $statusCode
     */
    public function redirect($url, $statusCode = 302)
    {
        header('Location: '.$url, true, $statusCode);
        exit;
    }
}