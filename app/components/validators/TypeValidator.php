<?php
namespace GGS\Components\Validators;

class TypeValidator extends AllowEmptyValidator
{
    public $type        = null;

    public function validate(\GGS\Components\Model & $object, $attribute)
    {
        if (parent::validate($object, $attribute))
        {
            return true;
        }
        $valid = (gettype($object->$attribute) == $this->type);
        if (!$valid)
        {
            $this->setError($object, $attribute, 'must be of type ' . $this->type);
        }
        return $valid;
    }
}