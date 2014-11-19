<?php
namespace GGS\Components;

/**
 * Interface Singleton
 * @package GGS\Components
 */
interface Singleton
{
    /**
     * Bootstrap the instance
     * @param array $config
     * @return mixed
     */
    public static function getInstance(array $config);
}