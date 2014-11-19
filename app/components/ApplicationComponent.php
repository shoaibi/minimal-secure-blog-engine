<?php
namespace GGS\Components;

/**
 * Base class for defining application components
 * Class ApplicationComponent
 * @package GGS\Components
 */
abstract class ApplicationComponent extends Object implements Singleton
{
    /**
     * ApplicationComponent implements Singleton so __clone's public visiblity should be removed.
     */
    protected function __clone()
    {
    }
}