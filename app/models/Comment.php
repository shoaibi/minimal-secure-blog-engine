<?php
namespace GGS\Models;
use GGS\Components\Model;

/**
 * Model class to deal with post comments
 * Class Comment
 * @package GGS\Models
 */
class Comment extends Model
{
    /**
     * Name of comment author
     * @var string
     */
    public $name;

    /**
     * Email of comment author
     * @var string
     */
    public $email;

    /**
     * The message commentor left us
     * @var string
     */
    public $message;

    /**
     * Post id this comment belongs to
     * @var int
     */
    public $postId;

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return parent::__toString() . ' - ' . $this->name;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $ownValidators      = array(
            'name' => array(
                                array('required'),
                                array('sanitize'),
                                array('type', array('type' => 'string')),
                                array('length', array('min' => 2, 'max' => 50)),
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