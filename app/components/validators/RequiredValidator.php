<?php
namespace GGS\Components\Validators;

class RequiredValidator extends AllowEmptyValidator
{
    public $value       = null;

    public $strict      = false;

    public function validate(\GGS\Components\Model & $object, $attribute)
    {
        if (isset($this->value))
        {
            if ($this->strict)
            {
                $valid = ($object->$attribute === $this->value);
            }
            else
            {
                $valid = ($object->$attribute == $this->value);
            }
            if (!$valid)
            {
                $this->setError($object, $attribute, 'does not match ' . $this->value);
            }
            return $valid;
        }
        return parent::validate($object, $attribute);
    }
}