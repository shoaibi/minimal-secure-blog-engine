<?php
namespace GGS\Components\Validators;

class DefaultValidator extends Validator
{
    public $value       = null;

    public function validate(\GGS\Components\Model & $object, $attribute)
    {
        if (isset($this->value) && empty($object->$attribute))
        {
            $object->$attribute = $this->value;
        }
        return true;
    }
}