<?php
namespace GGS\Models;
use GGS\Components\Model;
use GGS\Components\WebApplication;

/**
 * Model class to handle csrf records
 * Class Csrf
 * @package GGS\Models
 */
class Csrf extends Model
{
    /**
     * How long shall a csrf record live(in seconds)?
     */
    const LIFE_TIME     = 3600;

    /**
     * Length of the csrf key
     */
    const KEY_LENGTH    = 64;

    /**
     * The random key rendered in forms
     * @var string
     */
    public $key;

    /**
     * Action name for which this csrf was generated
     * @var string
     */
    public $action;

    /**
     * User Agent string who triggered this form
     * @var string
     */
    public $userAgent;

    /**
     * User's IP who triggered this form
     * @var int
     */
    public $userIP;

    /**
     * Time after which this csrf would be invalid
     * @var int
     */
    public $expirationTime;

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        // using default validator to set default values generated using functions
        $ownValidators      = array(
            'key' => array(
                                array('default', array('value' => static::generateRandomKey())),
                                array('required'),
                                array('type', array('type' => 'string')),
                                array('length', array('exact' => static::KEY_LENGTH)),
                                array('unique'),
            ),

            'action' => array(
                                array('required'),
                                array('type', array('type' => 'string')),
                                array('length', array('min' => 8, 'max' => 30)),
            ),

            'userAgent' => array(
                                array('default', array('value' => static::getUserAgent())),
                                array('required'),
                                array('type', array('type' => 'string')),
                                array('length', array('min' => 2, 'max' => 255)),
            ),

            'userIP' => array(
                                array('default', array('value' => static::getUserIP())),
                                array('required'),
                                array('type', array('type' => 'integer')),
            ),

            'expirationTime' => array(
                                array('default', array('value' => static::getExpirationTime())),
                                array('required'),
                                array('type', array('type' => 'integer')),
            ),
        );
        $parentValidators   = parent::rules();
        $validators         = array_merge($parentValidators, $ownValidators);
        return $validators;
    }

    /**
     * Generate a random key
     * @return string
     */
    public static function generateRandomKey()
    {
        return \GGS\Helpers\StringHelper::generateRandomString(static::KEY_LENGTH);
    }

    /**
     * Resolve current user's user agent. A wrapper around Request component's function with same name.
     * @return string
     */
    public static function getUserAgent()
    {
        return WebApplication::$request->getUserAgent();
    }

    /**
     * Resolve current user's IP. A wrapper around Request component's function with same name, added the functionality
     * to convert ip to long
     * @param bool $long
     * @return int|null|string
     */
    public static function getUserIP($long = true)
    {
        $ip     = WebApplication::$request->getUserIP();
        $ip     = (isset($ip) && $long)? static::encodeIpForDatabase($ip) : $ip;
        return $ip;
    }

    /**
     * Encode IP for storing it in database
     * @param $ip
     * @return int
     */
    public static function encodeIpForDatabase($ip)
    {
        return (isset($ip)) ? sprintf("%u", ip2long($ip)) : $ip;
    }

    /**
     * Decode retrieved IP from database
     * @param $ip
     * @return string
     */
    public static function decodeIpFromDatabase($ip)
    {
        return (isset($ip)) ? long2ip($ip) : $ip;
    }

    /**
     * Check if provided IP matches current record's userIP
     * @param $ip
     * @return bool
     */
    public function hasSameIPAs($ip)
    {
        return (intval($this->userIP) === static::encodeIpForDatabase($ip));
    }

    /**
     * Generate expiration time
     * @return int
     */
    public static function getExpirationTime()
    {
        return (time() + static::LIFE_TIME);
    }

    /**
     * Check if provided csrf key is valid
     * @param $key
     * @param $action
     * @param null $userAgent
     * @param null $userIP
     * @return bool
     */
    public static function isValid($key, $action, $userAgent = null, $userIP = null)
    {
        if (strlen($key) !== static::KEY_LENGTH)
        {
            // this is no way the key system generated.
            return false;
        }
        // set userAgent and userIP using own utility functions if they are empty
        $userAgent  = (!empty($userAgent))  ? $userAgent : static::getUserAgent();
        $userIP     = (!empty($userIP)) ? $userIP : static::getUserIP(false);
        // try to locate a record against the provided key.
        // intentionally using getOneByCriteria as key should be unique across multiple csrf entries
        $record     = static::getOneByCriteria(array('key' => $key));
        if (!isset($record))
        {
            // record not found?
            // perhaps there never was a record.
            // perhaps the key's record was expired and deleted(not implemented)
            return false;
        }

        var_dump(get_defined_vars());
        var_dump('Current Time:');
        var_dump(time());
        var_dump('is time less than expiration?');
        var_dump(time() < $record->expirationTime);
        var_dump('$record->action == $action');
        var_dump($record->action == $action);
        var_dump('$record->userAgent == $userAgent');
        var_dump($record->userAgent == $userAgent);
        var_dump('$record->hasSameIpAs($userIP)');
        var_dump($record->hasSameIpAs($userIP));
        var_dump('encoded ip');
        var_dump(static::encodeIpForDatabase($userIP));

        // ensure csrf has not expired and has same attributes as the one in this request
        return (time() < $record->expirationTime && $record->action == $action && $record->userAgent == $userAgent
                    && $record->hasSameIpAs($userIP));
    }
}