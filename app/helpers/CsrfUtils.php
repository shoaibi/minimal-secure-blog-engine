<?php
namespace GGS\Helpers;
use GGS\Components\WebApplication;
use GGS\Models\Csrf;

abstract class CsrfUtils
{
    // ideally this should be dynamic for each form too, but in that case we would have to store
    // form->csrf-input mapping somewhere.
    const CSRF_INPUT_NAME   = 'g2z9CJpnR0OYlmNvDeKwNa0EMyqNG2pLWaXie9WHJSY5lNsCwTqBjCwPo3kqq3Pz';

    public static function getNewToken($action)
    {
        $csrf           = new Csrf();
        $csrf->action   = $action;
        if (!$csrf->save())
        {
            WebApplication::exitWithException(new \Exception('Unable to generate csrf token'), 400);
        }
        return $csrf->key;
    }

    public static function validateRequest($action)
    {
        if (WebApplication::$request->isPostRequest())
        {
            $token      = WebApplication::$request->getPostParameter(static::CSRF_INPUT_NAME);
            return static::isTokenValid($token, $action);
        }
        return true;
    }

    public static function isTokenValid($token, $action, $userAgent = null, $userIP = null)
    {
        if (empty($token))
        {
            return false;
        }
        return \GGS\Models\Csrf::isValid($token, $action, $userAgent, $userIP);
    }

    public static function renderCsrfInput($value)
    {
        return FormUtils::renderAntiSpamInput(static::CSRF_INPUT_NAME, 'hidden', $value);
    }
}
?>