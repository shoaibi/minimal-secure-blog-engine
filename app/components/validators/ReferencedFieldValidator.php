<?php
namespace GGS\Components\Validators;

class ReferencedFieldValidator extends AllowEmptyValidator
{
    public $modelClass  = null;

    public $attribute   = null;

    public function validate(\GGS\Components\Model & $object, $attribute)
    {
        if (!parent::validate($object, $attribute))
        {
            return false;
        }
        $qualifiedModelClassName    = \GGS\Components\Model::getQualifiedModelClassName($this->modelClass);
        $exists = $qualifiedModelClassName::exists(array($this->attribute => $object->$attribute));
        if (!$exists)
        {
            $this->setError($object, $attribute, 'referenced record can not be found');
        }
        return $exists;
    }
}