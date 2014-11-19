<?php
namespace GGS\Helpers;
use GGS\Components\WebApplication;
use GGS\Models\Csrf;

/**
 * Utility class to handle honey pot input functions
 * Class HoneyPotInputHelper
 * @package GGS\Helpers
 */
abstract class HoneyPotInputHelper
{
    /**
     * Name of spam check input field on form
     * ideally this should be dynamic for each form too, but in that case we would have to store
     * form->spam-check-input mapping somewhere.
     */
    const HONEY_POT_INPUT_NAME     = 'check';

    /**
     * The html input type used for spam check input
     */
    const HONEY_POT_INPUT_TYPE     = 'text';

    /**
     * Render the spam check input
     * @return string
     */
    public static function renderInput()
    {
        return FormHelper::renderAntiSpamInput(static::HONEY_POT_INPUT_NAME, static::HONEY_POT_INPUT_TYPE, null);
    }

    /**
     * Check if honey pot input has been filled
     * @return bool
     */
    public static function isHoneyPotInputFilled()
    {
        if (WebApplication::$request->isPostRequest())
        {
            // get the value for honeypot
            $fieldValue = WebApplication::$request->getPostParameter(static::HONEY_POT_INPUT_NAME);
            return ($fieldValue !== '');
        }
        // not post request? cool.
        return true;
    }
}
?>