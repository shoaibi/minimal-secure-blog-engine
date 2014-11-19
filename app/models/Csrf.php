<?php
namespace GGS\Models;
use GGS\Components\Model;
use GGS\Components\WebApplication;

class Csrf extends Model
{
    const LIFE_TIME = 3600;

    // char(64)
    public $key;

    // varchar(30)
    public $action;

    // varchar(255)
    public $userAgent;

    // int
    public $userIP;

    // int
    public $expirationTime;

    public function __toString()
    {
        return $this->key;
    }

    public function rules()
    {
        $ownValidators      = array(
            'key' => array(
                                array('default', array('value' => static::generateRandomKey())),
                                array('required'),
                                array('type', array('type' => 'string')),
                                array('length', array('exact' => 64)),
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

    public static function generateRandomKey()
    {
        return \GGS\Helpers\StringUtils::generateRandomString(64);
    }

    public static function getUserAgent()
    {
        return WebApplication::$request->getUserAgent();
    }

    public static function getUserIP($long = true)
    {
        $ip     = WebApplication::$request->getUserIP();
        if ($ip && $long)
        {
            $ip     = ip2long($ip);
        }
        return $ip;
    }

    protected static function encodeIpForDatabase($ip)
    {
        return (isset($ip)) ? ip2long($ip) : $ip;
    }

    protected static function decodeIpFromDatabase($ip)
    {
        return (isset($ip)) ? long2ip($ip) : $ip;
    }

    public function hasSameIPAs($ip)
    {
        return (intval($this->userIP) === static::encodeIpForDatabase($ip));
    }

    public static function getExpirationTime()
    {
        return (time() + static::LIFE_TIME);
    }

    public static function isValid($key, $action, $userAgent = null, $userIP = null)
    {
        $userAgent  = (!empty($userAgent))  ? $userAgent : static::getUserAgent();
        $userIP     = (!empty($userIP)) ? $userIP : static::getUserIP(false);
        $record     = static::getOneByCriteria(array('key' => $key));
        if (!isset($record))
        {
            return false;
        }
        return (time() < $record->expirationTime && $record->action == $action && $record->userAgent == $userAgent
                    && $record->hasSameIpAs($userIP));
    }
}