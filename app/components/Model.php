<?php
namespace GGS\Components;
use \GGS\Components\Application;
use \GGS\Helpers\StringUtils;

abstract class Model extends Object
{
    /**
     * @var int
     */
    public $id;

    public static function getAll()
    {
        return static::getByCriteria(array());
    }

    public static function getByPk($pk)
    {
        $criteria = array(static::getPkColumnName() => $pk);
        return static::getByCriteria($criteria);
    }

    public static function getByCriteria(array $criteria = array())
    {
        $quotedTableName    = static::enquote(static::getTableName());
        $query              = "select * from {$quotedTableName}";
        $statement          = static::executeQueryByCriteria($query, $criteria);
        return $statement->fetchAll(\PDO::FETCH_CLASS, get_called_class());
    }

    public static function getCountByCriteria(array $criteria = array())
    {
        $quotedTableName    = static::enquote(static::getTableName());
        $query              = "select count(*) from {$quotedTableName}";
        $statement          = static::executeQueryByCriteria($query, $criteria);
        return intval($statement->fetchColumn());
    }

    public static function deleteByPk($pk)
    {
        $criteria           = array(static::getPkColumnName() => $pk);
        $quotedTableName    = static::enquote(static::getTableName());
        $query              = "delete from {$quotedTableName}";
        return static::executeQueryByCriteria($query, $criteria);
    }

    protected static function executeQueryByCriteria($query, array $criteria = array())
    {
        list($whereClauses, $parameters) = static::resolveClausesAndParametersByCriteria($criteria);
        if (!empty($whereClauses))
        {
            $where  = implode(' AND ', $whereClauses);
            $query  .= " where {$where}";
        }
        return static::prepareBindAndExecute($query, $parameters);
    }

    protected static function resolveClausesAndParametersByCriteria(array $criteria)
    {
        $clauses        = array();
        $parameters     = array();
        foreach ($criteria as $key => $value)
        {
            $placeholder                = static::resolveColumnToPlaceholder($key);
            $quotedKey                  = static::enquote($key);
            $clauses[]                  = "{$quotedKey} = {$placeholder}";
            $parameters[$placeholder]   = $value;
        }
        return array($clauses, $parameters);
    }

    protected static function prepareBindAndExecute($query, $bindParameters = array())
    {
        $statement          = static::prepare($query);
        foreach ($bindParameters as $key => $value)
        {
            $statement->bindValue($key, $value);
        }
        if ($statement->execute())
        {
            return $statement;
        }
        return false;
    }

    protected static function enquote($value)
    {
        return Application::$database->enquote($value);
    }

    protected static function prepare($query)
    {
        return Application::$database->getConnection()->prepare($query);
    }

    protected static function getTableName()
    {
        return strtolower(StringUtils::getNameWithoutNamespaces(get_called_class()));
    }

    protected static function getPkColumnName()
    {
        return 'id';
    }

    protected static function resolveColumnToPlaceholder($columnName)
    {
        return ":${columnName}";
    }

    public function __toString()
    {
        $className      = StringUtils::getNameWithoutNamespaces(get_class($this));
        $pkColumnName   = static::getPkColumnName();
        return "{$className} #" . $this->$pkColumnName;
    }

    public function delete()
    {
        $pkColumnName   = static::getPkColumnName();
        return static::deleteByPk($this->$pkColumnName);
    }

    public function save()
    {
        $pkColumnName   = static::getPkColumnName();
        $saved          = (isset($this->$pkColumnName))? $this->update() : $this->create();
        if (!$saved)
        {
            throw new \Exception("Unable to save record");
        }
        return $this->$pkColumnName;
    }

    protected function create()
    {
        $pkColumnName               = static::getPkColumnName();
        $properties                 = static::getSavableData();
        list($query, $parameters)   = static::resolveInsertQueryAndParametersByProperties($properties);
        $inserted                   = boolval(static::prepareBindAndExecute($query, $parameters));
        if ($inserted)
        {
            $this->$pkColumnName    = intval(Application::$database->getConnection()->lastInsertId(static::getTableName()));
            return true;
        }
        return false;
    }

    protected function update()
    {
        $properties                 = static::getSavableData();
        list($query, $parameters)   = static::resolveUpdateQueryAndParametersByProperties($properties);
        return boolval(static::prepareBindAndExecute($query, $parameters));
    }

    protected function resolveInsertQueryAndParametersByProperties(array $properties)
    {
        $quotedTableName    = static::enquote(static::getTableName());
        $query              = "insert into {$quotedTableName}(columnNames) values (columnData);";
        $columnNames        = array(); // not using array_keys($properties) to ensure quoted column names;
        $parameters         = array(); // not using raw values in query, instead binding them.
        foreach ($properties as $columnName => $value)
        {
            $placeholder                = static::resolveColumnToPlaceholder($columnName);
            $columnNames[]              = static::enquote($columnName);
            $parameters[$placeholder]   = $value;
        }
        $columnNames                    = implode(',', $columnNames);
        $columnData                     = implode(',', array_keys($parameters));
        $query                          = strtr($query, compact('columnNames', 'columnData'));
        return array($query, $parameters);
    }

    protected function resolveUpdateQueryAndParametersByProperties(array $properties)
    {
        $pkColumnName           = static::getPkColumnName();
        $quotedPkColumnName     = static::enquote($pkColumnName);
        $pkColumnPlaceholder    = static::resolveColumnToPlaceholder($pkColumnName);
        $quotedTableName        = static::enquote(static::getTableName());
        $query                  = "UPDATE {$quotedTableName} SET updatesList where {$quotedPkColumnName} = ${pkColumnPlaceholder}";
        $parameters             = array($pkColumnPlaceholder    => $this->$pkColumnName);
        $updatesList            = array();
        foreach ($properties as $columnName => $value)
        {
            $placeholder                = static::resolveColumnToPlaceholder($columnName);
            $updatesList[]              = static::enquote($columnName) . " = " . $placeholder;
            $parameters[$placeholder]   = $value;
        }
        $updatesList                = implode(',', $updatesList);
        $query                      = strtr($query, compact('updatesList'));
        return array($query, $parameters);
    }

    protected function getSavableData()
    {
        $properties         = get_object_vars($this);
        unset($properties[static::getPkColumnName()]);
        return $properties;
    }
}