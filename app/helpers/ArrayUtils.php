<?php
namespace GGS\Helpers;

class ArrayUtils
{
    public static function getAllNonNestedValues(array $source)
    {
        return array_filter($source, 'static::filterNonNestedValues');
    }

    protected static function filterNonNestedValues($value)
    {
        return !is_array($value);
    }
}
?>