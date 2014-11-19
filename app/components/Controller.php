<?php
namespace GGS\Components;
use GGS\Helpers\CsrfUtils;
use GGS\Helpers\FormUtils;
use GGS\Helpers\StringUtils;
use GGS\Components\WebApplication;

abstract class Controller extends ApplicationComponent
{
    public static function getInstance(array $config)
    {
        // left here for future use cases
    }

    public function createUrl($controller, $action, array $queryParameters = array())
    {
        return WebApplication::$request->createUrl($controller, $action, $queryParameters);
    }

    public function redirect($url, $statusCode = 302)
    {
        WebApplication::$request->redirect($url, $statusCode);
    }

    protected static function getModelByRequest($modelClassName, $queryParameter = 'id')
    {
        $id     = WebApplication::$request->getQueryStringParameter($queryParameter);
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
        WebApplication::exitWithException(new \Exception($message, $code));
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
        $referrer   = WebApplication::$request->getServerParameter('HTTP_REFERER');
        if(strpos($referrer, WebApplication::$request->getBaseUrl(true)) !== 0)
        {
            static::exitWithEatSpamException();
        }
    }

    protected static function ensureHoneyPotFieldIsNotSet()
    {
        $fieldValue = WebApplication::$request->getPostParameter(FormUtils::SPAM_CHECK_INPUT_NAME);
        if ($fieldValue !== '')
        {
            static::exitWithEatSpamException();
        }
    }

    protected static function ensureCsrfTokenValidity($action)
    {
        $isCsrfTokenValid   = CsrfUtils::validateRequest($action);
        if (!$isCsrfTokenValid)
        {
            static::exitWithEatSpamException();
        }
    }

    protected static function exitWithEatSpamException()
    {
        static::exitWithException('Eat Spam!', 400);
    }

    public function beforeAction($action)
    {
        static::stripSlashesFromGlobals();
        if (WebApplication::$request->isPostRequest())
        {
            static::ensureReferrer();
            static::ensureHoneyPotFieldIsNotSet();
            static::ensureCsrfTokenValidity($action);
        }
    }

    public function afterAction($action)
    {

    }
}