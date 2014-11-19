<?php
namespace GGS\Components;

/**
 * This contains a generic class that is responsible for bootstrapping the code and running requested actions
 * Class Application
 * @package GGS\Components
 */
abstract class Application extends Object
{
    /**
     * Application name
     * @var String
     */
    public static $name = 'GGS';

    /**
     * @var bool
     */
    public static $debug = false;

    /**
     * @var null|Database
     */
    public static $database = null;

    /**
     * @var null|View
     */
    public static $view = null;

    /**
     * @var null|Request
     */
    public static $request = null;

    /**
     * Bootstrap application and run it
     * @param array $config
     */
    public static function run(array $config = array())
    {
        static::beforeRun($config);
        static::init($config);
        static::afterRun($config);
    }

    /**
     * Bootstrap application properties and components
     * @param array $config
     */
    protected static function init(array $config = array())
    {
        static::beforeInit($config);
        static::setDirectProperties($config);
        $componentsConfig   = (isset($config['components']))? $config['components'] : array();
        static::setComponents($componentsConfig);
        ini_set('display_errors', static::$debug);
        static::setErrorReporting();
        static::afterInit($config);
    }

    /**
     * Bootstrap application properties
     * @param array $config
     */
    protected static function setDirectProperties(array $config)
    {
        $directProperties   = \GGS\Helpers\ArrayUtils::getAllNonNestedValues($config);
        foreach ($directProperties as $property => $value)
        {
            static::${$property}        = $value;
        }
    }

    /**
     * Bootstrap application components
     * @param array $componentsConfig
     */
    protected static function setComponents(array $componentsConfig)
    {
        foreach ($componentsConfig as $component => $config)
        {
            $componentClassName     =   static::resolveComponentNameFromConfigKey($component);
            static::${$component}   =   $componentClassName::getInstance($config);
        }
    }

    /**
     * Resolve a class name with its namespace.
     * @param $className
     * @param $namespace
     * @return string
     */
    protected static function resolveClassNameWithNamespace($className, $namespace)
    {
        return $namespace . ucfirst($className);
    }

    /**
     * Resolve a component's class name provided its config key
     * @param $componentConfigKey
     * @return string
     */
    protected static function resolveComponentNameFromConfigKey($componentConfigKey)
    {
        return static::resolveClassNameWithNamespace($componentConfigKey, '\GGS\Components\\');
    }

    /**
     * Something went big bada boom! complain about korben dallas and die
     * @param \Exception $e
     * @param string $message
     */
    public static function exitWithException(\Exception $e, $message = 'Application encountered an error.')
    {
        if (static::$debug)
        {
            $message    .= PHP_EOL . 'Error: ' . PHP_EOL . $e->getMessage();
        }
        die($message);
    }

    /**
     * Set error_reporting according to $debug
     */
    protected static function setErrorReporting()
    {
        if (static::$debug)
        {
            ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_STRICT);
        }
        else
        {
            ini_set('error_reporting', E_ALL & ~E_NOTICE);
        }
    }

    /**
     * Hook called before init()
     * @param array $config
     */
    protected static function beforeInit(array $config = array())
    {

    }

    /**
     * Hook called after init()
     * @param array $config
     */
    protected static function afterInit(array $config = array())
    {

    }

    /**
     * Hook called before run()
     * @param array $config
     */
    protected static function beforeRun(array $config = array())
    {

    }

    /**
     * Hook called after run()
     * @param array $config
     */
    protected static function afterRun(array $config = array())
    {

    }
}