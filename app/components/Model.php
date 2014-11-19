<?php
namespace GGS\Components;
use \GGS\Components\WebApplication;
use GGS\Components\Validators\Validator;
use \GGS\Helpers\StringHelper;
use \GGS\Helpers\FormHelper;

/**
 * Base class for models
 * Class Model
 * @package GGS\Components
 */
abstract class Model extends Object
{
    /**
     * Set to protected as we never set it from the frontend. System should always take care of this.
     * @var int
     */
    protected $id;

    /**
     * Container to store validation errors
     * @var array
     */
    protected $errors;

    /**
     * Get the name of primary key column
     * @return string
     */
    public static function getPkName()
    {
        return 'id';
    }

    /**
     * Get qualified model class name with namespace
     * @param $modelClassName
     * @return string
     */
    public static function getQualifiedModelClassName($modelClassName)
    {
        return '\GGS\Models\\' . $modelClassName;
    }

    /**
     * Get all instance of the model
     * @param null $limit
     * @param null $offset
     * @param null $orderBy
     * @return array
     */
    public static function getAll($limit = null, $offset = null, $orderBy = null)
    {
        return static::getByCriteria(array(), $limit, $offset, $orderBy);
    }

    /**
     * Get an instance of model using its primary key value
     * @param $pk
     * @return null
     */
    public static function getByPk($pk)
    {
        $criteria   = array(static::getPkName() => $pk);
        return  static::getOneByCriteria($criteria);
    }

    /**
     * Get a single object provided the filtering criteria
     * @param array $criteria
     * @param null $limit
     * @param null $offset
     * @param null $orderBy
     * @return null
     */
    public static function getOneByCriteria(array $criteria = array(), $limit = null, $offset = null, $orderBy = null)
    {
        $results    = static::getByCriteria($criteria, $limit, $offset, $orderBy);
        if (count($results) > 1)
        {
            // found multiple? but that should not happen.
            WebApplication::exitWithException(new \Exception("More than one records found for: " . PHP_EOL . print_r($criteria, true), 400));
        }
        return (isset($results[0]))? $results[0] : null;
    }

    /**
     * Get objects matching the provided filtering criteria
     * @param array $criteria
     * @param null $limit
     * @param null $offset
     * @param null $orderBy
     * @return array
     */
    public static function getByCriteria(array $criteria = array(), $limit = null, $offset = null, $orderBy = null)
    {
        $quotedTableName    = static::enquote(static::getTableName());
        $query              = "select * from {$quotedTableName}";
        $statement          = static::executeQueryByCriteria($query, $criteria, $limit, $offset, $orderBy);
        return $statement->fetchAll(\PDO::FETCH_CLASS, get_called_class());
    }

    /**
     * Check if an object exists provided a filtering criteria
     * @param array $criteria
     * @return bool
     */
    public static function exists(array $criteria = array())
    {
        return (static::getCountByCriteria($criteria) > 0);
    }

    /**
     * Get count of existing objects against provided filtering criteria
     * @param array $criteria
     * @return int
     */
    public static function getCountByCriteria(array $criteria = array())
    {
        $quotedTableName    = static::enquote(static::getTableName());
        $query              = "select count(*) from {$quotedTableName}";
        $statement          = static::executeQueryByCriteria($query, $criteria);
        return intval($statement->fetchColumn());
    }

    /**
     * Delete an object by its primary key value
     * @param $pk
     * @return bool
     */
    public static function deleteByPk($pk)
    {
        $criteria           = array(static::getPkName() => $pk);
        $quotedTableName    = static::enquote(static::getTableName());
        $query              = "delete from {$quotedTableName}";
        return boolval(static::executeQueryByCriteria($query, $criteria));
    }

