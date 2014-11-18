<?php
namespace GGS\Components\Validators;

class ValueValidator extends AllowEmptyValidator
{
    public $min     = null;

    public $max     = null;

    public function validate(\GGS\Components\Model & $object, $attribute)
    {
        if (!parent::validate($object, $attribute))
        {
            return false;
        }
        $valid              = false;
        $errorMessagePrefix = $this->resolveErrorMessagePrefix();
        $comparisonValue    = $this->resolveComparisonValue($object, $attribute);
        if (isset($this->min, $this->max))
        {
            $valid = ($comparisonValue > $this->min && $comparisonValue < $this->max);
            if (!$valid)
            {
                $this->setError($object, $attribute, $errorMessagePrefix . 'must be between ' . $this->min . ' - '  . $this->max);
            }
        }
        else if (isset($this->min))
        {
            $valid  = ($comparisonValue > $this->min);
            if (!$valid)
            {
                $this->setError($object, $attribute, $errorMessagePrefix . 'must be greater than ' . $this->min);
            }
        }
        else if (isset($this->max))
        {
            $valid = ($comparisonValue < $this->max);
            if (!$valid)
            {
                $this->setError($object, $attribute, $errorMessagePrefix . 'must be less than ' . $this->max);
            }
        }
        return $valid;
    }

    protected function resolveComparisonValue(\GGS\Components\Model $object, $attribute)
    {
        return $object->$attribute;
    }

    protected function resolveErrorMessagePrefix()
    {
        return '';
    }
}