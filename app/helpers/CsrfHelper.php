<?php
namespace GGS\Helpers;
use GGS\Components\WebApplication;
use GGS\Models\Csrf;

/**
 * Utility class to handle CSRF protection
 * Class CsrfHelper
 * @package GGS\Helpers
 */
abstract class CsrfHelper
{
    /**
     * Name of csrf input field on form
     * ideally this should be dynamic for each form too, but in that case we would have to store
     * form->csrf-input mapping somewhere.
     */
    const CSRF_INPUT_NAME   = 'g2z9CJpnR0OYlmNvDeKwNa0EMyqNG2pLWaXie9WHJSY5lNsCwTqBjCwPo3kqq3Pz';

    /**
     * The html input type to use for csrf input rendering
     */
    const CSRF_INPUT_TYPE   = 'hidden';

    /**
     * Resolve new csrf token against provided action
     * @param $action
     * @return mixed
     */
    public static function getNewToken($action)
    {
        // create new csrf model instance, set its action, rest would be auto-populated using default validators
        $csrf           = new Csrf();
        $csrf->action   = $action;
        if (!$csrf->save(true, false))
        {
            WebApplication::exitWithException(new \Exception('Unable to generate csrf token', 400));
        }
        return $csrf->key;
    }

    /**
     * Validate if current request is valid
     * @param $action
     * @return bool
     */
    public static function validateRequest($action)
    {
        if (WebApplication::$request->isPostRequest())
        {
            // get the token
            $token      = WebApplication::$request->getPostParameter(static::CSRF_INPUT_NAME);
            // check if token is valid
            return static::isTokenValid($token, $action);
        }
        // not post request? cool.
        return true;
    }

    /**
     * Check if provided token is valid
     * @param $token
     * @param $action
     * @param null $userAgent
     * @param null $userIP
     * @return bool
     */
    public static function isTokenValid($token, $action, $userAgent = null, $userIP = null)
    {
        if (empty($token))
        {
            // token is empty? bail out
            return false;
        }
        // use model to check if the token is valid. FAT MODELS afterall.
        return \GGS\Models\Csrf::isValid($token, $action, $userAgent, $userIP);
    }

    /**
     * Render csrf input
     * @param $value
     * @return string
     */
    public static function renderInput($value)
    {
        return FormHelper::renderAntiSpamInput(static::CSRF_INPUT_NAME, static::CSRF_INPUT_TYPE, $value);
    }
}
?>