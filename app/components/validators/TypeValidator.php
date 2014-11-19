<?php
namespace GGS\Components\Validators;

class TypeValidator extends AllowEmptyValidator
{
    public $type        = null;

    public $strict      = false;

    public function validate(\GGS\Components\Model & $object, $attribute)
    {
        if (empty($object->$attribute))
        {
            return parent::validate($object, $attribute);
        }
        $valid  = ((!$this->strict && $this->isSameWhenTypeCasted($object->$attribute)) || $this->isExactlySameType($object->$attribute));
        if (!$valid)
        {
            $this->setError($object, $attribute, 'must be of type ' . $this->type);
        }
        return $valid;
    }

    protected function isSameWhenTypeCasted($value)
    {
        if (is_array($value) || is_object($value) || is_resource($value) || is_bool($value))
        {
            return false;
        }
        if ($this->type === 'integer' && intval($value) == $value)
        {
            return true;
        }
        if ($this->type === 'string' && strval($value) == $value)
        {
            return true;
        }
        // TODO: @Shoaibi: High: Add support for float, double, date, datetime, time
    }

    protected function isExactlySameType($value)
    {
        return (gettype($value) == $this->type);
    }
}