<?php
namespace GGS\Components;

/**
 * A mini/stupid wrapper around PDO connection
 * Class Database
 * @package GGS\Components
 */
class Database extends ApplicationComponent
{
    /**
     * @var Database
     */
    private static $instance;

    /**
     * @var \PDO
     */
    protected $connection;

    /**
     * @var String
     */
    protected $dbType;

    /**
     * Setup connection
     * @param string $dsn
     * @param string $username
     * @param string $password
     */
    protected function __construct($dsn = '', $username = '', $password = '')
    {
        try
        {
            $this->connection = new \PDO($dsn, $username, $password);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            // we mostly set FETCH_MODE to FETCH_CLASS in model but lets have a reasonable default
            $this->connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            // set the db type to be later used in db specific operations
            $this->setDbTypeByDsn($dsn);
        }
        catch(\PDOException $e)
        {
            // no sql for me
            \GGS\Components\WebApplication::exitWithException($e, 'Unable to connect to database');
        }
    }

    /**
     * PHP would clean it up at the end of script and its not like we have a case where we close connection before that
     * but still..
     */
    public function __destruct()
    {
        $this->connection   = null;
    }

    /**
     * @inheritdoc
     */
    public static function getInstance(array $config)
    {
        if (!isset(static::$instance))
        {
            $connectionString   = null;
            $username           = null;
            $password           = null;
            extract($config);
            static::$instance   = new static($connectionString, $username, $password);
        }
        return static::$instance;
    }

    /**
     * Return connection to be used in models
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Set database type based on DSN
     * @param string $dsn
     */
    protected function setDbTypeByDsn($dsn = '')
    {
        $this->dbType   = substr($dsn, 0, strpos($dsn, ':'));
    }

    /**
     * Quote the provided string according to database type
     * @param $name
     * @return string
     */
    public function enquote($name)
    {
        if ($this->dbType   === 'mysql')
            return '`'.$name.'`';
        else
            return '"'.$name.'"';
        // add support for MSSQL
    }
}