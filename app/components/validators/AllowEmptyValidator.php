<?php
namespace GGS\Components\Validators;

class AllowEmptyValidator extends Validator
{
    public $allowEmpty  = false;

    public function validate(\GGS\Components\Model & $object, $attribute)
    {
        if ($this->allowEmpty && empty($object->$attribute))
        {
            return true;
        }
        $this->setError($object, $attribute, 'can not be empty');
        return false;
    }
}