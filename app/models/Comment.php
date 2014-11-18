<?php
namespace GGS\Models;
use GGS\Components\Model;

class Comment extends Model
{
    // validate is string
    // strip all tags
    // validate does not exceed 50 characters
    // validate not empty
    public $name;

    // validate is string
    // strip all tags
    // validate does not exceed 50 characters
    // validate not empty
    public $email;

    // validate is string
    // strip all but "a"
    // validate does not exceed more than text, < 2^16
    public $message;

    // validate is integer or like integer
    // validate does not exceed 4294967295
    // validate a post exists by this id
    public $postId;

    public function __toString()
    {
        return parent::__toString() . ' - ' . $this->name;
    }

    public function rules()
    {
        $ownValidators      = array(
            'name' => array(
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
                                array('email', array('allowEmpty' => true)),
            ),

            'message' => array(
                                array('required'),
                                array('sanitize', array('allowedTags' => '<a>')),
                                array('type', array('type' => 'string')),
                                array('length', array('min' => 5, 'max' => pow(2, 16))),
            ),

            'postId' => array(
                                array('required'),
                                array('sanitize'),
                                array('type', array('type' => 'integer')),
                                array('value', array('min' => 1, 'max' => 4294967295)),
                                array('referencedField', array('modelClass' => 'Post', 'attribute' => 'id')),
            ),
        );
        $parentValidators   = parent::rules();
        $validators         = array_merge($parentValidators, $ownValidators);
        return $validators;
    }

}