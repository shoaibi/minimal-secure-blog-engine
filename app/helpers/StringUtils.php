<?php
namespace GGS\Helpers;

abstract class StringUtils
{
    public static function getNameWithoutNamespaces($name)
    {
        return end(explode('\\', $name));
    }

    /**
     * Given a string and a length, return the chopped string if it is larger than the length.
     * @param $string
     * @param $length
     * @param string $ellipsis
     * @return string
     */
    public static function getChoppedStringContent($string, $length, $ellipsis = '...')
    {
        if ($string != null && strlen($string) > $length)
        {
            return substr($string, 0, ($length - 3)) . $ellipsis;
        }
        else
        {
            return $string;
        }
    }

    public static function stripSlashesRecursive($value)
    {
        $value = is_array($value) ? array_map('static::stripSlashesRecursive', $value) : stripslashes($value);
        return $value;
    }

}