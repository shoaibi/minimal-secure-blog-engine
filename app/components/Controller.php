<?php
namespace GGS\Components;
use GGS\Helpers\CsrfUtils;
use GGS\Helpers\FormUtils;
use GGS\Helpers\StringUtils;
use GGS\Components\WebApplication;

/**
 * Base class for application controllers
 * Class Controller
 * @package GGS\Components
 */
abstract class Controller extends ApplicationComponent
{
    /**
     * Number of records to show a single page
     */
    const MAX_RECORDS_PER_PAGE      = 3;

    /**
     * @inheritdoc
     */
    public static function getInstance(array $config)
    {
        // left here for future use cases
    }

    /**
     * Utility method to get an instance of a model given an identifier in the query string
     * @param $modelClassName
     * @param string $queryParameter
     * @return \GGS\Components\Model
     */
    protected static function getModelByRequest($modelClassName, $queryParameter = 'id')
    {
        $id     = WebApplication::$request->getQueryStringParameter($queryParameter);
        if (!isset($id))
        {
            // whoopsie, can't live without id
            static::exitWithException("Invalid Request: Missing id.", 400);
        }
        if (!is_numeric($id)|| $id < 1)
        {
            // so far we are only using positive integer primary keys so it doesn't make sense to search for a model
            // with negative key
            static::exitWithException("Invalid Request: id must be positive integer", 400);
        }
        // convert id to a proper integer, no string values;
        $id             = intval($id);
        // get the qualified model class name with namespace
        $modelClassName = \GGS\Components\Model::getQualifiedModelClassName($modelClassName);
        // get the model
        $model          = $modelClassName::getByPk($id);
        if (!isset($model))
        {
            // model not found? how come? I know, a bad request.
            static::exitWithException("No records found for: {$id}", 404);
        }
        // found it.
        return $model;
    }

    /**
     * A wrapper around WebApplication's function with same name but with different arguments
     * @param $message
     * @param null $code
     */
    protected static function exitWithException($message, $code = null)
    {
        WebApplication::exitWithException(new \Exception($message, $code));
    }

    /**
     * Strip slashes from the super globals to reduce the \\ mess
     */
    protected static function stripSlashesFromGlobals()
    {
        // do i need to do this chore?
        if ((function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) ||
            (ini_get('magic_quotes_sybase') && (strtolower(ini_get('magic_quotes_sybase')) != "off")))
        {
            // FANTASTIC. BAD BAD BAD SYSADMIN, NO GIFT FOR YOU ON THE NEXT SYSADMIN DAY
            StringUtils::stripSlashesRecursive($_GET);
            StringUtils::stripSlashesRecursive($_POST);
            StringUtils::stripSlashesRecursive($_COOKIE);
        }
    }

    /**
     * Ensure request came from the app itself
     */
    protected static function ensureReferrer()
    {
        // get the referrer
        $referrer   = WebApplication::$request->getServerParameter('HTTP_REFERER');
        // does it has the application's url at start?
        if(strpos($referrer, WebApplication::$request->getBaseUrl(true)) !== 0)
        {
            // nah? Kick the moron out who couldn't even tweak HTTP_REFERER when spoofing forms.
            static::exitWithEatSpamException();
        }
    }

    /**
     * Sweet sweet honey pot validation for naughty bees.
     */
    protected static function ensureHoneyPotFieldIsNotSet()
    {
        $isHoneyPotFilled   = \GGS\Helpers\HoneyPotInputUtils::isHoneyPotInputFilled();
        if ($isHoneyPotFilled)
        {
            // what? it is set? KILL ALL BOTS
            static::exitWithEatSpamException();
        }
    }

    /**
     * Check if we have a CSRF happening
     * @param $action
     */
    protected static function ensureCsrfTokenValidity($action)
    {
        // is the token valid?
        $isCsrfTokenValid   = CsrfUtils::validateRequest($action);
        if (!$isCsrfTokenValid)
        {
            // nope? Come on, not you again.
            static::exitWithEatSpamException();
        }
    }

    /**
     * A wrapper to exit application for when spam checks fail
     */
    protected static function exitWithEatSpamException()
    {
        // Munch Munch
        static::exitWithException('Eat Spam!', 400);
    }

    /**
     * Bootstrap and run the specified action
     * @param $action
     */
    public function action($action)
    {
        $this->beforeAction($action);
        $this->$action();
        $this->afterAction($action);
    }

    /**
     * Hook that runs before calling action
     * @param $action
     */
    public function beforeAction($action)
    {
        // cleanup super globals
        static::stripSlashesFromGlobals();
        if (WebApplication::$request->isPostRequest())
        {
            // woah, a post request? hold it right there for the spam police.
            static::ensureReferrer();
            static::ensureHoneyPotFieldIsNotSet();
            static::ensureCsrfTokenValidity($action);
        }
    }

    /**
     * Hook that runs after action
     * @param $action
     */
    public function afterAction($action)
    {

    }
}