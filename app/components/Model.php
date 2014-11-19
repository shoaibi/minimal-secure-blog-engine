<?php
namespace GGS\Components;
use \GGS\Components\WebApplication;
use GGS\Components\Validators\Validator;
use \GGS\Helpers\StringUtils;
use \GGS\Helpers\FormUtils;

abstract class Model extends Object
{
    protected $id;

    protected $errors;

    public static function getQualifiedModelClassName($modelClassName)
    {
        return '\GGS\Models\\' . $modelClassName;
    }

    public static function getAll($limit = null, $offset = null, $orderBy = null)
    {
        return static::getByCriteria(array(), $limit, $offset, $orderBy);
    }

    public static function getByPk($pk)
    {
        $criteria   = array(static::getPkName() => $pk);
        return  static::getOneByCriteria($criteria);
    }

    public static function getOneByCriteria(array $criteria = array(), $limit = null, $offset = null, $orderBy = null)
    {
        $results    = static::getByCriteria($criteria, $limit, $offset, $orderBy);
        if (count($results) > 1)
        {
            WebApplication::exitWithException(new \Exception("More than one records found for: " . PHP_EOL . print_r($criteria, true), 400));
        }
        return (isset($results[0]))? $results[0] : null;
    }

    public static function getByCriteria(array $criteria = array(), $limit = null, $offset = null, $orderBy = null)
    {
        $quotedTableName    = static::enquote(static::getTableName());
        $query              = "select * from {$quotedTableName}";
        $statement          = static::executeQueryByCriteria($query, $criteria, $limit, $offset, $orderBy);
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
        $criteria           = array(static::getPkName() => $pk);
        $quotedTableName    = static::enquote(static::getTableName());
        $query              = "delete from {$quotedTableName}";
        return boolval(static::executeQueryByCriteria($query, $criteria));
    }

    protected static function executeQueryByCriteria($query, array $criteria = array(), $limit = null,
                                                        $offset = null, $orderBy = null)
    {
        if (!isset($orderBy))
        {
            $orderBy    = static::getDefaultOrderBy();
        }
        list($whereClauses, $parameters) = static::resolveClausesAndParametersByCriteria($criteria);
        if (!empty($whereClauses))
        {
            $where  = implode(' AND ', $whereClauses);
            $query  .= " where {$where}";
        }
        $query .= " order by {$orderBy}";
        if (isset($limit))
        {
            $query  .= " limit {$limit}";
        }
        if (isset($offset))
        {
            $query  .= " offset {$offset}";
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
        return WebApplication::$database->enquote($value);
    }

    protected static function prepare($query)
    {
        return WebApplication::$database->getConnection()->prepare($query);
    }

    protected static function getTableName()
    {
        return strtolower(StringUtils::getNameWithoutNamespaces(get_called_class()));
    }

    protected static function getPkName()
    {
        return 'id';
    }

    protected function setPkValue($value)
    {
        $pk           = static::getPkName();
        $this->$pk    = $value;
    }

    public function getPkValue()
    {
        $pk   = static::getPkName();
        return $this->$pk;
    }

    protected static function resolveColumnToPlaceholder($columnName)
    {
        return ":${columnName}";
    }

    protected static function getDefaultOrderBy()
    {
        return static::getPkName() . ' desc';
    }

    public function __get($attribute)
    {
        if (property_exists($this, $attribute))
        {
            return $this->$attribute;
        }
        if ($attribute == $this->getPkName())
        {
            return $this->getPkValue();
        }
    }

    public function resolveAttributeLabel($attribute)
    {
        $labels = $this->attributeLabels();
        $label  = (isset($labels[$attribute]))? $labels[$attribute] : ucfirst($attribute);
        return $label;
    }

    public function attributeLabels()
    {
        // attribute => label
        return array('id' => 'ID');
    }

    public function rules()
    {
        $validators      = array(
            'id' => array(
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

    public function getQualifiedErrorMessageWithInputIds()
    {
        $formName               = StringUtils::getNameWithoutNamespaces(get_class($this));
        $qualifiedErrorMessages = array();
        foreach ($this->getErrors() as $attribute => $error)
        {
            $inputId                            = FormUtils::resolveInputId($formName, $attribute);
            $qualifiedErrorMessages[$inputId]   = $error;
        }
        return $qualifiedErrorMessages;
    }

    public function validate()
    {
        $this->beforeValidate();
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
        $this->afterValidate();
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
        return "{$className} #" . $this->getPkValue();
    }

    public function delete()
    {
        $this->beforeDelete();
        $deleted        = static::deleteByPk($this->getPkValue());
        $this->afterDelete($deleted);
        return $deleted;
    }

    public function isNew()
    {
        $pk     = $this->getPkValue();
        return (empty($pk));
    }

    public function save($validate = true)
    {
        if ($validate && !$this->validate())
        {
            return false;
        }
        $this->beforeSave();
        $saved          = ($this->isNew())? $this->create() : $this->update();
        if (!$saved)
        {
            throw new \Exception("Unable to save record");
        }
        $this->afterSave();
        return $this->getPkValue();
    }

    protected function create()
    {
        $attributes                 = static::getSavableAttributes();
        list($query, $parameters)   = static::resolveInsertQueryAndParametersByAttributes($attributes);
        $inserted                   = boolval(static::prepareBindAndExecute($query, $parameters));
        if ($inserted)
        {
            $this->setPkValue(intval(WebApplication::$database->getConnection()->lastInsertId(static::getTableName())));
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
        $pk                     = static::getPkName();
        $quotedpk               = static::enquote($pk);
        $pkColumnPlaceholder    = static::resolveColumnToPlaceholder($pk);
        $quotedTableName        = static::enquote(static::getTableName());
        $query                  = "UPDATE {$quotedTableName} SET updatesList where {$quotedpk} = ${pkColumnPlaceholder}";
        $parameters             = array($pkColumnPlaceholder    => $this->getPkValue());
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
        unset($attributes[static::getPkName()]);
        return $attributes;
    }

    protected function beforeValidate()
    {

    }

    protected function afterValidate()
    {

    }

    protected function beforeDelete()
    {

    }

    protected function afterDelete($deleted = true)
    {

    }

    protected function beforeSave()
    {

    }

    protected function afterSave()
    {

    }
}