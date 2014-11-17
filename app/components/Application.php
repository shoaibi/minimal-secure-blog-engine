<?php
namespace GGS\Components;

/**
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
     * @var Database
     */
    public static $database = null;

    /**
     * @var View
     */
    public static $view = null;

    /**
     * @param array $config
     */
    public static function run(array $config = array())
    {
        static::setDirectProperties($config);
        $componentsConfig   = (isset($config['components']))? $config['components'] : array();
        static::setComponents($componentsConfig);
        ini_set('display_errors', static::$debug);
        static::setErrorReporting();
    }

    protected static function setDirectProperties(array $config)
    {
        $directProperties   = \GGS\Helpers\ArrayUtils::getAllNonNestedValues($config);
        foreach ($directProperties as $property => $value)
        {
            static::${$property}        = $value;
        }
    }

    protected static function setComponents(array $componentsConfig)
    {
        foreach ($componentsConfig as $component => $config)
        {
            $componentClassName     =   static::resolveComponentNameFromConfigKey($component);
            static::${$component}   =   $componentClassName::getInstance($config);
        }
    }

    protected static function resolveClassNameWithNamespace($className, $namespace)
    {
        return $namespace . ucfirst($className);
    }

    protected static function resolveComponentNameFromConfigKey($componentConfigKey)
    {
        return static::resolveClassNameWithNamespace($componentConfigKey, '\GGS\Components\\');
    }

    public static function exitWithException(\Exception $e, $message = 'Application encountered an error.')
    {
        if (static::$debug)
        {
            $message    .= PHP_EOL . 'Error: ' . PHP_EOL . $e->getMessage();
        }
        die($message);
    }

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
}