    /**
     * Provided a query, apply filtering criteria and other limiters, return the compiled statement or false on error.
     * @param $query
     * @param array $criteria
     * @param null $limit
     * @param null $offset
     * @param null $orderBy
     * @return bool|\PDOStatement
     */
    protected static function executeQueryByCriteria($query, array $criteria = array(), $limit = null,
                                                        $offset = null, $orderBy = null)
    {
        if (!isset($orderBy))
        {
            // orderby not set? get the defaults
            $orderBy    = static::getDefaultOrderBy();
        }
        // resolve where clauses and the parameters to be bound
        list($whereClauses, $parameters) = static::resolveClausesAndParametersByCriteria($criteria);
        if (!empty($whereClauses))
        {
            // append where clauses to query after joining them together
            $where  = implode(' AND ', $whereClauses);
            $query  .= " where {$where}";
        }
        // apply order by clause
        $query .= " order by {$orderBy}";
        if (isset($limit))
        {
            // apply limit
            $query  .= " limit {$limit}";
        }
        if (isset($offset))
        {
            // apply offset
            $query  .= " offset {$offset}";
        }
        // prepare the query, bind it with parameters and execute it
        return static::prepareBindAndExecute($query, $parameters);
    }

    /**
     * Resolves where clauses as well as the parameters to be bound to a query provided a filtering criteria
     * @param array $criteria
     * @return array
     */
    protected static function resolveClausesAndParametersByCriteria(array $criteria)
    {
        $clauses        = array();
        $parameters     = array();
        foreach ($criteria as $key => $value)
        {
            $operator                   = "=";
            if (is_array($value))
            {
                $operator               = $value[1];
                $value                  = $value[0];
            }
            $placeholder                = static::resolveColumnToPlaceholder($key);
            $quotedKey                  = static::enquote($key);
            $clauses[]                  = "{$quotedKey} {$operator} {$placeholder}";
            $parameters[$placeholder]   = $value;
        }
        return array($clauses, $parameters);
    }

    /**
     * Prepared provided query, binds provided parameters and executes it. Returns statement on success and false on failure.
     * @param $query
     * @param array $bindParameters
     * @return bool|\PDOStatement
     */
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

    /**
     * Wrapper around Database component's function with same name
     * @param $value
     * @return string
     */
    protected static function enquote($value)
    {
        return WebApplication::$database->enquote($value);
    }

    /**
     * Wrapper around Database component's function with same name
     * @param $query
     * @return \PDOStatement
     */
    protected static function prepare($query)
    {
        return WebApplication::$database->getConnection()->prepare($query);
    }

    /**
     * Get database table name for this class
     * @return string
     */
    protected static function getTableName()
    {
        // by default we have tables in all lower case letters names after the unqualified model classes
        return strtolower(StringHelper::getNameWithoutNamespaces(get_called_class()));
    }

    /**
     * Set primary key value
     * Protected because system is responsible for maintaining primary keys
     * @param $value
     */
    protected function setPkValue($value)
    {
        $pk           = static::getPkName();
        $this->$pk    = $value;
    }

    /**
     * A wrapper to return the protected primary key field's value
     * @return mixed
     */
    public function getPkValue()
    {
        $pk   = static::getPkName();
        return $this->$pk;
    }

    /**
     * Taking care of direct accessing of primary key inside validators
     * @param $attribute
     * @return mixed
     */
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

    /**
     * Resolve PDO query parameter placeholder provided a column name
     * @param $columnName
     * @return string
     */
    protected static function resolveColumnToPlaceholder($columnName)
    {
        return ":${columnName}";
    }

    /**
     * resolve the default order by criteria
     * @return string
     */
    protected static function getDefaultOrderBy()
    {
        return static::getPkName() . ' desc';
    }

    /**
     * Resolve label for provided attribute
     * @param $attribute
     * @return string
     */
    public function resolveAttributeLabel($attribute)
    {
        // get all attribute labels
        $labels = $this->attributeLabels();
        // get the label if it exists else default to upper casing the first letter
        $label  = (isset($labels[$attribute]))? $labels[$attribute] : ucfirst($attribute);
        return $label;
    }

    /**
     * Configure a model's attribute labels displayed on frontend
     * @return array
     */
    public function attributeLabels()
    {
        // attribute => label
        return array('id' => 'ID');
    }

    /**
     * Configure a model's validation rules
     * @return array
     */
    public function rules()
    {
        // rule order matters
        $validators      = array(
            'id' => array(
                                array('type', array('type' => 'integer', 'allowEmpty' => true)),
                                array('value', array('min' => 0, 'max' => 4294967295, 'allowEmpty' => true)),
            )
        );
        return $validators;
    }

    /**
     * Check if a model attribute has validation error
     * @param $attribute
     * @return bool
     */
    public function hasError($attribute)
    {
        return isset($this->errors[$attribute]);
    }

