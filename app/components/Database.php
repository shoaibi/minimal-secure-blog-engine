<?php
namespace GGS\Components;

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

    protected function __construct($dsn = '', $username = '', $password = '')
    {
        try
        {
            $this->connection = new \PDO($dsn, $username, $password);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $this->setDbTypeByDsn($dsn);
        }
        catch(\PDOException $e)
        {
            \GGS\Components\WebApplication::exitWithException($e, 'Unable to connect to database');
        }
    }

    public function __destruct()
    {
        $this->connection   = null;
    }

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

    public function getConnection()
    {
        return $this->connection;
    }

    protected function setDbTypeByDsn($dsn = '')
    {
        $this->dbType   = substr($dsn, 0, strpos($dsn, ':'));
    }

    public function enquote($name)
    {
        if ($this->dbType   === 'mysql')
            return '`'.$name.'`';
        else
            return '"'.$name.'"';
    }
}