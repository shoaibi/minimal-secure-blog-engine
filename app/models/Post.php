<?php
namespace GGS\Models;
use GGS\Components\Model;

class Post extends Model
{
    public $title;

    public $email;

    public $content;

    public function __toString()
    {
        return parent::__toString() . ' - ' . $this->title;
    }

    public function rules()
    {
        $ownValidators      = array(
            'title' => array(
                                array('required'),
                                array('sanitize'),
                                array('type', array('type' => 'string')),
                                array('length', array('min' => 5, 'max' => 50)),
            ),

            'email' => array(
                                array('required'),
                                array('sanitize'),
                                array('type', array('type' => 'string')),
                                array('length', array('min' => 5, 'max' => 50)),
                                array('email'),
            ),

            'content' => array(
                                array('required'),
                                array('sanitize', array('allowedTags' => '<a>')),
                                array('type', array('type' => 'string')),
                                array('length', array('min' => 10, 'max' => pow(2, 16))),
            ),
        );
        $parentValidators   = parent::rules();
        $validators         = array_merge($parentValidators, $ownValidators);
        return $validators;
    }
}