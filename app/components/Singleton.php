<?php
namespace GGS\Components;

interface Singleton
{
    public static function getInstance(array $config);
}