<?php
namespace GGS\Helpers;

class StringUtils
{
    public static function getNameWithoutNamespaces($name)
    {
        return end(explode('\\', $name));
    }
}