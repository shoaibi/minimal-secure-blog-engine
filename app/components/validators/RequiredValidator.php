<?php
namespace GGS\Components\Validators;

class RequiredValidator extends AllowEmptyValidator
{
    public $strict      = false;

    public function validate(\GGS\Components\Model & $object, $attribute)
    {
        return parent::validate($object, $attribute);
    }
}