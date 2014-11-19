<?php
namespace GGS\Models;
use GGS\Components\Model;

/**
 * Model class to handle post data
 * Class Post
 * @package GGS\Models
 */
class Post extends Model
{
    /**
     * Title of the post
     * @var string
     */
    public $title;

    /**
     * Email of the post author
     * @var string
     */
    public $email;

    /**
     * Post content
     * @var string
     */
    public $content;

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return parent::__toString() . ' - ' . $this->title;
    }

    /**
     * @inheritdoc
     */
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