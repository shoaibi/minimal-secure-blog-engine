<?php
namespace GGS\Components\Validators;

class SanitizeValidator extends Validator
{
    public $allowedTags = null;

    public function validate(\GGS\Components\Model & $object, $attribute)
    {
        $object->$attribute = strip_tags($object->$attribute, $this->allowedTags);
        return true;
    }
}