<?php
namespace GGS\Components;
use \GGS\Components\Application;
use GGS\Components\Validators\Validator;
use \GGS\Helpers\StringUtils;

abstract class Model extends Object
{
    /**
     * @var int
     */
    public $id;

    protected $errors;

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

    public static function exists(array $criteria = array())
    {
        return (static::getCountByCriteria($criteria) > 0);
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

    public function rules()
    {
        $validators      = array(
            'id' => array(
                                array('sanitize'),
                                array('type', array('type' => 'integer', 'allowEmpty' => true)),
                                array('value', array('max' => 4294967295, 'allowEmpty' => true)),
            )
        );
        return $validators;
    }

    public function hasError($attribute)
    {
        return isset($this->errors[$attribute]);
    }

    public function setError($attribute, $message)
    {
        $this->errors[$attribute]   = $message;
    }

    public function getError($attribute)
    {
        return $this->errors[$attribute];
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function validate()
    {
        $validated  = true;
        foreach ($this->rules() as $attribute => $rules)
        {
            foreach ($rules as $rule)
            {
                $validatorClassName = Validator::resolveClassNameByType($rule[0]);
                $validator          = new $validatorClassName();
                if (isset($rule[1]))
                {
                    foreach ($rule[1] as $validatorAttribute => $value)
                    {
                        $validator->{$validatorAttribute} = $value;
                    }
                }
                if (!$validator->validate($this, $attribute))
                {
                    $validated = false;
                    break;
                }
            }
        }
        return $validated;
    }

    public function setAttributes(array $source)
    {
        $savableAttributeKeys    = array_keys(static::getSavableAttributes());
        foreach ($savableAttributeKeys as $key)
        {
            if (isset($source[$key]))
            {
                $this->$key     = $source[$key];
            }
        }
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

    public function save($validate = true)
    {
        if ($validate && !$this->validate())
        {
            return false;
        }
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
        $attributes                 = static::getSavableAttributes();
        list($query, $parameters)   = static::resolveInsertQueryAndParametersByAttributes($attributes);
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
        $attributes                 = static::getSavableAttributes();
        list($query, $parameters)   = static::resolveUpdateQueryAndParametersByAttributes($attributes);
        return boolval(static::prepareBindAndExecute($query, $parameters));
    }

    protected function resolveInsertQueryAndParametersByAttributes(array $attributes)
    {
        $quotedTableName    = static::enquote(static::getTableName());
        $query              = "insert into {$quotedTableName}(columnNames) values (columnData);";
        $columnNames        = array(); // not using array_keys($attributes) to ensure quoted column names;
        $parameters         = array(); // not using raw values in query, instead binding them.
        foreach ($attributes as $columnName => $value)
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

    protected function resolveUpdateQueryAndParametersByAttributes(array $attributes)
    {
        $pkColumnName           = static::getPkColumnName();
        $quotedPkColumnName     = static::enquote($pkColumnName);
        $pkColumnPlaceholder    = static::resolveColumnToPlaceholder($pkColumnName);
        $quotedTableName        = static::enquote(static::getTableName());
        $query                  = "UPDATE {$quotedTableName} SET updatesList where {$quotedPkColumnName} = ${pkColumnPlaceholder}";
        $parameters             = array($pkColumnPlaceholder    => $this->$pkColumnName);
        $updatesList            = array();
        foreach ($attributes as $columnName => $value)
        {
            $placeholder                = static::resolveColumnToPlaceholder($columnName);
            $updatesList[]              = static::enquote($columnName) . " = " . $placeholder;
            $parameters[$placeholder]   = $value;
        }
        $updatesList                = implode(',', $updatesList);
        $query                      = strtr($query, compact('updatesList'));
        return array($query, $parameters);
    }

    protected function getSavableAttributes()
    {
        $attributes = get_object_vars($this);
        unset($attributes['errors']);
        unset($attributes[static::getPkColumnName()]);
        return $attributes;
    }
}