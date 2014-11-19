<?php
namespace GGS\Components;
use GGS\Helpers\CsrfHelper;
use GGS\Helpers\FormHelper;
use GGS\Helpers\StringHelper;
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
            StringHelper::stripSlashesRecursive($_GET);
            StringHelper::stripSlashesRecursive($_POST);
            StringHelper::stripSlashesRecursive($_COOKIE);
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
            static::exitWithEatSpamException('Invalid Referrer!');
        }
    }

    /**
     * Sweet sweet honey pot validation for naughty bees.
     */
    protected static function ensureHoneyPotFieldIsNotSet()
    {
        $isHoneyPotFilled   = \GGS\Helpers\HoneyPotInputHelper::isHoneyPotInputFilled();
        if ($isHoneyPotFilled)
        {
            // what? it is set? KILL ALL BOTS
            static::exitWithEatSpamException('Honey Pot is filled.');
        }
    }

    /**
     * Check if we have a CSRF happening
     * @param $action
     */
    protected static function ensureCsrfTokenValidity($action)
    {
        // is the token valid?
        $isCsrfTokenValid   = CsrfHelper::validateRequest($action);
        if (!$isCsrfTokenValid)
        {
            // nope? Come on, not you again.
            static::exitWithEatSpamException('CSRF validation Failed.');
        }
    }

    /**
     * A wrapper to exit application for when spam checks fail
     */
    protected static function exitWithEatSpamException($message = null)
    {
        // Munch Munch
        static::exitWithException($message . PHP_EOL . 'Eat Spam!', 400);
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