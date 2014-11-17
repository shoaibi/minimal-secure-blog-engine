<?php
namespace GGS\Models;
use GGS\Components\Model;

class Post extends Model
{
    public $name;

    public function __toString()
    {
        return parent::__toString() . $this->name;
    }
}