    /**
     * Set validation error for provided attribute
     * @param $attribute
     * @param $message
     */
    public function setError($attribute, $message)
    {
        $this->errors[$attribute]   = $message;
    }

    /**
     * Get validation error for provided attribute
     * @param $attribute
     * @return mixed
     */
    public function getError($attribute)
    {
        return $this->errors[$attribute];
    }

    /**
     * Get all validation errors against the current model object
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get all validation errors against the current model object but qualify the keys to be form input ids
     * @return array
     */
    public function getQualifiedErrorMessageWithInputIds()
    {
        // get the form name
        $formName               = \GGS\Helpers\FormHelper::getName(get_class($this));
        $qualifiedErrorMessages = array();
        foreach ($this->getErrors() as $attribute => $error)
        {
            // qualify the key to be input id
            $inputId                            = FormHelper::resolveInputId($formName, $attribute);
            $qualifiedErrorMessages[$inputId]   = $error;
        }
        return $qualifiedErrorMessages;
    }

    /**
     * Validate a model object against rules()
     * @return bool
     */
    public function validate()
    {
        // trigger beforeValidate to do any special handling
        $this->beforeValidate();
        $validated  = true;
        foreach ($this->rules() as $attribute => $rules)
        {
            foreach ($rules as $rule)
            {
                // get validator class name
                $validatorClassName = Validator::resolveClassNameByType($rule[0]);
                $validator          = new $validatorClassName();
                if (isset($rule[1]))
                {
                    // validator properties are available, loop through and set for the validator object
                    foreach ($rule[1] as $validatorAttribute => $value)
                    {
                        $validator->{$validatorAttribute} = $value;
                    }
                }
                // validate the attribute
                if (!$validator->validate($this, $attribute))
                {
                    // failed? break the current chain. One error per attribute is enough.
                    $validated = false;
                    break;
                }
            }
        }
        // call afterValidate hook to perform any special checks
        $this->afterValidate();
        return $validated;
    }

