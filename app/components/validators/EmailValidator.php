<?php
namespace GGS\Components\Validators;

class EmailValidator extends AllowEmptyValidator
{
    public function validate(\GGS\Components\Model & $object, $attribute)
    {
        if (!parent::validate($object, $attribute))
        {
            return false;
        }
        $valid  = boolval(filter_var($object->$attribute, FILTER_VALIDATE_EMAIL));
        if (!$valid)
        {
            $this->setError($object, $attribute, 'is not a valid email address');
        }
        return $valid;
    }
}