<?php
namespace GGS\Helpers;

/**
 * Utility class to handle additional array features
 * Class ArrayHelper
 * @package GGS\Helpers
 */
abstract class ArrayHelper
{
    /**
     * Strip nested values from an array
     * @param array $source
     * @return array
     */
    public static function getAllNonNestedValues(array $source)
    {
        return array_filter($source, 'static::filterNonNestedValues');
    }

    /**
     * Resolve if a value is nested or not
     * @param $value
     * @return bool
     */
    protected static function filterNonNestedValues($value)
    {
        return is_scalar($value);
    }
}
?>