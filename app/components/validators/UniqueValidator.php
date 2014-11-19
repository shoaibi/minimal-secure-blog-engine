<?php
namespace GGS\Components\Validators;

class UniqueValidator extends Validator
{
    public function validate(\GGS\Components\Model & $object, $attribute)
    {
        $qualifiedModelClassName    = get_class($object);
        $exists                     = $qualifiedModelClassName::exists(array($attribute => $object->$attribute));
        if ($exists)
        {
            $this->setError($object, $attribute, ' record already exists');
        }
        return !$exists;
    }
}