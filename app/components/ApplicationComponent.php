<?php
namespace GGS\Components;

abstract class ApplicationComponent extends Object implements Singleton
{
    protected function __clone()
    {
    }
}