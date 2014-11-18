<?php
namespace GGS\Components\Validators;
use GGS\Components\Object;

abstract class Validator extends Object
{
    abstract public function validate(\GGS\Components\Model & $object, $attribute);

    public static function resolveClassNameByType($type)
    {
        return '\GGS\Components\Validators\\' . ucfirst($type) . 'Validator';
    }

    protected function setError(\GGS\Components\Model & $object, $attribute, $message)
    {
        $label      = $object->resolveAttributeLabel($attribute);
        $object->setError($attribute, $label . ' ' . $message);
    }
}