    /**
     * Mass assign model attributes from an array
     * @param array $source
     */
    public function setAttributes(array $source)
    {
        // get the attributes that are publicly exposed
        $savableAttributeKeys    = array_keys(static::getSavableAttributes());
        foreach ($savableAttributeKeys as $key)
        {
            // check if array has a value for this attribute, if so, set it.
            if (isset($source[$key]))
            {
                $this->$key     = $source[$key];
            }
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $className      = StringHelper::getNameWithoutNamespaces(get_class($this));
        return "{$className} #" . $this->getPkValue();
    }

    /**
     * Delete current model object
     * @return bool
     */
    public function delete()
    {
        // call the beforeDelete() hook to handle any special processes
        $this->beforeDelete();
        // delete the model
        $deleted        = static::deleteByPk($this->getPkValue());
        // call the afterDelete passing telling it if model was deleted or not
        $this->afterDelete($deleted);
        return $deleted;
    }

    /**
     * Is the current model object new? or does it already exist in database?
     * @return bool
     */
    public function isNew()
    {
        $pk     = $this->getPkValue();
        return (empty($pk));
    }

    /**
     * Save current model object
     * @param bool $validate
     * @param bool $throwExceptionOnFailure
     */
    public function save($validate = true, $throwExceptionOnFailure = true)
    {
        if ($validate && !$this->validate())
        {
            // wanted to validate but validation failed? bail out.
            return false;
        }
        // call beforeSave hook to handle special processes
        $this->beforeSave();
        // call create() if record is new, update() if it already exists
        $saved          = ($this->isNew())? $this->create() : $this->update();
        if (!$saved)
        {
            if ($throwExceptionOnFailure)
            {
                // woah, database bailed on app or was there an issue with the query?
                WebApplication::exitWithException(new \Exception("Unable to save record"));
            }
            else
            {
                return false;
            }
        }
        // call the after save hook only if record has been saved.
        $this->afterSave();
        // return the primary key value
        return $this->getPkValue();
    }

    /**
     * Save a model object that does not exist in database
     * @return bool
     */
    protected function create()
    {
        // get publicly available attributes
        $attributes                 = static::getSavableAttributes();
        // resolve query and the parameters to be bound to PDO statement for insert
        list($query, $parameters)   = static::resolveInsertQueryAndParametersByAttributes($attributes);
        // fire the guns
        $inserted                   = boolval(static::prepareBindAndExecute($query, $parameters));
        if ($inserted)
        {
            // record has been inserted. Time to refresh its primary key.
            // this part would need a refactor if we change primary key from autoincrement to something else.
            $this->setPkValue(intval(WebApplication::$database->getConnection()->lastInsertId(static::getTableName())));
            return true;
        }
        return false;
    }

    /**
     * Update an existing model object
     * @return bool
     */
    protected function update()
    {
        // get publicly available attributes
        $attributes                 = static::getSavableAttributes();
        // resolve query and parameters to be bound to PDO statement for update
        list($query, $parameters)   = static::resolveUpdateQueryAndParametersByAttributes($attributes);
        // fly away
        return boolval(static::prepareBindAndExecute($query, $parameters));
    }

    /**
     * Resolve insert query and parameters to be bound to PDO statement using provided attributes
     * @param array $attributes
     * @return array
     */
    protected function resolveInsertQueryAndParametersByAttributes(array $attributes)
    {
        // because we love quotes
        $quotedTableName    = static::enquote(static::getTableName());
        // the base query with placeholders to be replaced with actual data later
        $query              = "insert into {$quotedTableName}(columnNames) values (columnData);";
        // not using array_keys($attributes) to ensure quoted column names;
        $columnNames        = array();
        // not using raw values in query, instead binding them.
        $parameters         = array();
        foreach ($attributes as $columnName => $value)
        {
            // get the placeholder, set quoted columnName and add value to parameters value
            $placeholder                = static::resolveColumnToPlaceholder($columnName);
            $columnNames[]              = static::enquote($columnName);
            $parameters[$placeholder]   = $value;
        }
        // join columnNames
        $columnNames                    = implode(',', $columnNames);
        // join the parameters
        $columnData                     = implode(',', array_keys($parameters));
        // translate the placeholders in the base query above to actual data
        $query                          = strtr($query, compact('columnNames', 'columnData'));
        return array($query, $parameters);
    }

    protected function resolveUpdateQueryAndParametersByAttributes(array $attributes)
    {
        $pk                     = static::getPkName();
        $quotedpk               = static::enquote($pk);
        $pkColumnPlaceholder    = static::resolveColumnToPlaceholder($pk);
        $quotedTableName        = static::enquote(static::getTableName());
        // base update query with placeholders that will be replaced with actual data
        $query                  = "UPDATE {$quotedTableName} SET updatesList where {$quotedpk} = ${pkColumnPlaceholder}";
        // we know one parameter beforehand e.g. the pk value in WHERE clause of update query
        $parameters             = array($pkColumnPlaceholder    => $this->getPkValue());
        $updatesList            = array();
        foreach ($attributes as $columnName => $value)
        {
            // get the placeholder, set the updatesList with quoted column name and placeholder,
            // set value to parameter's placeholder
            $placeholder                = static::resolveColumnToPlaceholder($columnName);
            $updatesList[]              = static::enquote($columnName) . " = " . $placeholder;
            $parameters[$placeholder]   = $value;
        }
        // join updatesList
        $updatesList                = implode(',', $updatesList);
        // translate the updatesList placeholder in the update base query above
        $query                      = strtr($query, compact('updatesList'));
        return array($query, $parameters);
    }

    /**
     * Get the publicly modifiable attributes
     * @return array
     */
    protected function getSavableAttributes()
    {
        $attributes = get_object_vars($this);
        // errors is a special attribute
        unset($attributes['errors']);
        // oh and so is primary key
        unset($attributes[static::getPkName()]);
        return $attributes;
    }

    /**
     * Hook that gets called before validate()
     */
    protected function beforeValidate()
    {

    }

    /**
     * Hook that gets called after validate()
     */
    protected function afterValidate()
    {

    }

    /**
     * Hook that gets called before delete()
     */
    protected function beforeDelete()
    {

    }

    /**
     * Hook that gets called after delete()
     * @param bool $deleted
     */
    protected function afterDelete($deleted = true)
    {

    }

    /**
     * Hook that gets called before create() or update()
     */
    protected function beforeSave()
    {

    }

    /**
     * Hook that gets called after create() or update() on successful exection
     */
    protected function afterSave()
    {

    }
}