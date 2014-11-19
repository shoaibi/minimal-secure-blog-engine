<?php
namespace GGS\Helpers;

/**
 * Utility class to handle additional string functionality
 * Class StringUtils
 * @package GGS\Helpers
 */
abstract class StringUtils
{
    /**
     * Provided a class name, remove the namespace prefix and return the base name
     * @param $name
     * @return mixed
     */
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

    /**
     * Strip slashes from provided value
     * @param $value
     * @return array|string
     */
    public static function stripSlashesRecursive($value)
    {
        $value = is_array($value) ? array_map('static::stripSlashesRecursive', $value) : stripslashes($value);
        return $value;
    }

    /**
     * Generate a random string
     * @param int $length
     * @param null $characterSet
     * @return string
     */
    public static function generateRandomString($length = 10, $characterSet = null)
    {
        if (empty($characterSet))
        {
            $characterSet = implode(range("A", "Z")) . implode(range("a", "z")) . implode(range("0", "9"));
        }
        // get the length of character set to set bounds of rand() below
        $characterSetLength = strlen($characterSet);
        $randomString = '';
        for ($i = 0; $i < $length; $i++)
        {
            // get a random character
            $randomCharacter    = $characterSet[rand(0, $characterSetLength - 1)];
            // append it to the string
            $randomString       .= $randomCharacter;
        }
        return $randomString;
    }


}