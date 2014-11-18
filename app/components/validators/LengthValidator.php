<?php
namespace GGS\Components\Validators;

class LengthValidator extends ValueValidator
{
    protected function resolveComparisonValue(\GGS\Components\Model  $object, $attribute)
    {
        return strlen($object->$attribute);
    }

    protected function resolveErrorMessagePrefix()
    {
        return 'length ';
    }
}