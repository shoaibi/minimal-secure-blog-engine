<?php
namespace GGS\Helpers;

class StringUtils
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

}