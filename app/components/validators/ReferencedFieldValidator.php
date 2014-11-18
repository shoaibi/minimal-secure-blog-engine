<?php
namespace GGS\Components\Validators;

class ReferencedFieldValidator extends AllowEmptyValidator
{
    public $modelClass  = null;

    public $attribute   = null;

    public function validate(\GGS\Components\Model & $object, $attribute)
    {
        if (parent::validate($object, $attribute))
        {
            return true;
        }
        $qualifiedModelClassName    = $this->getQualifiedModelClassName();
        $exists = $qualifiedModelClassName::exists(array($attribute => $object->$attribute));
        if (!$exists)
        {
            $this->setError($object, $attribute, 'referenced record can not be found');
        }
        return $exists;
    }

    protected function getQualifiedModelClassName()
    {
        return '\GGS\Models\\' . $this->modelClass;
    }
}